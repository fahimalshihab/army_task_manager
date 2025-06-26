<?php
$host = 'localhost';
$dbname = 'army_task_manager';
$username = 'root';
$password = '2024'; // Use your MySQL password here

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
?>
