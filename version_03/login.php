<?php
session_start();
require_once '../assets/dbconn.php';
require_once '../assets/common.php';

$feedback = "";
$loginSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ✅ LOG ATTEMPT ONLY ON FORM SUBMIT
    audit_write(0, 'LOGIN_ATTEMPT', 'Login attempt initiated from IP: ' . $_SERVER['REMOTE_ADDR']);

    $email = trim(filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL));
    $password = filter_input(INPUT_POST, "password", FILTER_UNSAFE_RAW);

    if (empty($email) || empty($password)) {
        $feedback = "Login failed. Please check your details and try again.";
        audit_write(0, 'LOGIN_FAILED', 'Login failed for email ' . $email . ' - Empty fields provided. IP: ' . $_SERVER['REMOTE_ADDR']);
    } else {
        try {
            $pdo = dbconnect_insert();
            $stmt = $pdo->prepare("SELECT user_id, full_name, password_hash FROM users WHERE email_address = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['full_name'];
                audit_write($user['user_id'], 'LOGIN_SUCCESS', 'User logged in successfully with email: ' . $email . '. IP: ' . $_SERVER['REMOTE_ADDR']);
                $loginSuccess = true;
            } else {
                $user_id_for_audit = ($user ? $user['user_id'] : 0);
                $reason = $user ? 'Incorrect password' : 'Email not found';
                audit_write($user_id_for_audit, 'LOGIN_FAILED', 'Login failed for email ' . $email . ' - ' . $reason . '. IP: ' . $_SERVER['REMOTE_ADDR']);
                $feedback = "Login failed. Please check your details and try again.";
            }
        } catch (Exception $e) {
            audit_write(0, 'LOGIN_FAILED', 'Login failed for email ' . $email . ' due to a database error. Error: ' . $e->getMessage() . '. IP: ' . $_SERVER['REMOTE_ADDR']);
            $feedback = "Login failed. Please try again.";
        }
    }
}

if (isset($_SESSION['user_id']) && !isset($_GET['logout'])) {
    $loginSuccess = true;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Primary Oaks Surgery</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<?php require_once '../assets/topbar.php'; ?>
<?php require_once '../assets/nav.php'; ?>
<?php require_once '../assets/content.php'; ?>

<?php if ($loginSuccess): ?>
    <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES, 'UTF-8') ?>!</h1>
    <p>You are now logged into the Primary Oaks Surgery portal.</p>
    <a href="book.php">Book an Appointment</a><br><br>
    <a href="logout.php">Logout</a>
<?php else: ?>
    <h1>Login to Your Account</h1>
    <hr>
    <?php if (!empty($feedback)): ?>
        <div class="feedback-error"><strong>❌ Error:</strong> <?= $feedback ?></div><br>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <label for="email">Email Address:</label><br>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required><br><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        <button type="submit">Login</button>
    </form>
<?php endif; ?>

</div>
</body>
</html>