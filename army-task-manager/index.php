<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Army Task Manager - Mission Control System</title>
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
                url('https://images.unsplash.com/photo-1509803874385-db7c23652552?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
            line-height: 1.6;
        }
        
        header {
            background-color: rgba(10, 15, 13, 0.85);
            border-bottom: 2px solid var(--military-accent);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        h1 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            color: var(--military-accent);
            margin: 0;
            font-size: 1.8rem;
            letter-spacing: 1px;
            text-shadow: 0 0 10px rgba(197, 167, 61, 0.5);
        }
        
        h1::before {
            content: "⚔️";
            margin-right: 10px;
        }
        
        nav {
            display: flex;
            gap: 1.5rem;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            font-size: 1.1rem;
            letter-spacing: 1px;
            padding: 0.5rem 1rem;
            border: 1px solid transparent;
            border-radius: 4px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        nav a:hover {
            color: var(--military-accent);
            border-color: var(--military-accent);
        }
        
        nav a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: var(--military-accent);
            transition: width 0.3s ease;
        }
        
        nav a:hover::after {
            width: 70%;
        }
        
        main {
            min-height: calc(100vh - 150px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 2rem;
        }
        
        .hero {
            max-width: 800px;
            background: rgba(42, 74, 60, 0.7);
            border: 1px solid var(--military-accent);
            border-radius: 8px;
            padding: 3rem 2rem;
            box-shadow: 0 0 20px rgba(197, 167, 61, 0.2);
            backdrop-filter: blur(5px);
            animation: pulse 6s infinite alternate;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 20px rgba(197, 167, 61, 0.2);
            }
            100% {
                box-shadow: 0 0 30px rgba(197, 167, 61, 0.4);
            }
        }
        
        .hero h2 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
            color: white;
            text-shadow: 0 2px 5px rgba(0,0,0,0.5);
            letter-spacing: 1px;
        }
        
        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            color: var(--military-light);
        }
        
        .cta-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.8rem 1.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
        }
        
        .btn-primary {
            background-color: var(--military-accent);
            color: var(--military-dark);
        }
        
        .btn-secondary {
            background-color: transparent;
            color: var(--military-accent);
            border-color: var(--military-accent);
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .btn:active {
            transform: translateY(-1px);
        }
        /* Notice Board Styles */
.notice-board {
    background: rgba(42, 74, 60, 0.7);
    border: 1px solid var(--military-accent);
    border-radius: 8px;
    padding: 1.5rem;
    margin: 2rem auto;
    max-width: 800px;
    width: 90%;
}

.notice-board h3 {
    font-family: 'Orbitron', sans-serif;
    color: var(--military-accent);
    margin-top: 0;
    border-bottom: 1px solid var(--military-gray);
    padding-bottom: 0.5rem;
}

.notices-container {
    text-align: left;
}

.notice {
    padding: 0.8rem;
    margin: 0.5rem 0;
    border-left: 4px solid;
    background: rgba(10, 15, 13, 0.5);
}

.notice.alert {
    border-left-color: var(--military-red);
}

.notice.info {
    border-left-color: var(--military-light);
}

.notice.warning {
    border-left-color: var(--military-accent);
}

.notice-date {
    color: var(--military-light);
    font-size: 0.8rem;
    margin-right: 1rem;
}

.notice-priority {
    font-weight: bold;
    margin-right: 0.5rem;
}

/* Gallery Styles */
.operations-gallery {
    background: rgba(42, 74, 60, 0.7);
    border: 1px solid var(--military-accent);
    border-radius: 8px;
    padding: 1.5rem;
    margin: 2rem auto;
    max-width: 800px;
    width: 90%;
}

.operations-gallery h3 {
    font-family: 'Orbitron', sans-serif;
    color: var(--military-accent);
    margin-top: 0;
    border-bottom: 1px solid var(--military-gray);
    padding-bottom: 0.5rem;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.gallery-item {
    height: 180px;
    background-size: cover;
    background-position: center;
    position: relative;
    border: 1px solid var(--military-gray);
    transition: transform 0.3s ease;
}

.gallery-item:hover {
    transform: scale(1.03);
    cursor: pointer;
}

.gallery-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(10, 15, 13, 0.8);
    padding: 0.5rem;
    font-size: 0.9rem;
}

/* Status Dashboard Styles */
.status-dashboard {
    background: rgba(42, 74, 60, 0.7);
    border: 1px solid var(--military-accent);
    border-radius: 8px;
    padding: 1.5rem;
    margin: 2rem auto;
    max-width: 800px;
    width: 90%;
}

.status-dashboard h3 {
    font-family: 'Orbitron', sans-serif;
    color: var(--military-accent);
    margin-top: 0;
    border-bottom: 1px solid var(--military-gray);
    padding-bottom: 0.5rem;
}

.status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.status-item {
    text-align: center;
    padding: 1rem;
    background: rgba(10, 15, 13, 0.5);
    border-radius: 4px;
    border-top: 3px solid var(--military-accent);
}

.status-value {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--military-accent);
    font-family: 'Orbitron', sans-serif;
}

