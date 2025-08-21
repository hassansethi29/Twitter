<?php
session_start();
require_once 'db.php';
 
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
 
// Fetch tweets from followed users
$stmt = $pdo->prepare("
    SELECT t.*, u.username, u.profile_picture 
    FROM tweets t 
    JOIN users u ON t.user_id = u.user_id 
    WHERE t.user_id IN (
        SELECT followed_id FROM follows WHERE follower_id = ?
    ) OR t.user_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$user_id, $user_id]);
$tweets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twitter Clone - Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
        }
        .tweet-box {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .tweet-box textarea {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            font-size: 16px;
            resize: none;
        }
        .tweet-box button {
            background: #1da1f2;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 16px;
        }
        .tweet-box button:hover {
            background: #0d8bdb;
        }
        .tweet {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .tweet img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .tweet-header {
            display: flex;
            align-items: center;
        }
        .tweet-actions button {
            background: none;
            border: none;
            cursor: pointer;
            margin-right: 10px;
            font-size: 14px;
            color: #555;
        }
        .tweet-actions button:hover {
            color: #1da1f2;
        }
        .nav {
            background: #1da1f2;
            padding: 10px;
            position: fixed;
            width: 100%;
            top: 0;
            color: white;
            display: flex;
            justify-content: space-between;
        }
        .nav a {
            color: white;
            text-decoration: none;
            padding: 0 15px;
        }
        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }
            .tweet-box, .tweet {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="index.php">Home</a>
        <a href="#" onclick="redirectToProfile()">Profile</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
        <div class="tweet-box">
            <form action="post_tweet.php" method="POST">
                <textarea name="content" rows="4" placeholder="What's happening?" required></textarea>
                <button type="submit" name="post_tweet">Tweet</button>
            </form>
        </div>
        <?php foreach ($tweets as $tweet): ?>
            <div class="tweet">
                <div class="tweet-header">
                    <img src="<?php echo htmlspecialchars($tweet['profile_picture']); ?>" alt="Profile">
                    <div>
                        <strong><?php echo htmlspecialchars($tweet['username']); ?></strong>
                        <span><?php echo $tweet['(created_at']; ?></span>
                    </div>
                </div>
                <p><?php echo htmlspecialchars($tweet['content']); ?></p>
                <div class="tweet-actions">
                    <button onclick="likeTweet(<?php echo $tweet['tweet_id']; ?>)">Like</button>
                    <button onclick="showCommentBox(<?php echo $tweet['tweet_id']; ?>)">Comment</button>
                    <?php if ($tweet['user_id'] == $user_id): ?>
                        <button onclick="editTweet(<?php echo $tweet['tweet_id']; ?>, '<?php echo htmlspecialchars($tweet['content']); ?>')">Edit</button>
                        <button onclick="deleteTweet(<?php echo $tweet['tweet_id']; ?>)">Delete</button>
                    <?php endif; ?>
                </div>
                <div id="comment-box-<?php echo $tweet['tweet_id']; ?>" style="display:none;">
                    <form action="like_comment.php" method="POST">
                        <input type="hidden" name="tweet_id" value="<?php echo $tweet['tweet_id']; ?>">
                        <textarea name="comment" rows="2" placeholder="Add a comment" required></textarea>
                        <button type="submit" name="post_comment">Comment</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        function redirectToProfile() {
            window.location.href = 'profile.php';
        }
        function likeTweet(tweetId) {
            fetch('like_comment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'tweet_id=' + tweetId + '&like_tweet=true'
            }).then(() => location.reload());
        }
        function showCommentBox(tweetId) {
            document.getElementById('comment-box-' + tweetId).style.display = 'block';
        }
        function editTweet(tweetId, content) {
            let newContent = prompt('Edit your tweet:', content);
            if (newContent) {
                fetch('post_tweet.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'tweet_id=' + tweetId + '&content=' + encodeURIComponent(newContent) + '&edit_tweet=true'
                }).then(() => location.reload());
            }
        }
        function deleteTweet(tweetId) {
            if (confirm('Are you sure you want to delete this tweet?')) {
                fetch('post_tweet.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'tweet_id=' + tweetId + '&delete_tweet=true'
                }).then(() => location.reload());
            }
        }
    </script>
</body>
</html>
