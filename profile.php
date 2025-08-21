<?php
session_start();
require_once 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
 
// Fetch user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
 
// Fetch user's tweets
$stmt = $pdo->prepare("SELECT * FROM tweets WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$tweets = $stmt->fetchAll();
 
// Fetch follower and following counts
$followers = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE followed_id = ?");
$followers->execute([$user_id]);
$follower_count = $followers->fetchColumn();
 
$following = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ?");
$following->execute([$user_id]);
$following_count = $following->fetchColumn(0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo htmlspecialchars($user['username']); ?></title>
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
        .profile-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .profile-header img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        .profile-header button {
            background: #1da1f2;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
        }
        .profile-header button:hover {
            background: #0d8bdb;
        }
        .tweet {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
            .profile-header img {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="#" onclick="redirectToHome()">Home</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">
        <div class="profile-header">
            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile">
            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
            <p><?php echo htmlspecialchars($user['bio'] ?? 'No bio'); ?></p>
            <p>Followers: <?php echo $follower_count; ?> | Following: <?php echo $following_count; ?></p>
            <button onclick="editProfile()">Edit Profile</button>
        </div>
        <h3>Your Tweets</h3>
        <?php foreach ($tweets as $tweet): ?>
            <div class="tweet">
                <p><?php echo htmlspecialchars($tweet['content']); ?></p>
                <p><?php echo $tweet['created_at']; ?></p>
                <button onclick="editTweet(<?php echo $tweet['tweet_id']; ?>, '<?php echo htmlspecialchars($tweet['content']); ?>')">Edit</button>
                <button onclick="deleteTweet(<?php echo $tweet['tweet_id']; ?>)">Delete</button>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        function redirectToHome() {
            window.location.href = 'index.php';
        }
        function editProfile() {
            let bio = prompt('Update your bio:');
            if (bio) {
                fetch('profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'bio=' + encodeURIComponent(bio) + '&edit_profile=true'
                }).then(() => location.reload());
            }
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
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_profile'])) {
            $bio = $_POST['bio'];
            $stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE user_id = ?");
            $stmt->execute([$bio, $user_id]);
            echo "location.reload();";
        }
        ?>
    </script>
</body>
</html>
