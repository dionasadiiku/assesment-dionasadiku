<?php
require_once '../config/config.php';

class Annotation
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($videoId, $userId, $timestamp, $description = null, $drawing = null)
    {
       
        if (
            empty($videoId) ||
            empty($userId) ||
            $timestamp < 0 ||
            (empty($description) && empty($drawing))
        ) {
            return false;
        }

    
        $description = !empty($description) ? $description : null;

      
        $drawing = !empty($drawing) ? $drawing : null;

        $stmt = $this->pdo->prepare("
            INSERT INTO annotations 
            (video_id, user_id, timestamp_seconds, description, drawing)
            VALUES (:video_id, :user_id, :timestamp, :description, :drawing)
        ");

        $stmt->execute([
            ':video_id' => $videoId,
            ':user_id' => $userId,
            ':timestamp' => $timestamp,
            ':description' => $description,
            ':drawing' => $drawing
        ]);

        return $this->pdo->lastInsertId();
    }

    public function getByVideo($videoId)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                id,
                user_id,
                timestamp_seconds,
                description,
                drawing,
                created_at
            FROM annotations
            WHERE video_id = ?
            ORDER BY timestamp_seconds ASC
        ");

        $stmt->execute([$videoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getAll()
    {
        $sql = "
            SELECT 
                a.id,
                a.video_id,
                a.user_id,
                a.timestamp_seconds,
                a.description,
                a.drawing,
                a.created_at,
                v.title AS video_title,
                u.full_name
            FROM annotations a
            LEFT JOIN videos v ON a.video_id = v.id
            LEFT JOIN users u ON a.user_id = u.id
            ORDER BY a.id DESC
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

  
    public function delete($annotationId, $userId = null, $isAdmin = false)
    {
        if ($isAdmin) {
            $stmt = $this->pdo->prepare("DELETE FROM annotations WHERE id = ?");
            $stmt->execute([$annotationId]);
        } else {
            $stmt = $this->pdo->prepare("DELETE FROM annotations WHERE id = ? AND user_id = ?");
            $stmt->execute([$annotationId, $userId]);
        }

        return true;
    }

 
    public function findById($annotationId)
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM annotations
            WHERE id = ?
        ");

        $stmt->execute([$annotationId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>