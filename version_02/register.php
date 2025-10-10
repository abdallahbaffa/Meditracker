<?php

// Start a session to potentially store messages (optional, but good practice)
session_start();

// --- 1. Initialize variables and Check for Submission ---
$feedback = "";
$password_raw = ""; // To hold the submitted password for re-display/checking

// Check if the form has been submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 2. Get and Sanitize Input ---

    // Get and sanitize the raw password input
    $password_raw = filter_input(INPUT_POST, "password", FILTER_UNSAFE_RAW);
    $confirmPassword_raw = filter_input(INPUT_POST, "confirmPassword", FILTER_UNSAFE_RAW);
    $password_raw = trim($password_raw);

    // Escape special HTML chars for safe output and checking
    $password = htmlspecialchars($password_raw, ENT_QUOTES, 'UTF-8');
    $confirmPassword = htmlspecialchars($confirmPassword_raw, ENT_QUOTES, 'UTF-8');

    // --- 3. Initial Validation and Password Check Setup ---

    // Check if passwords match
    if ($password_raw !== $confirmPassword_raw) {
        $feedback .= "Passwords do not match.<br>";
    }

    // Continue with password strength checks only if the field isn't empty and passwords match
    if (empty($password_raw)) {
        $feedback .= "Password field cannot be empty.<br>";
    } else {

        // --- 4. Password Strength Rules ---

        // Rule 1: Check if the password contains the word "password" (case-insensitive)
        if (stripos($password, 'password') !== false) {
            $feedback .= "Password cannot contain the word 'password'.<br>";
        }

        // Rule 2: Check the length (greater than 8 characters)
        if (strlen($password) < 9) {
            $feedback .= "Password must be greater than 8 characters.<br>";
        }

        // Rule 3: Check for uppercase
        if (!preg_match('/[A-Z]/', $password)) {
            $feedback .= "Password must contain at least one uppercase letter.<br>";
        }

        // Rule 4: Check for lowercase
        if (!preg_match('/[a-z]/', $password)) {
            $feedback .= "Password must contain at least one lowercase letter.<br>";
        }

        // Rule 5: Check for a number
        if (!preg_match('/[0-9]/', $password)) {
            $feedback .= "Password must contain at least one number.<br>";
        }

        // Rule 6: Check for a special character
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $feedback .= "Password must contain at least one special character.<br>";
        }

        // Rule 7 & 8: Check the first character (must not be a number or special char)
        if (isset($password[0])) {
            if (is_numeric($password[0])) {
                $feedback .= "First character cannot be a number.<br>";
            } elseif (!ctype_alnum($password[0])) { // Rule 8
                $feedback .= "First character cannot be a special character.<br>";
            }
        }

        // Rule 9: Check the last character (must not be a special character)
        $len = strlen($password);
        if ($len > 0 && !ctype_alnum($password[$len - 1])) {
            $feedback .= "Last character cannot be a special character.<br>";
        }
    }

    // --- 5. Final Result Handling ---

    if (empty($feedback)) {
        // SUCCESS!
        $success_message = "✅ **Registration Successful!** Your password is strong.";
        // In a real application, you would hash and store the data here.
    }
}

// --- 6. HTML Output (Display Form and Feedback) ---

echo '<!DOCTYPE html>';
echo '<html>';

echo '<head>';
echo '<title>Register - Primary Oaks Surgery</title>';
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
if (!empty($success_message)) {
    echo "<div class='feedback-success'>$success_message</div>";
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($feedback)) {
    echo "<div class='feedback-error'>";
    echo "❌ **Registration Failed!** Please correct the following issues:<br><br>";
    echo $feedback;
    echo "</div><br>";
}

// The 'action' attribute now submits back to THIS file (register.php)
echo '<form action="register.php" method="POST">';

// Full Name Field (Pre-fill logic is omitted for simplicity, but recommended for production)
echo '<label for="fullName">Full Name:</label><br>';
echo '<input type="text" id="fullName" name="fullName" value="' . htmlspecialchars($_POST['fullName'] ?? '', ENT_QUOTES, 'UTF-8') . '"><br>';
echo '<br>';

// Email Field
echo '<label for="email">Email Address:</label><br>';
echo '<input type="email" id="email" name="email" value="' . htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') . '"><br>';
echo '<br>';

// Password Field
echo '<label for="password">Password:</label><br>';
echo '<input type="password" id="password" name="password"><br>';
echo '<br>';

// Confirm Password Field
echo '<label for="confirmPassword">Confirm Password:</label><br>';
echo '<input type="password" id="confirmPassword" name="confirmPassword"><br>';
echo '<br>';

// Submit Button
echo '<button type="submit">Create Account</button>';

echo '</form>';

// Display Rules
echo '<h2>Password Requirements</h2>';
echo '<ul class="rules">';
echo '<li>Must be **greater than 8** characters.</li>';
echo '<li>Must contain at least **one upper case** character.</li>';
echo '<li>Must contain at least **one lower case** character.</li>';
echo '<li>Must contain at least **one number**.</li>';
echo '<li>Must contain at least **one special character** (non-alphanumeric).</li>';
echo '<li>The word **“password”** cannot be part of the password.</li>';
echo '<li>The **first** character cannot be a number or a special character.</li>';
echo '<li>The **last** character cannot be a special character.</li>';
echo '</ul>';

echo '<br>';
echo '<a href="index.php">Back to Home</a>';

echo '</body>';
echo '</html>';

?>