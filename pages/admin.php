<?php
require_once '../config/config.php';
require_once '../middleware/AdminMiddleware.php';
require_once '../models/Video.php';
require_once '../models/User.php';
require_once '../models/Bookmark.php';
require_once '../models/Annotation.php';

AdminMiddleware::handle();

$videoModel      = new Video($pdo);
$userModel       = new User($pdo);
$bookmarkModel   = new Bookmark($pdo);
$annotationModel = new Annotation($pdo);

$videos      = $videoModel->getAll();
$users       = $userModel->getAll();
$bookmarks   = $bookmarkModel->getAll();
$annotations = $annotationModel->getAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="admin-container">

<h2>Admin Panel</h2>

<div class="admin-links">
<a href="../pages/dashboard.php">Go to Dashboard</a> 
<a href="../auth/logout.php">Logout</a>
</div>
<hr>

<div class="section">
<h3>All Users</h3>

<?php if (empty($users)): ?>
    <p>No users found.</p>
<?php else: ?>
    <ul>
        <?php foreach ($users as $user): ?>
            <li>
                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                (<?= htmlspecialchars($user['email']) ?>)
                - Role: <?= htmlspecialchars($user['role']) ?>
                <?php if (!empty($user['created_at'])): ?>
                    - Joined: <?= htmlspecialchars($user['created_at']) ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
</div>
<hr>

<div class="section">
<h3>All Videos</h3>

<?php if (empty($videos)): ?>
    <p>No videos found.</p>
<?php else: ?>
    <ul>
        <?php foreach ($videos as $video): ?>
            <li>
                <strong><?= htmlspecialchars($video['title']) ?></strong>
                - Uploaded by: <?= htmlspecialchars($video['full_name'] ?? 'Unknown') ?>
                - Visibility: <?= htmlspecialchars($video['visibility'] ?? 'public') ?>
                <?php if (!empty($video['created_at'])): ?>
                    - Created at: <?= htmlspecialchars($video['created_at']) ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
</div>
<hr>

<div class="section">
<h3>All Bookmarks</h3>

<?php if (empty($bookmarks)): ?>
    <p>No bookmarks found.</p>
<?php else: ?>
    <ul>
        <?php foreach ($bookmarks as $bookmark): ?>
            <li>
                Video: <?= htmlspecialchars($bookmark['video_title'] ?? 'Unknown') ?> |
                User: <?= htmlspecialchars($bookmark['full_name'] ?? 'Unknown') ?> |
                Time: <?= htmlspecialchars($bookmark['timestamp_seconds']) ?>s |
                Title: <?= htmlspecialchars($bookmark['title']) ?>
                <?php if (!empty($bookmark['created_at'])): ?>
                    - Created at: <?= htmlspecialchars($bookmark['created_at']) ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
</div>
<hr>

<div class="section">
<h3>All Annotations</h3>

<?php if (empty($annotations)): ?>
    <p>No annotations found.</p>
<?php else: ?>
    <ul>
        <?php foreach ($annotations as $annotation): ?>
            <li>
                Video: <?= htmlspecialchars($annotation['video_title'] ?? 'Unknown') ?> |
                User: <?= htmlspecialchars($annotation['full_name'] ?? 'Unknown') ?> |
                Time: <?= htmlspecialchars($annotation['timestamp_seconds']) ?>s |
                Description: <?= htmlspecialchars($annotation['description']) ?>
                <?php if (!empty($annotation['created_at'])): ?>
                    - Created at: <?= htmlspecialchars($annotation['created_at']) ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
</div>
</div>

</body>
</html>