<?php
declare(strict_types=1);

/**
 * Military Task Manager Helper Functions
 * Security Classification: RESTRICTED
 */

// Session validation for military systems
function validate_session(array $session): bool {
    // Validate session fingerprint
    $current_fingerprint = hash_hmac('sha256', 
        $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'], 
        getenv('APP_SECRET')
    );
    
    return hash_equals($session['fingerprint'] ?? '', $current_fingerprint);
}

// Get authenticated user with security checks
function get_authenticated_user(PDO $pdo, int $user_id): ?array {
    $stmt = $pdo->prepare("
        SELECT u.*, r.clearance_level 
        FROM users u
        LEFT JOIN roles r ON u.user_type = r.role_name
        WHERE u.id = ? AND u.status = 'active'
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// Role-Based Task Query
function get_user_tasks(PDO $pdo, array $user): array {
    $query = "
        SELECT 
            t.*,
            u1.username as assigned_by_name,
            u2.username as assigned_to_name,
            GROUP_CONCAT(e.name SEPARATOR ', ') as required_equipment
        FROM tasks t
        LEFT JOIN users u1 ON t.assigned_by = u1.id
        LEFT JOIN users u2 ON t.assigned_to = u2.id
        LEFT JOIN task_equipment te ON t.id = te.task_id
        LEFT JOIN equipment e ON te.equipment_id = e.id
    ";

    $params = [];
    $conditions = [];

    switch ($user['user_type']) {
        case 'admin':
            // Admins see all tasks
            break;
            
        case 'officer':
            $conditions[] = "(t.assigned_by = :user_id OR t.assigned_to = :user_id)";
            $params[':user_id'] = $user['id'];
            break;
            
        case 'soldier':
            $conditions[] = "t.assigned_to = :user_id";
            $params[':user_id'] = $user['id'];
            break;
            
        default:
            throw new Exception("Unauthorized role");
    }

    // Clearance level filter
    if ($user['user_type'] !== 'admin') {
        $conditions[] = "t.clearance_level <= :clearance";
        $params[':clearance'] = $user['clearance_level'] ?? 'unclassified';
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $query .= " GROUP BY t.id ORDER BY t.deadline ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// NATO Standard Status Formatting
function format_nato_status(string $status): string {
    $nato_codes = [
        'pending' => 'STANDBY',
        'in_progress' => 'EXECUTING',
        'completed' => 'MISSION ACCOMPLISHED',
        'overdue' => 'CRITICAL',
        'aborted' => 'ABORTED'
    ];
    return $nato_codes[strtolower($status)] ?? strtoupper($status);
}

// Military Priority Formatting
function format_priority(string $priority): string {
    return match(strtolower($priority)) {
        'high' => 'IMMEDIATE',
        'medium' => 'PRIORITY',
        'low' => 'ROUTINE',
        default => strtoupper($priority)
    };
}

// Deadline Formatting with Alert Status
function format_deadline(?string $deadline): string {
    if (!$deadline) return '<span class="text-muted">N/A</span>';
    
    $date = new DateTime($deadline);
    $now = new DateTime();
    $interval = $now->diff($date);
    
    if ($date < $now) {
        return '<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> OVERDUE</span>';
    }
    
    $hours = $interval->h + ($interval->days * 24);
    
    if ($hours < 12) {
        return '<span class="text-warning">' . $date->format('M j, H:i') . ' <small>(URGENT)</small></span>';
    }
    
    return $date->format('M j, H:i');
}

// Task Row Styling Based on Status
function get_task_row_class(array $task): string {
    $classes = [];
    
    // Status-based coloring
    switch (strtolower($task['status'])) {
        case 'completed':
            $classes[] = 'table-success';
            break;
        case 'overdue':
            $classes[] = 'table-danger pulse-alert';
            break;
        case 'in_progress':
            $classes[] = 'table-primary';
            break;
    }
    
    // Deadline proximity warning
    if ($task['deadline']) {
        $deadline = new DateTime($task['deadline']);
        $now = new DateTime();
        
        if ($deadline < $now->modify('+24 hours')) {
            $classes[] = 'table-warning';
        }
    }
    
    // Top secret marking
    if ($task['clearance_level'] === 'top_secret') {
        $classes[] = 'bg-dark bg-opacity-10';
    }
    
    return implode(' ', $classes);
}

// Combat Readiness Calculation
function calculate_readiness_score(array $tasks): int {
    if (empty($tasks)) return 100;
    
    $completed = 0;
    $overdue = 0;
    $total = count($tasks);
    
    foreach ($tasks as $task) {
        if (strtolower($task['status']) === 'completed') {
            $completed++;
        } elseif ($task['deadline'] && new DateTime($task['deadline']) < new DateTime()) {
            $overdue++;
        }
    }
    
    $score = (($completed * 1.5) + ($total - $overdue)) / ($total * 1.5) * 100;
    return min(100, max(0, (int) round($score)));
}

// Equipment Availability Check
function check_equipment_availability(PDO $pdo, array $tasks): int {
    if (empty($tasks)) return 0;
    
    $task_ids = array_column($tasks, 'id');
    $placeholders = implode(',', array_fill(0, count($task_ids), '?'));
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as missing
        FROM task_equipment te
        JOIN equipment e ON te.equipment_id = e.id
        WHERE te.task_id IN ($placeholders)
        AND e.status != 'available'
    ");
    
    $stmt->execute($task_ids);
    return (int) $stmt->fetchColumn();
}

// Task Permission Check
function can_edit_task(array $user, array $task): bool {
    // Admins can edit anything
    if ($user['user_type'] === 'admin') return true;
    
    // Officers can edit their own tasks
    if ($user['user_type'] === 'officer' && $task['assigned_by'] == $user['id']) return true;
    
    // Soldiers can edit tasks assigned to them
    if ($user['user_type'] === 'soldier' && $task['assigned_to'] == $user['id']) return true;
    
    return false;
}

// Flash Briefing Helpers
function count_priority_tasks(array $tasks, int $hours = 24): int {
    $now = new DateTime();
    $cutoff = $now->modify("+$hours hours");
    $count = 0;
    
    foreach ($tasks as $task) {
        if ($task['deadline'] && new DateTime($task['deadline']) <= $cutoff) {
            $count++;
        }
    }
    
    return $count;
}

function get_equipment_summary(array $tasks): string {
    $equipment = [];
    
    foreach ($tasks as $task) {
        if (!empty($task['required_equipment'])) {
            $items = explode(', ', $task['required_equipment']);
            foreach ($items as $item) {
                $equipment[$item] = ($equipment[$item] ?? 0) + 1;
            }
        }
    }
    
    if (empty($equipment)) return 'No equipment required';
    
    $output = [];
    foreach ($equipment as $item => $count) {
        $output[] = "$item ($count)";
    }
    
    return implode(', ', $output);
}

function get_readiness_description(int $score): string {
    return match(true) {
        $score >= 90 => 'FULL READINESS',
        $score >= 75 => 'OPERATIONAL',
        $score >= 50 => 'DEGRADED CAPABILITY',
        $score >= 25 => 'LIMITED OPERATIONS',
        default => 'CRITICAL STATE'
    };
}

// Security Classification Helpers
function get_readiness_color(int $score): string {
    return match(true) {
        $score >= 80 => 'success',
        $score >= 60 => 'info',
        $score >= 40 => 'warning',
        default => 'danger'
    };
}

function get_status_badge_class(string $status): string {
    return match(strtolower($status)) {
        'completed' => 'success',
        'in_progress' => 'primary',
        'pending' => 'warning',
        'overdue' => 'danger',
        'aborted' => 'dark',
        default => 'secondary'
    };
}

function get_priority_badge_class(string $priority): string {
    return match(strtolower($priority)) {
        'high' => 'danger',
        'medium' => 'warning',
        'low' => 'success',
        default => 'secondary'
    };
}

// Secure logging for military systems
function log_activity(string $action, array $user): void {
    $log_entry = sprintf(
        "[%s] %s (User ID: %d) - %s\n",
        date('Y-m-d H:i:s'),
        $user['username'],
        $user['id'],
        $action
    );
    
    file_put_contents(
        __DIR__ . '/logs/activity.log',
        $log_entry,
        FILE_APPEND | LOCK_EX
    );
}
