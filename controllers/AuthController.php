<?php
require_once '../config/config.php';

class AuthController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function register($email, $password, $fullName, $role = 'user')
    {
      
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return "Email already registered.";
        }

        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        
        $stmt = $this->pdo->prepare("INSERT INTO users (email, password, full_name, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$email, $hashedPassword, $fullName, $role]);

        return true;
    }


    public function login($email, $password)
    {
        $stmt = $this->pdo->prepare("SELECT id, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return "User not found.";
        }

        if (!password_verify($password, $user['password'])) {
            return "Incorrect password.";
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        return true;
    }

   
    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();
        return true;
    }

    
    public function getCurrentUser()
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $stmt = $this->pdo->prepare("SELECT id, email, full_name, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>