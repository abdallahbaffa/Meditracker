<?php
// Start a session to potentially store messages (optional, but good practice)
session_start();
// Include your database connection function
require_once '../assets/dbconn.php'; // Adjusted path to match your assets folder
// --- 2. Initialize variables and Check for Submission ---
$feedback = "";
$password_raw = ""; // To hold the submitted password for re-display/checking
// Check if the form has been submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- 3. Get and Sanitize Input ---
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
    // --- 4. Initial Validation (Empty checks, password match) ---
    if (empty($full_name) || empty($email) || empty($password_raw)) {
        $feedback .= "All fields are required.<br>";
    }
    // Check if passwords match
    if ($password_raw !== $confirmPassword_raw) {
        $feedback .= "Passwords do not match.<br>";
    }
    // Continue with password strength checks only if the fields aren't empty and passwords match
    if (empty($feedback) && !empty($password_raw) && $password_raw === $confirmPassword_raw) {
        // --- 5. Password Strength Rules (Mirror your pass_check.php logic) ---
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
    // --- 6. Database Interaction (If all checks pass) ---
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
// --- 8. HTML Output (Display Form and Feedback) ---
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Primary Oaks Surgery</title>
    <!-- Link to the external stylesheet (CRUCIAL: Move this here) -->
    <link rel="stylesheet" href="../css/styles.css"> <!-- Adjust path relative to register.php location -->
</head>
<body>
<!-- Include the topbar structure -->
<?php require_once '../assets/topbar.php'; ?>
<!-- Include the navigation structure -->
<?php require_once '../assets/nav.php'; ?>
<!-- Include the content wrapper structure -->
<?php require_once '../assets/content.php'; ?>

<h1>Create Your Account</h1>
<hr>
<!-- Display Success or Error Messages -->
<?php
// Check for success message in session
if (isset($_SESSION['msg'])) {
    echo "<div class='feedback-success'>" . $_SESSION['msg'] . "</div>";
    unset($_SESSION['msg']); // Clear the message after displaying it
}
// Display error messages from validation or database errors
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($feedback)) {
    echo "<div class='feedback-error'>";
    echo "❌ **Registration Failed!** Please correct the following issues:<br><br>";
    echo $feedback;
    echo "</div><br>";
}
?>

<!-- The 'action' attribute now submits back to THIS file (register.php) -->
<form action="register.php" method="POST">
    <!-- Full Name Field (Pre-fill logic is included, using the sanitized raw input for value) -->
    <label for="fullName">Full Name:</label><br>
    <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($_POST['fullName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br>
    <br>
    <!-- Email Field (Pre-fill logic is included, using the sanitized raw input for value) -->
    <label for="email">Email Address:</label><br>
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><br>
    <br>
    <!-- Password Field -->
    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password"><br> <!-- Don't pre-fill password for security -->
    <br>
    <!-- Confirm Password Field -->
    <label for="confirmPassword">Confirm Password:</label><br>
    <input type="password" id="confirmPassword" name="confirmPassword"><br> <!-- Don't pre-fill password for security -->
    <br>
    <!-- Submit Button -->
    <button type="submit">Create Account</button>
</form>

<!-- Display Rules (Mirror your pass_check.php list) -->
<h2>Password Requirements</h2>
<ul class="rules">
    <li>Must be <strong>greater than 8</strong> characters.</li>
    <li>Must contain at least <strong>one upper case</strong> character.</li>
    <li>Must contain at least <strong>one lower case</strong> character.</li>
    <li>Must contain at least <strong>one number</strong>.</li>
    <li>Must contain at least <strong>one special character</strong> (non-alphanumeric).</li>
    <li>The word <strong>“password”</strong> cannot be part of the password.</li>
    <li>The <strong>first</strong> character cannot be a number or a special character.</li>
    <li>The <strong>last</strong> character cannot be a special character.</li>
</ul>
<br>
<!-- Close the content wrapper div (from content.php) -->
</div>
</body>
</html>