.status-label {
    font-size: 0.9rem;
    color: var(--military-light);
}

/* Upcoming Events Styles */
.upcoming-events {
    background: rgba(42, 74, 60, 0.7);
    border: 1px solid var(--military-accent);
    border-radius: 8px;
    padding: 1.5rem;
    margin: 2rem auto;
    max-width: 800px;
    width: 90%;
}

.upcoming-events h3 {
    font-family: 'Orbitron', sans-serif;
    color: var(--military-accent);
    margin-top: 0;
    border-bottom: 1px solid var(--military-gray);
    padding-bottom: 0.5rem;
}

.events-list {
    margin-top: 1rem;
}

.event-item {
    display: flex;
    align-items: center;
    padding: 0.8rem 0;
    border-bottom: 1px dashed var(--military-gray);
}

.event-item:last-child {
    border-bottom: none;
}

.event-date {
    background: var(--military-accent);
    color: var(--military-dark);
    font-weight: bold;
    padding: 0.5rem;
    border-radius: 4px;
    min-width: 60px;
    text-align: center;
    margin-right: 1rem;
    font-family: 'Orbitron', sans-serif;
}

.event-title {
    font-weight: bold;
    color: white;
}

.event-time {
    font-size: 0.9rem;
    color: var(--military-light);
}

