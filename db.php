<?php
$host = 'localhost';
$dbname = 'dbgzf469vgxqmy';
$username = 'ui7tygtg6tjn5';
$password = 'wajnehqi0o0m';
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
