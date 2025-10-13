<?php
// Start session to manage user login state
session_start();

// Include your database connection function
require_once '../assets/dbconn.php'; // Adjust path if needed

// Initialize variables
$feedback = "";
$email = "";
$password = "";
$loginSuccess = false; // Flag to track successful login

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

                // Set the flag to indicate successful login
                $loginSuccess = true;
                // Do NOT redirect here. Let the page reload and display the welcome message.
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

// Check if user is already logged in when the page loads (optional, for direct access)
if (isset($_SESSION['user_id']) && !isset($_GET['logout'])) { // Check for a logout parameter if you add a logout link later
    $loginSuccess = true;
    // $user_name is already in the session
}

// --- HTML Output ---
echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<title>Login - Primary Oaks Surgery</title>';
// Link to the external stylesheet
echo '<link rel="stylesheet" href="../css/styles.css">'; // Adjust path relative to login.php location
echo '</head>';
echo '<body>';

// Include the topbar structure
require_once '../assets/topbar.php';

// Include the navigation structure
require_once '../assets/nav.php';

// Include the content wrapper structure
require_once '../assets/content.php';

// Check the login success flag
if ($loginSuccess) {
    $user_name = $_SESSION['user_name'] ?? 'User'; // Fallback to 'User' if name isn't set
    echo '<h1>Welcome, ' . htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') . '!</h1>';
    echo '<p>You are now logged into the Primary Oaks Surgery portal.</p>';
    // Add links to other parts of the system here (e.g., book appointment, view appointments)
    echo '<a href="#">Book an Appointment</a> <!-- Placeholder link -->';
    echo '<br><br>';
    // Add a logout link that redirects to logout.php - ONLY if logged in
    if (isset($_SESSION['user_id'])) {
        echo '<a href="logout.php">Logout</a> <!-- Link to logout script -->';
    }
} else {
    // Display the login form and any feedback if not logged in
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
    // You might want to remove the direct links if you have the navigation bar
    // echo '<a href="index.php">Back to Home</a>';
    // echo '<a href="register.php">Need an account? Register here</a>'; // Link back to registration
}

// Close the content wrapper div (from content.php)
echo '</div>';

echo '</body>';
echo '</html>';
?>