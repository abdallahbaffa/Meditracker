<?php
// Start session to manage user login state
session_start();

// Include your database connection function
require_once '../assets/dbconn.php'; // Adjust path if needed

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
        $feedback = "Login failed. Please check your details and try again.";
    } else {
        try {
            // Get database connection using your function
            $pdo = dbconnect_insert();

            // Prepare statement to find user by email (using prepared statement for security - NFR2)
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

                // Redirect to the dashboard or home page after successful login
                header("Location: home.php"); // Or wherever you want them to go after login
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
echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<title>Login - Primary Oaks Surgery</title>';
// Link to the external stylesheet (assuming you'll add one later)
// echo '<link rel="stylesheet" href="../css/styles.css">'; // Add this line when you create styles.css
echo '<style>
    .feedback-error { color: red; padding: 10px; border: 1px solid red; background-color: #ffe6e6; }
</style>';
echo '</head>';
echo '<body>';
echo '<h1>Login to Your Account</h1>';
echo '<hr>';

// Display error message if set
if (!empty($feedback)) {
    echo "<div class='feedback-error'>";
    echo "<strong>❌ Error:</strong> $feedback";
    echo "</div><br>";
}

// The login form
echo '<form action="login.php" method="POST">';
echo '<label for="email">Email Address:</label><br>';
echo '<input type="email" id="email" name="email" value="' . htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') . '" required><br><br>'; // Pre-fill email and make required

echo '<label for="password">Password:</label><br>';
echo '<input type="password" id="password" name="password" required><br><br>'; // Make password required, don't pre-fill for security

echo '<button type="submit">Login</button>';
echo '</form>';

echo '<br>';
echo '<a href="index.php">Back to Home</a>';
echo '<br>';
echo '<a href="register.php">Need an account? Register here</a>'; // Link back to registration

echo '</body>';
echo '</html>';
?>