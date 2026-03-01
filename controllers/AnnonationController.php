<?php
require_once '../config/config.php';

class AnnotationController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function createAnnotation($videoId, $userId, $timestamp, $description, $shapeType = null, $shapeData = null)
    {
        $sql = "INSERT INTO annotations (video_id, user_id, timestamp_seconds, description, shape_type, shape_data)
                VALUES (:video_id, :user_id, :timestamp, :description, :shape_type, :shape_data)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':video_id' => $videoId,
            ':user_id' => $userId,
            ':timestamp' => $timestamp,
            ':description' => $description,
            ':shape_type' => $shapeType,
            ':shape_data' => $shapeData
        ]);

        return $this->pdo->lastInsertId();
    }

  
    public function getAnnotationsByVideo($videoId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM annotations WHERE video_id = :video_id ORDER BY timestamp_seconds ASC");
        $stmt->execute([':video_id' => $videoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function deleteAnnotation($annotationId, $userId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM annotations WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([
            ':id' => $annotationId,
            ':user_id' => $userId
        ]);
    }
}
?>