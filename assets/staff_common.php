<?php

function staff_auditor($conn, $staffid, $code, $long_desc)
{
// *** Added: Use a try...catch block for professional error handling ***
try {
// *** Added: Input Validation ***
// Checks if the required user ID is numeric/valid and if code/desc are not empty.
if (!is_numeric($staffid) || $staffid <= 0 || empty($code) || empty($long_desc)) {
// Throw a general exception if data is invalid BEFORE hitting the database
throw new Exception("Audit data missing or invalid.");
}
// 1. SQL Statement and Preparation (Same as original)
$sql = "INSERT INTO staff_audits (user_id, date, code, long_desc) VALUES (?,?,?,?)";
$stmt = $conn->prepare($sql);
// 2. Date/Time Capture (Same as original)
$date = date('Y-m-d');
// 3. Binding Parameters (Same as original)
$stmt->bindParam(1, $staffid);
$stmt->bindParam(2, $date);
$stmt->bindParam(3, $code);
$stmt->bindParam(4, $long_desc);
// 4. Execution (Same as original)
$stmt->execute();
// 5. Connection Management (Fixed: Use $conn = null; instead of undefined close_connection())
$conn = null; // Close the connection properly
return true;
} catch (PDOException $e) {
// Handles database errors (e.g., table/column mismatch)
error_log("Audit Database Error: " . $e->getMessage());
throw new Exception("Audit Database Error: " . $e->getMessage());
} catch (Exception $e) {
// Handles validation errors (thrown above) or other runtime errors
error_log("Audit Runtime Error: " . $e->getMessage());
throw new Exception("Audit Runtime Error: " . $e->getMessage());
}
}

?>