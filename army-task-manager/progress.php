<?php
require_once 'includes/db.php';
session_start();

// Only admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Get all soldiers with their task counts
$soldiers = $pdo->query("
    SELECT 
        u.id, 
        u.username,
        u.email,
        COUNT(t.id) AS total_tasks,
        SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) AS completed_tasks,
        SUM(CASE WHEN t.status = 'pending' THEN 1 ELSE 0 END) AS pending_tasks,
        SUM(CASE WHEN t.status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_tasks
    FROM users u
    LEFT JOIN tasks t ON u.id = t.assigned_to
    WHERE u.user_type = 'soldier'
    GROUP BY u.id
    ORDER BY completed_tasks DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soldier Progress - Army Task Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --military-dark: #0a0f0d;
            --military-green: #2a4a3c;
            --military-light: #8b9e7e;
            --military-accent: #c5a73d;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: var(--military-green);
            border-bottom: 2px solid var(--military-accent);
            padding-bottom: 10px;
        }
        
        .soldier-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .soldier-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .soldier-card h3 {
            margin-top: 0;
            color: var(--military-green);
        }
        
        .progress-bar {
            height: 20px;
            background-color: #e0e0e0;
            border-radius: 10px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .progress {
            height: 100%;
            background-color: var(--military-accent);
        }
        
        .stats {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-value {
            font-weight: bold;
            font-size: 1.2em;
        }
        
        .stat-label {
            font-size: 0.8em;
            color: #666;
        }
        
        .completed { color: #2ecc71; }
        .pending { color: #f39c12; }
        .in-progress { color: #3498db; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1><i class="fas fa-user-shield"></i> Soldier Progress Report</h1>
        
        <div class="soldier-grid">
            <?php foreach ($soldiers as $soldier): 
                $completion_rate = $soldier['total_tasks'] > 0 
                    ? round(($soldier['completed_tasks'] / $soldier['total_tasks']) * 100) 
                    : 0;
            ?>
                <div class="soldier-card">
                    <h3><?php echo htmlspecialchars($soldier['username']); ?></h3>
                    <p><?php echo htmlspecialchars($soldier['email']); ?></p>
                    
                    <div class="progress-bar">
                        <div class="progress" style="width: <?php echo $completion_rate; ?>%"></div>
                    </div>
                    <p>Completion: <?php echo $completion_rate; ?>%</p>
                    
                    <div class="stats">
                        <div class="stat">
                            <div class="stat-value completed"><?php echo $soldier['completed_tasks']; ?></div>
                            <div class="stat-label">Completed</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value in-progress"><?php echo $soldier['in_progress_tasks']; ?></div>
                            <div class="stat-label">In Progress</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value pending"><?php echo $soldier['pending_tasks']; ?></div>
                            <div class="stat-label">Pending</div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>