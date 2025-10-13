<?php
// Start a session to potentially store messages (optional, but good practice)
session_start();

// Include your database connection function
require_once '../assets/dbconn.php'; // Adjusted path to match your assets folder

// --- 1. Initialize variables and Check for Submission ---
$feedback = "";
$password_raw = ""; // To hold the submitted password for re-display/checking

// Check if the form has been submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 2. Get and Sanitize Input ---
    // Get and sanitize the raw inputs
    $full_name_raw = filter_input(INPUT_POST, "fullName", FILTER_UNSAFE_RAW);
    $email_raw = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL); // Sanitize email
    $password_raw = filter_input(INPUT_POST, "password", FILTER_UNSAFE_RAW);
    $confirmPassword_raw = filter_input(INPUT_POST, "confirmPassword", FILTER_UNSAFE_RAW);

    // Also trim whitespace
    $full_name_raw = trim($full_name_raw);
    $email_raw = trim($email_raw);
    $password_raw = trim($password_raw);
    $confirmPassword_raw = trim($confirmPassword_raw);

    // Escape special HTML chars for safe output and checking (IMPORTANT for preventing XSS)
    $full_name = htmlspecialchars($full_name_raw, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email_raw, ENT_QUOTES, 'UTF-8');
    // Note: $password_raw is not escaped here as it will be hashed, not stored/sent back raw.

    // --- 3. Initial Validation (Empty checks, password match) ---
    if (empty($full_name) || empty($email) || empty($password_raw)) {
        $feedback .= "All fields are required.<br>";
    }

    // Check if passwords match
    if ($password_raw !== $confirmPassword_raw) {
        $feedback .= "Passwords do not match.<br>";
    }

    // Continue with password strength checks only if the fields aren't empty and passwords match
    if (empty($feedback) && !empty($password_raw) && $password_raw === $confirmPassword_raw) {
        // --- 4. Password Strength Rules (Mirror your pass_check.php logic) ---
        // Rule 1: Check if the password contains the word "password" (case-insensitive)
        if (stripos($password_raw, 'password') !== false) { // Use raw input for checks
            $feedback .= "Password cannot contain the word 'password'.<br>";
        }

        // Rule 2: Check the length (greater than 8 characters)
        if (strlen($password_raw) < 9) { // Use raw input for checks
            $feedback .= "Password must be greater than 8 characters.<br>";
        }

        // Rule 3: Check for uppercase
        if (!preg_match('/[A-Z]/', $password_raw)) { // Use raw input for checks
            $feedback .= "Password must contain at least one uppercase letter.<br>";
        }

        // Rule 4: Check for lowercase
        if (!preg_match('/[a-z]/', $password_raw)) { // Use raw input for checks
            $feedback .= "Password must contain at least one lowercase letter.<br>";
        }

        // Rule 5: Check for a number
        if (!preg_match('/[0-9]/', $password_raw)) { // Use raw input for checks
            $feedback .= "Password must contain at least one number.<br>";
        }

        // Rule 6: Check for a special character
        if (!preg_match('/[^a-zA-Z0-9]/', $password_raw)) { // Use raw input for checks
            $feedback .= "Password must contain at least one special character.<br>";
        }

        // Rule 7 & 8: Check the first character (must not be a number or special char)
        if (isset($password_raw[0])) { // Use raw input for checks
            if (is_numeric($password_raw[0])) { // Rule 7
                $feedback .= "First character cannot be a number.<br>";
            } elseif (!ctype_alnum($password_raw[0])) { // Rule 8
                $feedback .= "First character cannot be a special character.<br>";
            }
        }

        // Rule 9: Check the last character (must not be a special character)
        $len = strlen($password_raw); // Use raw input for checks
        if ($len > 0 && !ctype_alnum($password_raw[$len - 1])) { // Use raw input for checks
            $feedback .= "Last character cannot be a special character.<br>";
        }
    } // End of password check block (only runs if no initial errors and passwords match)

    // --- 5. Database Interaction (If all checks pass) ---
    if (empty($feedback)) {
        try {
            // Get database connection using your function
            $pdo = dbconnect_insert(); // Calls your function from dbconn.php

            // Check if email already exists (FR2: Unique email)
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email_address = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                // Generic error as per brief to prevent leaking information
                $feedback = "Registration failed. Please try again.";
            } else {
                // Hash the password securely (NFR1: Hashed passwords)
                $password_hash = password_hash($password_raw, PASSWORD_BCRYPT);

                // Insert the new user into the database using prepared statements (NFR2: Prevent SQL Injection)
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email_address, password_hash) VALUES (?, ?, ?)");
                $stmt->execute([$full_name, $email, $password_hash]);

                // Success! Set a session message and redirect
                $_SESSION['msg'] = "✅ Registration successful! You can now log in.";
                header("Location: login.php"); // Redirect to login page
                exit(); // Important: Stop execution after redirect
            }
        } catch (Exception $e) {
            // Generic error in case of database failure
            $feedback = "An unexpected error occurred. Please try again.";
            // Optional: Log the specific error for debugging: error_log("Registration Error: " . $e->getMessage());
        }
    }
} // End of POST check

