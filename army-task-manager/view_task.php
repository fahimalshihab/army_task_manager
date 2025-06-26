<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$task_id = $_GET['id'];

// Get task details
$stmt = $pdo->prepare("SELECT t.*, u1.username as assigned_to_username, u2.username as assigned_by_username 
                      FROM tasks t 
                      JOIN users u1 ON t.assigned_to = u1.id 
                      JOIN users u2 ON t.assigned_by = u2.id 
                      WHERE t.id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task) {
    header('Location: dashboard.php');
    exit;
}

// Check if user has permission to view this task
$user_stmt = $pdo->prepare("SELECT user_type FROM users WHERE id = ?");
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch();

if ($user['user_type'] == 'soldier' && $task['assigned_to'] != $_SESSION['user_id']) {
    header('Location: dashboard.php');
    exit;
}

// Check if deadline is urgent (within 24 hours)
$isUrgent = $task['deadline'] && strtotime($task['deadline']) < strtotime('+1 day');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operation Briefing - Army Task Command</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Rajdhani:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --military-dark: #0a0f0d;
            --military-green: #2a4a3c;
            --military-light: #8b9e7e;
            --military-accent: #c5a73d;
            --military-red: #8c2e0b;
            --military-gray: #3a3a3a;
            --status-pending: #f39c12;
            --status-in-progress: #e74c3c;
            --status-completed: #27ae60;
            --priority-high: #e74c3c;
            --priority-medium: #f39c12;
            --priority-low: #27ae60;
        }
        
        body {
            font-family: 'Rajdhani', sans-serif;
            line-height: 1.6;
            color: white;
            background-color: var(--military-dark);
            background-image: 
                linear-gradient(rgba(10, 15, 13, 0.9), rgba(10, 15, 13, 0.9)),
                url('https://images.unsplash.com/photo-1543351611-58f69d7c1781?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            margin: 0;
            padding: 0;
        }
        
        main {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: rgba(42, 74, 60, 0.7);
            border: 1px solid var(--military-accent);
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(197, 167, 61, 0.2);
            backdrop-filter: blur(5px);
        }
        
        h2 {
            font-family: 'Orbitron', sans-serif;
            color: var(--military-accent);
            border-bottom: 2px solid var(--military-accent);
            padding-bottom: 10px;
            margin-bottom: 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        h3 {
            font-family: 'Orbitron', sans-serif;
            color: var(--military-light);
            margin: 25px 0 15px 0;
            letter-spacing: 1px;
            border-left: 3px solid var(--military-accent);
            padding-left: 10px;
        }
        
        .operation-briefing {
            background: rgba(10, 15, 13, 0.7);
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid var(--military-gray);
            position: relative;
        }
        
        .operation-briefing::before {
            content: "TOP SECRET";
            position: absolute;
            top: 10px;
            right: 10px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.8rem;
            color: var(--military-accent);
            opacity: 0.3;
        }
        
        .operation-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            color: var(--military-accent);
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px dashed var(--military-gray);
            padding-bottom: 10px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 15px;
            padding: 12px;
            background: rgba(10, 15, 13, 0.5);
            border-radius: 3px;
            border-left: 3px solid var(--military-gray);
        }
        
        .detail-label {
            font-weight: bold;
            min-width: 180px;
            color: var(--military-light);
            letter-spacing: 0.5px;
        }
        
        .detail-value {
            flex-grow: 1;
        }
        
        .status-pending {
            color: var(--status-pending);
            font-weight: bold;
        }
        
        .status-in_progress {
            color: var(--status-in-progress);
            font-weight: bold;
        }
        
        .status-completed {
            color: var(--status-completed);
            font-weight: bold;
        }
        
        .priority-high {
            color: var(--priority-high);
            font-weight: bold;
        }
        
        .priority-medium {
            color: var(--priority-medium);
            font-weight: bold;
        }
        
        .priority-low {
            color: var(--priority-low);
            font-weight: bold;
        }
        
        .operation-actions {
            background: rgba(10, 15, 13, 0.7);
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid var(--military-gray);
        }
        
        .status-update-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        
        .status-select {
            flex-grow: 1;
            padding: 10px 15px;
            border: 1px solid var(--military-gray);
            border-radius: 4px;
            background: rgba(10, 15, 13, 0.7);
            color: white;
            font-family: 'Rajdhani', sans-serif;
            font-size: 1em;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23c5a73d'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
        }
        
        .status-update-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--military-accent);
            color: var(--military-dark);
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }
        
        .status-update-btn:hover {
            background: #d9b83b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--military-gray);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-family: 'Rajdhani', sans-serif;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: #4a4a4a;
            transform: translateY(-2px);
        }
        
        .urgent {
            color: var(--priority-high);
            font-weight: bold;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }
        
        @media (max-width: 600px) {
            .detail-row {
                flex-direction: column;
            }
            
            .detail-label {
                margin-bottom: 5px;
                min-width: auto;
            }
            
            .status-update-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            main {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <h2><i class="fas fa-file-contract"></i> OPERATION BRIEFING</h2>
        
        <div class="operation-briefing">
            <div class="operation-title"><?php echo htmlspecialchars(strtoupper($task['title'])); ?></div>
            
            <div class="detail-row">
                <span class="detail-label"><i class="fas fa-file-alt"></i> MISSION BRIEF:</span>
                <span class="detail-value"><?php echo htmlspecialchars($task['description']); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label"><i class="fas fa-user-tag"></i> OPERATIVE:</span>
                <span class="detail-value"><?php echo htmlspecialchars($task['assigned_to_username']); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label"><i class="fas fa-user-secret"></i> COMMANDING OFFICER:</span>
                <span class="detail-value"><?php echo htmlspecialchars($task['assigned_by_username']); ?></span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label"><i class="fas fa-signal"></i> OPERATION STATUS:</span>
                <span class="detail-value status-<?php echo str_replace(' ', '_', strtolower($task['status'])); ?>">
                    <?php echo strtoupper(str_replace('_', ' ', $task['status'])); ?>
                </span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label"><i class="fas fa-exclamation-triangle"></i> PRIORITY LEVEL:</span>
                <span class="detail-value priority-<?php echo strtolower($task['priority']); ?>">
                    <?php echo strtoupper($task['priority']); ?>
                </span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label"><i class="fas fa-clock"></i> EXECUTION WINDOW:</span>
                <span class="detail-value <?php echo $isUrgent ? 'urgent' : ''; ?>">
                    <?php if ($task['deadline']): ?>
                        <?php echo date('M j, Y H:i', strtotime($task['deadline'])); ?>
                        <?php if ($isUrgent): ?>
                            <i class="fas fa-exclamation-circle"></i> URGENT
                        <?php endif; ?>
                    <?php else: ?>
                        ONGOING
                    <?php endif; ?>
                </span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label"><i class="fas fa-calendar-plus"></i> BRIEFING DATE:</span>
                <span class="detail-value"><?php echo date('M j, Y H:i', strtotime($task['created_at'])); ?></span>
            </div>
        </div>
        
        <?php if ($_SESSION['user_id'] == $task['assigned_to']): ?>
            <div class="operation-actions">
                <h3><i class="fas fa-satellite-dish"></i> STATUS UPDATE</h3>
                <form class="status-update-form" action="update_task_status.php" method="POST">
                    <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                    <select class="status-select" name="status">
                        <option value="pending" <?php echo $task['status'] == 'pending' ? 'selected' : ''; ?>>PENDING</option>
                        <option value="in_progress" <?php echo $task['status'] == 'in_progress' ? 'selected' : ''; ?>>IN PROGRESS</option>
                        <option value="completed" <?php echo $task['status'] == 'completed' ? 'selected' : ''; ?>>COMPLETED</option>
                    </select>
                    <button type="submit" class="status-update-btn"><i class="fas fa-paper-plane"></i> TRANSMIT UPDATE</button>
                </form>
            </div>
        <?php endif; ?>
        
        <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> RETURN TO COMMAND CENTER</a>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Add urgent deadline flashing effect
        document.addEventListener('DOMContentLoaded', function() {
            const urgentElements = document.querySelectorAll('.urgent');
            urgentElements.forEach(el => {
                setInterval(() => {
                    el.style.color = el.style.color === 'var(--priority-high)' ? 
                        'var(--military-accent)' : 'var(--priority-high)';
                }, 1000);
            });
        });
    </script>
</body>
</html>