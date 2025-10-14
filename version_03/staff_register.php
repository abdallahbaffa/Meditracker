<?php
session_start();
require_once '../assets/dbconn.php';
require_once '../assets/common.php';

$feedback = "";
$password_raw = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ✅ LOG ATTEMPT ONLY ON FORM SUBMIT
    audit_write(0, 'REG_ATTEMPT', 'Registration attempt initiated from IP: ' . $_SERVER['REMOTE_ADDR']);

    $full_name_raw = filter_input(INPUT_POST, "fullName", FILTER_UNSAFE_RAW);
    $email_raw = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
    $password_raw = filter_input(INPUT_POST, "password", FILTER_UNSAFE_RAW);
    $confirmPassword_raw = filter_input(INPUT_POST, "confirmPassword", FILTER_UNSAFE_RAW);

    $full_name_raw = trim($full_name_raw);
    $email_raw = trim($email_raw);
    $password_raw = trim($password_raw);
    $confirmPassword_raw = trim($confirmPassword_raw);

    $full_name = htmlspecialchars($full_name_raw, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email_raw, ENT_QUOTES, 'UTF-8');

    if (empty($full_name) || empty($email) || empty($password_raw)) {
        $feedback .= "All fields are required.<br>";
    }
    if ($password_raw !== $confirmPassword_raw) {
        $feedback .= "Passwords do not match.<br>";
    }

    if (empty($feedback) && !empty($password_raw) && $password_raw === $confirmPassword_raw) {
        if (stripos($password_raw, 'password') !== false) {
            $feedback .= "Password cannot contain the word 'password'.<br>";
        }
        if (strlen($password_raw) < 9) {
            $feedback .= "Password must be greater than 8 characters.<br>";
        }
        if (!preg_match('/[A-Z]/', $password_raw)) {
            $feedback .= "Password must contain at least one uppercase letter.<br>";
        }
        if (!preg_match('/[a-z]/', $password_raw)) {
            $feedback .= "Password must contain at least one lowercase letter.<br>";
        }
        if (!preg_match('/[0-9]/', $password_raw)) {
            $feedback .= "Password must contain at least one number.<br>";
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password_raw)) {
            $feedback .= "Password must contain at least one special character.<br>";
        }
        if (isset($password_raw[0])) {
            if (is_numeric($password_raw[0])) {
                $feedback .= "First character cannot be a number.<br>";
            } elseif (!ctype_alnum($password_raw[0])) {
                $feedback .= "First character cannot be a special character.<br>";
            }
        }
        $len = strlen($password_raw);
        if ($len > 0 && !ctype_alnum($password_raw[$len - 1])) {
            $feedback .= "Last character cannot be a special character.<br>";
        }
    }

    if (empty($feedback)) {
        try {
            $pdo = dbconnect_insert();
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email_address = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                audit_write(0, 'REG_FAILED', 'Registration failed for email ' . $email . ' - Email already exists. IP: ' . $_SERVER['REMOTE_ADDR']);
                $feedback = "Registration failed. Please try again.";
            } else {
                $password_hash = password_hash($password_raw, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email_address, password_hash) VALUES (?, ?, ?)");
                $stmt->execute([$full_name, $email, $password_hash]);

                $new_user_id = $pdo->lastInsertId();
                audit_write($new_user_id, 'REG_SUCCESS', 'New user registered successfully with email: ' . $email . '. Full Name: ' . $full_name . '. IP: ' . $_SERVER['REMOTE_ADDR']);

                $_SESSION['msg'] = "✅ Registration successful! You can now log in.";
                header("Location: login.php");
                exit();
            }
        } catch (Exception $e) {
            audit_write(0, 'REG_FAILED', 'Registration failed due to a database error for email ' . $email . '. Error: ' . $e->getMessage() . '. IP: ' . $_SERVER['REMOTE_ADDR']);
            $feedback = "An unexpected error occurred. Please try again.";
        }
    } else {
        audit_write(0, 'REG_FAILED', 'Registration failed for email ' . $email . ' - Validation errors: ' . $feedback . '. IP: ' . $_SERVER['REMOTE_ADDR']);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Primary Oaks Surgery</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<?php require_once '../assets/topbar.php'; ?>
<?php require_once '../assets/nav.php'; ?>
<?php require_once '../assets/content.php'; ?>

<h1>Create Your Account</h1>
<hr>

<?php if (isset($_SESSION['msg'])): ?>
    <div class="feedback-success"><?= $_SESSION['msg'] ?></div>
    <?php unset($_SESSION['msg']); ?>
<?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($feedback)): ?>
    <div class="feedback-error">❌ **Registration Failed!** Please correct the following issues:<br><br><?= $feedback ?></div><br>
<?php endif; ?>

<form action="../version_02/register.php" method="POST">
    <label for="fullName">Full Name:</label><br>
    <input type="text" id="fullName" name="fullName" value="<?= htmlspecialchars($_POST['fullName'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><br><br>
    <label for="email">Email Address:</label><br>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><br><br>
    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password"><br><br>
    <label for="confirmPassword">Confirm Password:</label><br>
    <input type="password" id="confirmPassword" name="confirmPassword"><br><br>
    <button type="submit">Create Account</button>
</form>

<h2>Password Requirements</h2>
<ul class="rules">
    <li>Must be <strong>greater than 8</strong> characters.</li>
    <li>Must contain at least <strong>one upper case</strong> character.</li>
    <li>Must contain at least <strong>one lower case</strong> character.</li>
    <li>Must contain at least <strong>one number</strong>.</li>
    <li>Must contain at least <strong>one special character</strong>.</li>
    <li>The word <strong>“password”</strong> cannot be part of the password.</li>
    <li>The <strong>first</strong> character cannot be a number or special character.</li>
    <li>The <strong>last</strong> character cannot be a special character.</li>
</ul><br>

</div>
</body>
</html>