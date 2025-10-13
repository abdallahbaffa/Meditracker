<?php

function dbconnect_insert(){
    // The variables are defined locally inside the function
    $servername = "localhost";
    $dbusername = "root"; #SHOULD NOT USE ROOT TO ACCESS A DATABASE
    $dbpassword = "";
    $dbname = "primary_oaks_db"; // <-- Make sure this DB exists

    /*THESE THINGS SHOULD NOT BE STORED IN PLAIN TEXT!! VERY INSECURE!!*/

    try {
        // The variables are used locally and correctly here
        $conn = new PDO("mysql:host=$servername;port=3306;dbname=$dbname", $dbusername, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Return the connection object
        return $conn;
    } catch(PDOException $e) {
        error_log("Database error in super_checker: " . $e->getMessage());
        throw $e;
    }
}

// !!! IMPORTANT: You must call the function to get the connection
// Example of calling the function later in your script:
// $connection = dbconnect_insert();
?>