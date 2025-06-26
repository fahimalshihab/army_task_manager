<?php
require_once 'includes/db.php';
session_start();

// Only admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $user_id = (int)$_GET['delete_user']; // Force integer type
    
    // Prevent admin from deleting themselves
    if ($user_id != $_SESSION['user_id']) {
        try {
            $pdo->beginTransaction();
            
            // First delete dependent tasks
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE assigned_to = ? OR assigned_by = ?");
            $stmt->execute([$user_id, $user_id]);
            
            // Then delete the user
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            
            $pdo->commit();
            header('Location: manage_users.php?deleted=1');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            header('Location: manage_users.php?error=1');
            exit;
        }
    } else {
        header('Location: manage_users.php?error=self_delete');
        exit;
    }
}

// Get all users except current admin
$users = $pdo->query("
    SELECT id, username, email, user_type 
    FROM users 
    WHERE id != ".$_SESSION['user_id']."
    ORDER BY user_type, username
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Army Task Command</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --military-dark: #0a0f0d;
            --military-green: #2a4a3c;
            --military-light: #8b9e7e;
            --military-accent: #c5a73d;
            --military-red: #8c2e0b;
            --military-gray: #3a3a3a;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--military-dark);
            color: white;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .user-card {
            background: rgba(42, 74, 60, 0.7);
            border: 1px solid var(--military-gray);
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .user-type-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
            text-transform: uppercase;
        }
        
        .admin-badge { background: var(--military-red); color: white; }
        .officer-badge { background: var(--military-accent); color: var(--military-dark); }
        .soldier-badge { background: var(--military-green); color: white; }
        
        .delete-btn {
            background: var(--military-red);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .delete-btn:hover {
            background: #c0392b;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.2);
            border-left: 4px solid #2ecc71;
        }
        
        .alert-error {
            background-color: rgba(231, 76, 60, 0.2);
            border-left: 4px solid var(--military-red);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h1><i class="fas fa-users-cog"></i> User Management</h1>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> User successfully terminated from the system.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php if ($_GET['error'] == '1'): ?>
                    <i class="fas fa-exclamation-triangle"></i> Error terminating user. Please try again.
                <?php elseif ($_GET['error'] == 'self_delete'): ?>
                    <i class="fas fa-exclamation-triangle"></i> You cannot terminate your own account.
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="user-list">
            <?php if (empty($users)): ?>
                <div class="user-card">
                    <p>No users found in the system.</p>
                </div>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <div class="user-card">
                        <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                        
                        <span class="user-type-badge <?php echo $user['user_type'] ?>-badge">
                            <?php echo ucfirst($user['user_type']); ?>
                        </span>
                        
                        <form method="GET" style="display: inline;">
                            <input type="hidden" name="delete_user" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="delete-btn" 
                                    onclick="return confirm('Terminate user <?php echo htmlspecialchars(addslashes($user['username'])); ?>? This action cannot be undone.')">
                                <i class="fas fa-user-slash"></i> Terminate
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>