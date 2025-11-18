<?php

$host = "localhost";
$user = "root";
$pass = "Root@12345!";
$dbname = "todo_list";

try {
    $conn = new PDO("mysql:host=$host; dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo ("Connected!");
} catch (PDOException $e) {
    echo "Connection Failed: " . $e->getMessage();
}