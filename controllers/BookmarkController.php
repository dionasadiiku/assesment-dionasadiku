<?php

class BookmarkController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($videoId, $userId, $timestamp, $title)
    {
        if ($timestamp < 0) {
            return "Invalid timestamp.";
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO bookmarks (video_id, user_id, timestamp_seconds, title)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([$videoId, $userId, $timestamp, $title]);

        return true;
    }


    public function getByVideo($videoId)
    {
        $stmt = $this->pdo->prepare("
            SELECT id, timestamp_seconds, title, created_at
            FROM bookmarks
            WHERE video_id = ?
            ORDER BY timestamp_seconds ASC
        ");

        $stmt->execute([$videoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function delete($bookmarkId, $userId, $role)
    {
        if ($role !== 'admin') {
            $stmt = $this->pdo->prepare("
                DELETE FROM bookmarks
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$bookmarkId, $userId]);
        } else {
            $stmt = $this->pdo->prepare("
                DELETE FROM bookmarks
                WHERE id = ?
            ");
            $stmt->execute([$bookmarkId]);
        }

        return true;
    }
}