<?php
// assets/nav.php
// This file outputs the navigation bar HTML structure.
// It should be included using require_once.
// It checks the session to decide whether to show Logout.

// Ensure session is started if not already (good practice in included files that might use sessions)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="login.php">Login</a></li>
        <!-- Conditionally show Logout link if user is logged in -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="logout.php">Logout</a></li>
        <?php endif; ?>
        <!-- Add other navigation links as needed for future features -->
        <!-- <li><a href="book.php">Book</a></li> -->
        <!-- <li><a href="bookings.php">Bookings</a></li> -->
    </ul>
</nav>