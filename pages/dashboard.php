<?php
require_once '../config/config.php';
require_once '../middleware/AuthMiddleware.php';
require_once '../models/Video.php';

AuthMiddleware::handle();

$videoModel = new Video($pdo);
$userId = $_SESSION['user_id'];


if (isset($_GET['delete'])) {

    $videoId = (int) $_GET['delete'];

    $videoModel->delete($videoId, $userId);

    header("Location: dashboard.php");
    exit();
}

$videos = $videoModel->getByUser($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>

<div class="dashboard-container">
    <h2>Welcome to your Dashboard</h2>

    <div class="links">
        <a href="../videos/upload.php" class="action-box">Upload New Video</a>
        <a href="../auth/logout.php" class="action-box">Logout</a>
    </div>

    <hr>

    <h3>Your Videos</h3>

    <?php if (empty($videos)): ?>
        <p>You have not uploaded any videos yet.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($videos as $video): ?>
                <li>
                    <strong><?= htmlspecialchars($video['title']) ?></strong>

                    <div style="margin-top:8px;">
                        <a href="../videos/watch.php?id=<?= $video['id'] ?>">Watch</a>

                        <a href="?delete=<?= $video['id'] ?>"
                           class="delete-btn"
                           onclick="return confirm('Are you sure you want to delete this video?')">
                           🗑 Delete
                        </a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

</body>
</html>