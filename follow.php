<?php
session_start();
require_once 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
 
$user_id = $_SESSION['user_id'];
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['followed_id'])) {
    $followed_id = $_POST['followed_id'];
    if (isset($_POST['follow'])) {
        $stmt = $pdo->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $followed_id]);
    } elseif (isset($_POST['unfollow'])) {
        $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
        $stmt->execute([$user_id, $followed_id]);
    }
}
 
header('Location: index.php');
exit;
?>
