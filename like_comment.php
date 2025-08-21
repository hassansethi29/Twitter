<?php
session_start();
require_once 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
 
$user_id = $_SESSION['user_id'];
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['like_tweet']) && isset($_POST['tweet_id'])) {
        $tweet_id = $_POST['tweet_id'];
        $stmt = $pdo->prepare("INSERT INTO likes (user_id, tweet_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $tweet_id]);
    } elseif (isset($_POST['post_comment']) && isset($_POST['tweet_id']) && isset($_POST['comment'])) {
        $tweet_id = $_POST['tweet_id'];
        $comment = $_POST['comment'];
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, tweet_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $tweet_id, $comment]);
    }
}
 
header('Location: index.php');
exit;
?>
