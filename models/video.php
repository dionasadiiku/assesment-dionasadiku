<?php
require_once '../config/config.php';

class Video
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

  
    public function create($userId, $title, $description, $filename)
    {
        if (empty($userId) || empty($title) || empty($filename)) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO videos 
            (user_id, title, description, filename)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $title,
            $description,
            $filename
        ]);

        return $this->pdo->lastInsertId();
    }

  
    public function findById($videoId)
    {
        $stmt = $this->pdo->prepare("
            SELECT v.*, u.full_name
            FROM videos v
            LEFT JOIN users u ON v.user_id = u.id
            WHERE v.id = ?
            LIMIT 1
        ");
        $stmt->execute([$videoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

 
    public function getByUser($userId)
    {
        $stmt = $this->pdo->prepare("
            SELECT id, title, filename, created_at
            FROM videos
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getAll()
    {
        $stmt = $this->pdo->query("
            SELECT v.id, v.title, v.created_at, u.full_name
            FROM videos v
            LEFT JOIN users u ON v.user_id = u.id
            ORDER BY v.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

   
    public function delete($videoId, $userId = null, $isAdmin = false)
    {
       
        if ($isAdmin) {
            $stmt = $this->pdo->prepare("SELECT filename FROM videos WHERE id = ?");
            $stmt->execute([$videoId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT filename FROM videos WHERE id = ? AND user_id = ?");
            $stmt->execute([$videoId, $userId]);
        }

        $video = $stmt->fetch(PDO::FETCH_ASSOC);

  
        if (!$video) {
            return false;
        }

      
        if ($isAdmin) {
            $stmt = $this->pdo->prepare("DELETE FROM videos WHERE id = ?");
            $stmt->execute([$videoId]);
        } else {
            $stmt = $this->pdo->prepare("DELETE FROM videos WHERE id = ? AND user_id = ?");
            $stmt->execute([$videoId, $userId]);
        }

     
        $filePath = dirname(__DIR__) . '/uploads/' . $video['filename'];

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        return true;
    }
}
?>