<?php
// Start session to manage user login state
session_start();
// Include your database connection function
require_once '../assets/dbconn.php'; // Adjust path if needed
// Include your common functions (to use user_message if needed, though not strictly required for Part 1)
// require_once '../assets/common.php'; // We won't use audit_write in Part 1
// Initialize variables
$feedback = "";
$email = "";
$password = "";
// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $email = trim(filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL));
    $password = filter_input(INPUT_POST, "password", FILTER_UNSAFE_RAW);
    // Basic validation: Check if fields are empty
    if (empty($email) || empty($password)) {
        $feedback = "Login failed. Please check your details and try again."; // Generic error
    } else {
        try {
            // Get database connection using your function
            $pdo = dbconnect_insert();
            // Prepare statement to find user by email (using prepared statement for security)
            $stmt = $pdo->prepare("SELECT user_id, full_name, password_hash FROM users WHERE email_address = ?");
            $stmt->execute([$email]);
            // Fetch the user record (if it exists)
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            // Check if a user was found AND if the password is correct using password_verify
            if ($user && password_verify($password, $user['password_hash'])) {
                // Success! User is authenticated.
                // Set session variables to remember the user (you can store user_id, name, etc.)
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['full_name']; // Optional: for personalized greeting
                // Redirect to index.php or a welcome page after successful login
                header("Location: index.php");
                exit(); // Important: Stop execution after redirect
            } else {
                // If no user found OR password is incorrect, show generic error (NFR2)
                $feedback = "Login failed. Please check your details and try again.";
            }
        } catch (Exception $e) {
            // Generic error in case of database failure
            $feedback = "Login failed. Please try again.";
            // Optional: Log the specific error for debugging: error_log("Login Error: " . $e->getMessage());
        }
    }
}
// --- HTML Output ---
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Primary Oaks Surgery</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- Adjust path relative to login.php location -->
</head>
<body>
<!-- Include the topbar structure -->
<?php require_once '../assets/topbar.php'; ?>
<!-- Include the navigation structure -->
<?php require_once '../assets/nav.php'; ?>
<!-- Include the content wrapper structure -->
<?php require_once '../assets/content.php'; ?>

<h1>Login to Your Account</h1>
<hr>
<!-- Display error message if set -->
<?php if (!empty($feedback)): ?>
    <div class='feedback-error'>
        <strong>❌ Error:</strong> <?php echo $feedback; ?>
    </div><br>
<?php endif; ?>

<!-- The login form -->
<form action="login.php" method="POST">
    <label for="email">Email Address:</label><br>
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required><br><br> <!-- Pre-fill email and make required -->
    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password" required><br><br> <!-- Make password required, don't pre-fill for security -->
    <button type="submit">Login</button>
</form>
<br>
<!-- You might want to remove the direct links if you have the navigation bar -->
<!-- <a href="index.php">Back to Home</a> -->
<!-- <a href="register.php">Need an account? Register here</a> --> <!-- Link back to registration -->

<!-- Close the content wrapper div (from content.php) -->
</div>
</body>
</html>