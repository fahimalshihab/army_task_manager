<header>
    <h1><i class="fas fa-helmet-battle"></i> ARMY TASK COMMAND</h1>
    <nav>
        <a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a>

        <a href="index.php"><i class="fas fa-home"></i> HQ</a>
        <a href="dashboard.php"><i class="fas fa-command"></i> DASHBOARD</a>
        <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_type'] == 'officer' || $_SESSION['user_type'] == 'admin')): ?>
            <a href="progress.php"><i class="fas fa-medal"></i> PROGRESS</a>
        <?php endif; ?>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == 'admin'): ?>
    <a href="manage_users.php"><i class="fas fa-users-cog"></i> Manage Users</a>
<?php endif; ?>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="login.php"><i class="fas fa-user-shield"></i> LOGIN</a>
            <a href="register.php"><i class="fas fa-user-plus"></i> REGISTER</a>
        <?php else: ?>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> LOGOUT</a>
        <?php endif; ?>
    </nav>
</header>

<style>
    header {
        background: rgba(10, 15, 13, 0.9);
        border-bottom: 2px solid var(--military-accent);
        padding: 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    h1 {
        font-family: 'Orbitron', sans-serif;
        color: var(--military-accent);
        margin: 0;
        font-size: 1.5rem;
        letter-spacing: 1px;
    }
    nav {
        display: flex;
        gap: 1.5rem;
    }
    nav a {
        color: var(--military-light);
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s;
        padding: 0.5rem 0;
        border-bottom: 2px solid transparent;
    }
    nav a:hover {
        color: var(--military-accent);
        border-bottom-color: var(--military-accent);
    }
    nav a i {
        font-size: 0.9em;
    }
</style>