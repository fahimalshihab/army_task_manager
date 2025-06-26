<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: logout.php');
    exit;
}

// Handle profile updates
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    // Validate inputs
    if (empty($username)) {
        $errors[] = 'Username is required';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }
    
    // Only validate passwords if they're provided
    if (!empty($current_password) || !empty($new_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = 'Current password is incorrect';
        }
        
        if (strlen($new_password) < 6) {
            $errors[] = 'New password must be at least 6 characters';
        }
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Prepare base update
            $update_data = [
                'username' => $username,
                'email' => $email,
                'id' => $_SESSION['user_id']
            ];
            
            $sql = "UPDATE users SET username = :username, email = :email";
            
            // Add password update if needed
            if (!empty($new_password)) {
                $sql .= ", password = :password";
                $update_data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($update_data);
            
            $pdo->commit();
            $success = true;
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Army Task Command</title>
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
            font-family: 'Arial', sans-serif;
            background-color: var(--military-dark);
            color: white;
            margin: 0;
            padding: 0;
        }
        
        .profile-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: rgba(42, 74, 60, 0.7);
            border: 1px solid var(--military-accent);
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(197, 167, 61, 0.2);
        }
        
        h1 {
            color: var(--military-accent);
            border-bottom: 2px solid var(--military-accent);
            padding-bottom: 10px;
            font-family: 'Orbitron', sans-serif;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: var(--military-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 2.5rem;
            color: var(--military-light);
            border: 3px solid var(--military-accent);
        }
        
        .profile-info {
            flex: 1;
        }
        
        .user-role {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.8rem;
            margin-top: 5px;
        }
        
        .admin-role { background: var(--military-red); }
        .officer-role { background: var(--military-accent); color: var(--military-dark); }
        .soldier-role { background: var(--military-green); }
        
        .profile-form {
            margin-top: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--military-light);
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            background: rgba(10, 15, 13, 0.7);
            border: 1px solid var(--military-gray);
            border-radius: 4px;
            color: white;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: var(--military-accent);
            color: var(--military-dark);
        }
        
        .btn-primary:hover {
            background: #d9b83b;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            border-left: 4px solid #2ecc71;
        }
        
        .alert-error {
            background: rgba(231, 76, 60, 0.2);
            border-left: 4px solid var(--military-red);
        }
        
        .password-toggle {
            cursor: pointer;
            color: var(--military-light);
            font-size: 0.9rem;
            margin-top: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="profile-container">
        <h1><i class="fas fa-user-circle"></i> USER PROFILE</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Profile updated successfully!
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> 
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                <span class="user-role <?php echo $user['user_type'] ?>-role">
                    <?php echo ucfirst($user['user_type']); ?>
                </span>
            </div>
        </div>
        
        <form class="profile-form" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" 
                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            
            <h3><i class="fas fa-lock"></i> Password Update</h3>
            <p class="password-message">Leave blank to keep current password</p>
            
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password">
                <span class="password-toggle" onclick="togglePassword('current_password')">
                    <i class="fas fa-eye"></i> Show Password
                </span>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password">
                <span class="password-toggle" onclick="togglePassword('new_password')">
                    <i class="fas fa-eye"></i> Show Password
                </span>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Profile
            </button>
        </form>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                input.nextElementSibling.innerHTML = '<i class="fas fa-eye-slash"></i> Hide Password';
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                input.nextElementSibling.innerHTML = '<i class="fas fa-eye"></i> Show Password';
            }
        }
    </script>
</body>
</html>
