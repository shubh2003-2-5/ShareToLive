<?php
$servername = "sql209.infinityfree.com";  // from InfinityFree
$username = "if0_38734779";
$password = "Aashrith2004";
$dbname = "if0_38734779_sharetolive";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
