<?php
require_once 'includes/db.php';
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get tasks based on user type
if ($user['user_type'] == 'admin') {
    $tasks = $pdo->query("SELECT * FROM tasks")->fetchAll();
} elseif ($user['user_type'] == 'officer') {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE assigned_by = ? OR assigned_to = ?");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $tasks = $stmt->fetchAll();
} else { // soldier
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE assigned_to = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $tasks = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command Center - Army Task Manager</title>
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
                url('https://images.unsplash.com/photo-1509803874385-db7c23652552?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            margin: 0;
            padding: 0;
        }
        
        main {
            max-width: 1200px;
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
            margin-bottom: 15px;
            letter-spacing: 1px;
        }
        
        .user-info {
            background: rgba(10, 15, 13, 0.7);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--military-gray);
        }
        
        .user-type {
            background: var(--military-accent);
            color: var(--military-dark);
            padding: 5px 15px;
            border-radius: 3px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9em;
            letter-spacing: 1px;
        }
        
        .task-actions {
            background: rgba(10, 15, 13, 0.7);
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid var(--military-gray);
        }
        
        .button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--military-accent);
            color: var(--military-dark);
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: all 0.3s ease;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
        }
        
        .button:hover {
            background: #d9b83b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(10, 15, 13, 0.7);
            border: 1px solid var(--military-gray);
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--military-gray);
        }
        
        th {
            background-color: var(--military-green);
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Orbitron', sans-serif;
        }
        
        tr:hover {
            background: rgba(139, 158, 126, 0.1);
        }
        
        .status-pending {
            color: var(--status-pending);
            font-weight: bold;
        }
        
        .status-completed {
            color: var(--status-completed);
            font-weight: bold;
        }
        
        .status-in_progress {
            color: var(--status-in-progress);
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
        
        .action-link {
            color: var(--military-light);
            margin-right: 15px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }
        
        .action-link:hover {
            color: var(--military-accent);
            transform: scale(1.2);
        }
        
        .no-tasks {
            text-align: center;
            padding: 40px;
            color: var(--military-light);
            font-style: italic;
            background: rgba(10, 15, 13, 0.7);
            border-radius: 5px;
            border: 1px dashed var(--military-gray);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .deadline-urgent {
            color: var(--priority-high);
            font-weight: bold;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        @media (max-width: 768px) {
            main {
                padding: 15px;
            }
            
            .user-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            th, td {
                padding: 8px 10px;
                font-size: 0.9rem;
            }
            
            .action-link {
                margin-right: 10px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="user-info">
            <h2><i class="fas fa-user-astronaut"></i> COMMAND CENTER: <?php echo htmlspecialchars(strtoupper($user['username'])); ?></h2>
            <span class="user-type"><i class="fas fa-shield-alt"></i> <?php echo strtoupper($user['user_type']); ?></span>
        </div>
        
        <?php if ($user['user_type'] == 'admin' || $user['user_type'] == 'officer'): ?>
            <section class="task-actions">
                <h3><i class="fas fa-bullhorn"></i> MISSION CONTROL</h3>
                <a href="create_task.php" class="button"><i class="fas fa-plus"></i> ISSUE NEW ORDERS</a>
            </section>
        <?php endif; ?>
        
        <section class="tasks">
            <h3><i class="fas fa-clipboard-check"></i> ACTIVE OPERATIONS</h3>
            
            <?php if (empty($tasks)): ?>
                <div class="no-tasks">
                    <p><i class="fas fa-peace"></i> No active missions. Stand by for orders.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>OPERATION</th>
                                <th>BRIEFING</th>
                                <th>STATUS</th>
                                <th>PRIORITY</th>
                                <th>DEADLINE</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): 
                                $isUrgent = $task['deadline'] && strtotime($task['deadline']) < strtotime('+3 days');
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                                    <td><?php echo strlen($task['description']) > 50 ? htmlspecialchars(substr($task['description'], 0, 50)).'...' : htmlspecialchars($task['description']); ?></td>
                                    <td class="status-<?php echo str_replace(' ', '_', strtolower($task['status'])); ?>">
                                        <?php echo strtoupper(str_replace('_', ' ', $task['status'])); ?>
                                    </td>
                                    <td class="priority-<?php echo strtolower($task['priority']); ?>">
                                        <?php echo strtoupper($task['priority']); ?>
                                    </td>
                                    <td class="<?php echo $isUrgent ? 'deadline-urgent' : ''; ?>">
                                        <?php echo $task['deadline'] ? date('M j, Y H:i', strtotime($task['deadline'])) : 'NONE'; ?>
                                    </td>
                                    <td>
                                        <a href="view_task.php?id=<?php echo $task['id']; ?>" class="action-link" title="Mission Details"><i class="fas fa-binoculars"></i></a>
                                        <?php if ($user['user_type'] != 'soldier' || $_SESSION['user_id'] == $task['assigned_to']): ?>
                                            <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="action-link" title="Update Mission"><i class="fas fa-edit"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Add urgent deadline flashing for missions due within 24 hours
        document.addEventListener('DOMContentLoaded', function() {
            const urgentCells = document.querySelectorAll('.deadline-urgent');
            urgentCells.forEach(cell => {
                cell.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + cell.textContent;
            });
        });
    </script>
</body>
</html>