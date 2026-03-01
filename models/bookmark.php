<?php
require_once '../config/config.php';

class Bookmark
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    
    public function create($videoId, $userId, $timestamp, $title)
    {
        if (empty($videoId) || empty($userId) || empty($timestamp) || empty($title)) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO bookmarks (video_id, user_id, timestamp_seconds, title)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $videoId,
            $userId,
            $timestamp,
            $title
        ]);

        return $this->pdo->lastInsertId();
    }


    public function getByVideo($videoId)
    {
        $stmt = $this->pdo->prepare("
            SELECT id, user_id, timestamp_seconds, title, created_at
            FROM bookmarks
            WHERE video_id = ?
            ORDER BY timestamp_seconds ASC
        ");

        $stmt->execute([$videoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getAll()
    {
        $sql = "SELECT b.*, v.title AS video_title, u.full_name
                FROM bookmarks b
                LEFT JOIN videos v ON b.video_id = v.id
                LEFT JOIN users u ON b.user_id = u.id
                ORDER BY b.id DESC";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function delete($bookmarkId, $userId = null, $isAdmin = false)
    {
        if ($isAdmin) {
            $stmt = $this->pdo->prepare("DELETE FROM bookmarks WHERE id = ?");
            $stmt->execute([$bookmarkId]);
        } else {
            $stmt = $this->pdo->prepare("DELETE FROM bookmarks WHERE id = ? AND user_id = ?");
            $stmt->execute([$bookmarkId, $userId]);
        }

        return true;
    }
}
?>