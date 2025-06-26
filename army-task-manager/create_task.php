<?php
require_once 'includes/db.php';
session_start();

// Only officers and admins can create tasks
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] != 'officer' && $_SESSION['user_type'] != 'admin')) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $assigned_to = $_POST['assigned_to'];
    $priority = $_POST['priority'];
    $deadline = $_POST['deadline'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'OPERATION TITLE REQUIRED';
    }
    
    if (empty($assigned_to)) {
        $errors[] = 'ASSIGNEE REQUIRED';
    }
    
    if (empty($errors)) {
        // Insert new task
        $stmt = $pdo->prepare("INSERT INTO tasks (title, description, assigned_to, assigned_by, priority, deadline) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $description, $assigned_to, $_SESSION['user_id'], $priority, $deadline ? $deadline : null])) {
            header('Location: dashboard.php?mission_created=1');
            exit;
        } else {
            $errors[] = 'MISSION CREATION FAILED - TRY AGAIN';
        }
    }
}

// Get all soldiers for assignment
$soldiers = $pdo->query("SELECT id, username FROM users WHERE user_type = 'soldier' ORDER BY username")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue New Orders - Army Task Command</title>
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
            --priority-high: #e74c3c;
            --priority-medium: #f39c12;
            --priority-low: #27ae60;
        }
        
        body {
            font-family: 'Rajdhani', sans-serif;
            color: white;
            background-color: var(--military-dark);
            background-image: 
                linear-gradient(rgba(10, 15, 13, 0.9), rgba(10, 15, 13, 0.9)),
                url('https://images.unsplash.com/photo-1543351611-58f69d7c1781?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-attachment: fixed;
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
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .mission-form {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--military-light);
            letter-spacing: 0.5px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            background: rgba(10, 15, 13, 0.7);
            border: 1px solid var(--military-gray);
            border-radius: 4px;
            color: white;
            font-family: 'Rajdhani', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--military-accent);
            box-shadow: 0 0 0 2px rgba(197, 167, 61, 0.3);
        }
        
        .form-group input::placeholder {
            color: var(--military-gray);
        }
        
        .priority-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .priority-low { background-color: var(--priority-low); }
        .priority-medium { background-color: var(--priority-medium); }
        .priority-high { background-color: var(--priority-high); }
        
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .btn-primary {
            background: var(--military-accent);
            color: var(--military-dark);
        }
        
        .btn-secondary {
            background: var(--military-gray);
            color: white;
            text-decoration: none;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .errors {
            background: rgba(140, 46, 11, 0.2);
            border-left: 3px solid var(--military-red);
            padding: 15px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .errors p {
            margin: 5px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .errors p::before {
            content: "⚠";
            font-size: 1.2em;
        }
        
        @media (max-width: 600px) {
            main {
                padding: 15px;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <h2><i class="fas fa-bullhorn"></i> ISSUE NEW ORDERS</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form class="mission-form" method="POST">
            <div class="form-group">
                <label for="title"><i class="fas fa-heading"></i> OPERATION TITLE</label>
                <input type="text" id="title" name="title" required placeholder="Enter mission codename">
            </div>
            
            <div class="form-group">
                <label for="description"><i class="fas fa-file-alt"></i> MISSION BRIEF</label>
                <textarea id="description" name="description" placeholder="Provide detailed operation parameters"></textarea>
            </div>
            
            <div class="form-group">
                <label for="assigned_to"><i class="fas fa-user-tag"></i> ASSIGN TO OPERATIVE</label>
                <select id="assigned_to" name="assigned_to" required>
                    <option value="">SELECT SOLDIER</option>
                    <?php foreach ($soldiers as $soldier): ?>
                        <option value="<?php echo $soldier['id']; ?>">
                            <?php echo htmlspecialchars($soldier['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="priority"><i class="fas fa-exclamation-triangle"></i> PRIORITY LEVEL</label>
                <select id="priority" name="priority">
                    <option value="low">
                        <span class="priority-indicator priority-low"></span> LOW PRIORITY
                    </option>
                    <option value="medium" selected>
                        <span class="priority-indicator priority-medium"></span> STANDARD PRIORITY
                    </option>
                    <option value="high">
                        <span class="priority-indicator priority-high"></span> CRITICAL PRIORITY
                    </option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="deadline"><i class="fas fa-clock"></i> EXECUTION WINDOW (OPTIONAL)</label>
                <input type="datetime-local" id="deadline" name="deadline">
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> ISSUE ORDERS
                </button>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> ABORT MISSION
                </a>
            </div>
        </form>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Add priority indicator to select options
        document.addEventListener('DOMContentLoaded', function() {
            const prioritySelect = document.getElementById('priority');
            prioritySelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const priorityClass = selectedOption.value === 'high' ? 'priority-high' : 
                                     selectedOption.value === 'medium' ? 'priority-medium' : 'priority-low';
                this.style.borderLeft = `4px solid var(--${priorityClass})`;
            });
            
            // Trigger initial state
            prioritySelect.dispatchEvent(new Event('change'));
        });
    </script>
</body>
</html>