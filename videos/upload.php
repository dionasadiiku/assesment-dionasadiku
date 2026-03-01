<?php
require_once '../config/config.php';
require_once '../middleware/AuthMiddleware.php';
require_once '../models/video.php';

AuthMiddleware::handle();

$videoModel = new Video($pdo);
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_FILES['video']) || $_FILES['video']['error'] !== 0) {
        $error = "Video upload failed.";
    } else {

        $allowedExtensions = ['mp4', 'webm', 'ogg'];
        $maxSize = 50 * 1024 * 1024; // 50MB

        $extension = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            $error = "Invalid video format. Allowed: mp4, webm, ogg.";
        } elseif ($_FILES['video']['size'] > $maxSize) {
            $error = "Video too large (max 50MB).";
        } else {

            
            $uploadDir = dirname(__DIR__) . '/uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newFilename = uniqid('video_', true) . '.' . $extension;
            $destination = $uploadDir . $newFilename;

            if (move_uploaded_file($_FILES['video']['tmp_name'], $destination)) {

               
                $videoModel->create(
                    $_SESSION['user_id'],
                    $_POST['title'],
                    $_POST['description'],
                    $newFilename
                );

                header("Location: ../pages/dashboard.php");
                exit();
            } else {
                $error = "Failed to save file.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Video</title>
    <link rel="stylesheet" href="../css/upload.css">
</head>
<body>

<div class="upload-box">

<h2>🎬 Upload Video</h2>

<?php if ($error): ?>
    <p class="error-message"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Video title" required>
    <textarea name="description" placeholder="Description"></textarea>

    <div class="file-upload">
        <label for="video" class="file-label">📁 Choose Video File</label>
        <input type="file" id="video" name="video" accept="video/*" required>
        <span class="file-name">No file selected</span>
    </div>

    <button type="submit">Upload</button>
</form>

<a href="../pages/dashboard.php" class="back-link">← Back to Dashboard</a>

</div>

<script>
const fileInput = document.getElementById("video");
const fileName = document.querySelector(".file-name");

fileInput.addEventListener("change", function() {
    fileName.textContent = this.files.length > 0 ? this.files[0].name : "No file selected";
});
</script>
</body>
</html>