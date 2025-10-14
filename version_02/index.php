<?php
// Start session (good practice, even if not immediately used here)
session_start();

// Include your common functions if needed (e.g., user_message)
require_once '../assets/common.php';
require_once '../assets/dbconn.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Home - Primary Oaks Surgery</title>
    <link rel="stylesheet" href="../css/styles.css">  <!-- Path relative to index.php -->
</head>
<body>

<!-- Include the topbar structure -->
<?php require_once '../assets/topbar.php'; ?>

<!-- Include the navigation structure -->
<?php require_once '../assets/nav.php'; ?>

<!-- Include the content wrapper structure -->
<?php require_once '../assets/content.php'; ?>

<h1>Welcome to Primary Oaks Surgery</h1>
<?php echo '<hr>';?>
<p>Manage your appointments securely online.</p>

<!-- Portal Links (Register/Login) - Only show if NOT logged in -->
<?php if (!isset($_SESSION['user_id'])): ?>
    <div class="portal-links">
        <a href="register.php">REGISTER</a>
        <a href="login.php">LOGIN</a>
    </div>
<?php else: ?>
    <!-- Show a welcome message and a link to the dashboard/homepage if logged in -->
    <p>Hello, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES, 'UTF-8'); ?>!</p>
    <a href="index.php">Go to Homepage</a> <!-- Or wherever the main logged-in page is -->
    <!-- The logout link will be in the nav bar, handled by nav.php -->
<?php endif; ?>

<!-- Close the content wrapper div (from content.php) -->
</div>

</body>
</html>