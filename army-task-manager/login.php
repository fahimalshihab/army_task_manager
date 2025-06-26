<?php
require_once 'includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Access code required';
    }
    
    if (empty($password)) {
        $errors[] = 'Authentication token required';
    }
    
    if (empty($errors)) {
        // Check user credentials
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Authentication successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            
            // Redirect to dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid credentials - access denied';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Access - Army Task Command</title>
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
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Rajdhani', sans-serif;
            color: white;
            background-color: var(--military-dark);
            background-image: 
                linear-gradient(rgba(10, 15, 13, 0.9), rgba(10, 15, 13, 0.9)),
                url('https://images.unsplash.com/photo-1543351611-58f69d7c1781?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            background: rgba(42, 74, 60, 0.7);
            border: 1px solid var(--military-accent);
            border-radius: 8px;
            padding: 2.5rem;
            box-shadow: 0 0 20px rgba(197, 167, 61, 0.2);
            backdrop-filter: blur(5px);
            position: relative;
            overflow: hidden;
        }
        
        .login-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--military-accent);
        }
        
        h2 {
            font-family: 'Orbitron', sans-serif;
            color: var(--military-accent);
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .access-denied {
            color: var(--military-red);
            background: rgba(140, 46, 11, 0.2);
            border-left: 3px solid var(--military-red);
            padding: 0.8rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .access-granted {
            color: var(--military-light);
            background: rgba(139, 158, 126, 0.2);
            border-left: 3px solid var(--military-light);
            padding: 0.8rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--military-light);
            letter-spacing: 0.5px;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            background: rgba(10, 15, 13, 0.7);
            border: 1px solid var(--military-gray);
            border-radius: 4px;
            color: white;
            font-family: 'Rajdhani', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--military-accent);
            box-shadow: 0 0 0 2px rgba(197, 167, 61, 0.3);
        }
        
        .form-group input::placeholder {
            color: var(--military-gray);
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 0.8rem;
            background: var(--military-accent);
            color: var(--military-dark);
            border: none;
            border-radius: 4px;
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        
        button[type="submit"]:hover {
            background: #d9b83b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--military-light);
            font-size: 0.9rem;
        }
        
        .login-footer a {
            color: var(--military-accent);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .security-level {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            font-size: 0.8rem;
            color: var(--military-light);
        }
        
        @media (max-width: 500px) {
            .login-container {
                padding: 1.5rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <div class="login-container">
            <h2><i class="fas fa-user-shield"></i> SECURE ACCESS</h2>
            
            <?php if (isset($_GET['registered'])): ?>
                <div class="access-granted">
                    <i class="fas fa-check-circle"></i> Registration confirmed. Proceed with authentication.
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="access-denied">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $errors[0]; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username"><i class="fas fa-id-card"></i> ACCESS CODE</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your access code">
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-key"></i> AUTHENTICATION TOKEN</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your token">
                </div>
                
                <button type="submit">AUTHENTICATE</button>
                
                <div class="security-level">
                    <span>SECURITY LEVEL: ALPHA</span>
                    <span>ENCRYPTION: AES-256</span>
                </div>
            </form>
            
            <div class="login-footer">
                <p>No access credentials? <a href="register.php">Request clearance</a></p>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Add subtle security animation
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.background = 'rgba(10, 15, 13, 0.9)';
                });
                input.addEventListener('blur', function() {
                    this.style.background = 'rgba(10, 15, 13, 0.7)';
                });
            });
        });
    </script>
</body>
</html>