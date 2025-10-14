<?php
session_start();
$user_id_to_log = $_SESSION['user_id'] ?? 0;
require_once '../assets/common.php';

if ($user_id_to_log > 0) {
    audit_write($user_id_to_log, 'LOGOUT', 'User logged out successfully. IP: ' . $_SERVER['REMOTE_ADDR']);
} else {
    audit_write(0, 'LOGOUT_FAILED', 'Logout attempt made without active session. IP: ' . $_SERVER['REMOTE_ADDR']);
}

$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header("Location: index.php");
exit();
?>