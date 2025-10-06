<?php
// Make sure you have session_start() at the top of this file if it's not already there.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function user_message() {
    // Check if a message exists in the session
    if (isset($_SESSION["msg"])) {
        // Store the message in a variable
        $message = $_SESSION["msg"];

        // IMPORTANT: Clear the message from the session so it only shows once
        unset($_SESSION["msg"]);

        // Return the message wrapped in some HTML for styling
        return "<div class='message'>{$message}</div>";
    }
    // If no message, return nothing
    return "";
}

// Add any other common functions or database connections here, like dbconnect_insert()
?>