@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: 1fr;
    }
    
    .status-grid {
        grid-template-columns: 1fr 1fr;
    }
}
        
        footer {
            background-color: rgba(10, 15, 13, 0.9);
            color: var(--military-light);
            text-align: center;
            padding: 1.5rem;
            font-size: 0.9rem;
            border-top: 1px solid var(--military-gray);
        }
        
        footer p {
            margin: 0;
        }
        
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                padding: 1rem;
            }
            
            h1 {
                margin-bottom: 1rem;
            }
            
            nav {
                width: 100%;
                justify-content: space-around;
                gap: 0.5rem;
            }
            
            .hero {
                padding: 2rem 1rem;
            }
            
            .hero h2 {
                font-size: 1.8rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                gap: 1rem;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>ARMY TASK COMMAND</h1>
        <nav>
            <a href="index.php"><i class="fas fa-home"></i> HQ</a>
            <a href="login.php"><i class="fas fa-user-shield"></i> Secure Access</a>
            <a href="register.php"><i class="fas fa-user-plus"></i> Enlist</a>
        </nav>
    </header>
    
    <main>
        <section class="hero">
            <h2>MISSION CONTROL SYSTEM</h2>
            <p>Strategic task delegation and operational coordination for military units</p>
            
            <div class="cta-buttons">
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> COMMAND LOGIN
                </a>
                <a href="register.php" class="btn btn-secondary">
                    <i class="fas fa-clipboard-check"></i> REQUEST ACCESS
                </a>
            </div>
        </section>

        <section class="notice-board">
    <h3><i class="fas fa-bullhorn"></i> COMMAND NOTICES</h3>
    <div class="notices-container">
        <div class="notice alert">
            <span class="notice-date">[2023-11-15]</span>
            <span class="notice-priority">ALERT:</span> All units maintain radio silence during OP-EXERCISE
        </div>
        <div class="notice info">
            <span class="notice-date">[2023-11-14]</span>
            <span class="notice-priority">INFO:</span> Mandatory briefing for all officers at 0800 tomorrow
        </div>
        <div class="notice warning">
            <span class="notice-date">[2023-11-13]</span>
            <span class="notice-priority">WARNING:</span> Weather conditions deteriorating in Sector 7
        </div>
    </div>
</section>


<section class="operations-gallery">
    <h3><i class="fas fa-camera-retro"></i> RECENT OPERATIONS</h3>
    <div class="gallery-grid">
        <div class="gallery-item" style="background-image: url('https://static.independent.co.uk/s3fs-public/thumbnails/image/2017/03/21/11/kim-jong-un.jpg?quality=75&width=1368&crop=3%3A2%2Csmart&auto=webp')">
            <div class="gallery-caption">Lets destroy USA</div>
        </div>
        <div class="gallery-item" style="background-image: url('https://plus.unsplash.com/premium_photo-1682097238346-3f2a677ccfe6?q=80&w=1470&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D')">
            <div class="gallery-caption">Urban Defense Drills</div>
        </div>
        <div class="gallery-item" style="background-image: url('https://www.rollingstone.com/wp-content/uploads/2018/06/donald-trump-disrespects-troops-c61a73a8-6d13-4914-8265-f8e125207d37.jpg?w=1581&h=1054&crop=1')">
            <div class="gallery-caption">Make USA Great never</div>
        </div>
    </div>
</section>

<section class="status-dashboard">
    <h3><i class="fas fa-tachometer-alt"></i> OPERATIONAL STATUS</h3>
    <div class="status-grid">
        <div class="status-item">
            <div class="status-value">12</div>
            <div class="status-label">ACTIVE MISSIONS</div>
        </div>
        <div class="status-item">
            <div class="status-value">47</div>
            <div class="status-label">PERSONNEL ONLINE</div>
        </div>
        <div class="status-item">
            <div class="status-value">3</div>
            <div class="status-label">CRITICAL ALERTS</div>
        </div>
        <div class="status-item">
            <div class="status-value">100%</div>
            <div class="status-label">SYSTEM STATUS</div>
        </div>
    </div>
</section>


<section class="upcoming-events">
    <h3><i class="fas fa-calendar-alt"></i> OPERATIONAL CALENDAR</h3>
    <div class="events-list">
        <div class="event-item">
            <div class="event-date">NOV 18</div>
            <div class="event-details">
                <div class="event-title">Field Training Exercise</div>
                <div class="event-time">0600 - Sector 4</div>
            </div>
        </div>
        <div class="event-item">
            <div class="event-date">NOV 20</div>
            <div class="event-details">
                <div class="event-title">Equipment Maintenance</div>
                <div class="event-time">All day - Motor Pool</div>
            </div>
        </div>
        <div class="event-item">
            <div class="event-date">NOV 22</div>
            <div class="event-details">
                <div class="event-title">Command Briefing</div>
                <div class="event-time">0900 - War Room</div>
            </div>
        </div>
    </div>
</section>


    </main>
    
    <footer>
        <p>OPERATION: TASK COMMANDER &copy; <?php echo date('Y'); ?> | CLASSIFIED</p>
    </footer>
    
    <script src="js/script.js"></script>
    <script>
        // Add subtle typewriter effect to hero text
        document.addEventListener('DOMContentLoaded', function() {
            const heroText = document.querySelector('.hero h2');
            const originalText = heroText.textContent;
            heroText.textContent = '';
            
            let i = 0;
            const typing = setInterval(function() {
                if (i < originalText.length) {
                    heroText.textContent += originalText.charAt(i);
                    i++;
                } else {
                    clearInterval(typing);
                }
            }, 50);
        });
    </script>
</body>
</html>