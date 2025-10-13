<?php

// Version 3: Separated CSS into an external file.
// This page provides the basic structure and navigation links.

// --- START OF HTML OUTPUT ---

echo '<!DOCTYPE html>';
echo '<html>';

echo '<head>';
echo '<title>Primary Oaks Surgery</title>';
// Link to the external stylesheet
echo '<link rel="stylesheet" href="/css/styles.css">';
echo '</head>';

echo '<body>';

// The dark header bar for the title - Using the existing .topbar class
echo '<div class="topbar">';
echo '<h1>Primary Oaks Surgery</h1>'; // This will be white due to .topbar h1
echo '</div>';

// The purple navigation bar - Using existing nav ul/li structure
echo '<nav>';
echo '<ul>';
// The links from the first image
echo '<li><a href="index.php">Home</a></li>';
echo '<li><a href="register.php">Register</a></li>';
echo '<li><a href="login.php">Login</a></li>';
echo '</ul>';
echo '</nav>';

// Container for the content from the second image - Using a new class for styling
echo '<div class="portal-content">';

echo '<p>Welcome to the online portal.</p>';

// The links from the second image, wrapped in a div for layout/color
echo '<div class="portal-links">';
echo '<a href="register.php">REGISTER</a>';
echo '<br>';
echo '<a href="login.php">LOGIN</a>';
echo '</div>';

echo '</div>'; // close .portal-content

echo '</body>';

echo '</html>';

?>