// --- 6. HTML Output (Display Form and Feedback) ---
echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<title>Register - Primary Oaks Surgery</title>';
// Link to the external stylesheet (assuming you'll add one later)
// echo '<link rel="stylesheet" href="../css/styles.css">'; // Add this line when you create styles.css
echo '<style>
    .feedback-success { color: green; font-weight: bold; padding: 10px; border: 1px solid green; background-color: #e6ffe6; }
    .feedback-error { color: red; padding: 10px; border: 1px solid red; background-color: #ffe6e6; }
    .rules li { color: #555; font-size: 0.9em; }
</style>';
echo '</head>';
echo '<body>';
echo '<h1>Create Your Account</h1>';
echo '<hr>';

// Display Success or Error Messages
if (isset($_SESSION['msg'])) {
    echo "<div class='feedback-success'>" . $_SESSION['msg'] . "</div>";
    unset($_SESSION['msg']); // Clear the message so it doesn't show again
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($feedback)) {
    echo "<div class='feedback-error'>";
    echo "❌ **Registration Failed!** Please correct the following issues:<br><br>";
    echo $feedback;
    echo "</div><br>";
}

// The 'action' attribute now submits back to THIS file (register.php)
echo '<form action="register.php" method="POST">';
// Full Name Field (Pre-fill logic is included, using the sanitized raw input for value)
echo '<label for="fullName">Full Name:</label><br>';
echo '<input type="text" id="fullName" name="fullName" value="' . htmlspecialchars($_POST['fullName'] ?? '', ENT_QUOTES, 'UTF-8') . '"><br>';
echo '<br>';
// Email Field (Pre-fill logic is included, using the sanitized raw input for value)
echo '<label for="email">Email Address:</label><br>';
echo '<input type="email" id="email" name="email" value="' . htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') . '"><br>';
echo '<br>';
// Password Field
echo '<label for="password">Password:</label><br>';
echo '<input type="password" id="password" name="password"><br>'; // Don't pre-fill password for security
echo '<br>';
// Confirm Password Field
echo '<label for="confirmPassword">Confirm Password:</label><br>';
echo '<input type="password" id="confirmPassword" name="confirmPassword"><br>'; // Don't pre-fill password for security
echo '<br>';
// Submit Button
echo '<button type="submit">Create Account</button>';
echo '</form>';

// Display Rules (Mirror your pass_check.php list)
echo '<h2>Password Requirements</h2>';
echo '<ul class="rules">';
echo '<li>Must be <strong>greater than 8</strong> characters.</li>';
echo '<li>Must contain at least <strong>one upper case</strong> character.</li>';
echo '<li>Must contain at least <strong>one lower case</strong> character.</li>';
echo '<li>Must contain at least <strong>one number</strong>.</li>';
echo '<li>Must contain at least <strong>one special character</strong> (non-alphanumeric).</li>';
echo '<li>The word <strong>“password”</strong> cannot be part of the password.</li>';
echo '<li>The <strong>first</strong> character cannot be a number or a special character.</li>';
echo '<li>The <strong>last</strong> character cannot be a special character.</li>';
echo '</ul>';
echo '<br>';
echo '<a href="index.php">Back to Home</a>';
echo '</body>';
echo '</html>';
?>