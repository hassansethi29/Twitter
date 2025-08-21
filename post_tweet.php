<?php
session_start();
require_once 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
 
$user_id = $_SESSION['user_id'];
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['post_tweet'])) {
        $content = $_POST['content'];
        $stmt = $pdo->prepare("INSERT INTO tweets (user_id, content) VALUES (?, ?)");
        $stmt->execute([$user_id, $content]);
    } elseif (isset($_POST['edit_tweet']) && isset($_POST['tweet_id'])) {
        $tweet_id = $_POST['tweet_id'];
        $content = $_POST['content'];
        $stmt = $pdo->prepare("UPDATE tweets SET content = ? WHERE tweet_id = ? AND user_id = ?");
        $stmt->execute([$content, $tweet_id, $user_id]);
    } elseif (isset($_POST['delete_tweet']) && isset($_POST['tweet_id'])) {
        $tweet_id = $_POST['tweet_id'];
        $stmt = $pdo->prepare("DELETE FROM tweets WHERE tweet_id = ? AND user_id = ?");
        $stmt->execute([$tweet_id, $user_id]);
    }
}
 
header('Location: index.php');
exit;
?>
