<?php
require_once '../config/config.php';

class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    
    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare("
            SELECT id, email, password, full_name, role, created_at
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

   
    public function findById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT id, email, full_name, role, created_at
            FROM users
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

   
    public function create($email, $hashedPassword, $fullName, $role = 'user')
    {
        if (empty($email) || empty($hashedPassword)) {
            return false;
        }

      
        $check = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            return "Email already exists.";
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO users (email, password, full_name, role)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$email, $hashedPassword, $fullName, $role]);
        return $this->pdo->lastInsertId();
    }

   
    public function getAll()
    {
        $stmt = $this->pdo->query("
            SELECT id, email, full_name, role, created_at
            FROM users
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

 
    public function delete($id)
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM users
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return true;
    }
}
?>