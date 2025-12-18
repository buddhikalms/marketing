<?php
$host = 'localhost';
$user = 'root';
$pass = 'buddhika@LMS2002';
$dbname = 'marketing_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
