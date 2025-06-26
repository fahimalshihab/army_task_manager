<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $user_type = $_POST['user_type'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Identification code required';
    }
    
    if (empty($email)) {
        $errors[] = 'Contact frequency required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid frequency format';
    }
    
    if (empty($password)) {
        $errors[] = 'Encryption key required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Encryption key must be at least 6 characters';
    }
    
    if (empty($errors)) {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $errors[] = 'Identification already in use - clearance denied';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password, $user_type])) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $errors[] = 'Clearance request failed. Stand by.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Clearance - Army Task Command</title>
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
                url('https://images.unsplash.com/photo-1534796636912-3b95b3ab5986?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
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
        
        .clearance-container {
            width: 100%;
            max-width: 500px;
            background: rgba(42, 74, 60, 0.7);
            border: 1px solid var(--military-accent);
            border-radius: 8px;
            padding: 2.5rem;
            box-shadow: 0 0 20px rgba(197, 167, 61, 0.2);
            backdrop-filter: blur(5px);
            position: relative;
            overflow: hidden;
        }
        
        .clearance-container::before {
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
        
        .clearance-denied {
            color: var(--military-red);
            background: rgba(140, 46, 11, 0.2);
            border-left: 3px solid var(--military-red);
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
        
        .form-group input, 
        .form-group select {
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
        
        .form-group select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23c5a73d'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.7rem top 50%;
            background-size: 1.5rem;
        }
        
        .form-group input:focus,
        .form-group select:focus {
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
        
        .clearance-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--military-light);
            font-size: 0.9rem;
        }
        
        .clearance-footer a {
            color: var(--military-accent);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .clearance-footer a:hover {
            text-decoration: underline;
        }
        
        .security-badge {
            display: inline-block;
            background: rgba(10, 15, 13, 0.7);
            border: 1px solid var(--military-accent);
            padding: 0.3rem 0.6rem;
            border-radius: 3px;
            font-size: 0.8rem;
            margin-top: 0.5rem;
            color: var(--military-accent);
        }
        
        @media (max-width: 500px) {
            .clearance-container {
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
        <div class="clearance-container">
            <h2><i class="fas fa-user-secret"></i> REQUEST CLEARANCE</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="clearance-denied">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $errors[0]; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username"><i class="fas fa-id-badge"></i> IDENTIFICATION CODE</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your call sign">
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-satellite-dish"></i> CONTACT FREQUENCY</label>
                    <input type="email" id="email" name="email" required placeholder="Enter secure comms channel">
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-key"></i> ENCRYPTION KEY</label>
                    <input type="password" id="password" name="password" required placeholder="Minimum 6 characters">
                    <span class="security-badge">AES-256 ENCRYPTED</span>
                </div>
                
                <div class="form-group">
                    <label for="user_type"><i class="fas fa-chess-queen"></i> CLEARANCE LEVEL</label>
                    <select id="user_type" name="user_type" required>
                        <option value="soldier">Soilder (Field Operative)</option>
                        <option value="officer">Officer (Mission Commander)</option>
                        <option value="admin">Admin (Full Access)</option>
                    </select>
                </div>
                
                <button type="submit">REQUEST CLEARANCE</button>
                
                <div class="clearance-footer">
                    <p>Already have clearance? <a href="login.php">Access command center</a></p>
                </div>
            </form>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Add security validation animation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> PROCESSING REQUEST';
                submitBtn.disabled = true;
            });
        });
    </script>
</body>
</html>