<?php
require '../config/config.php'; 

$message = "";

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$maxAttempts = 5;
$lockoutSeconds = 300; 

if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_last_attempt'] = null;
}

if ($_SESSION['login_attempts'] >= $maxAttempts) {
    $since = time() - ($_SESSION['login_last_attempt'] ?? 0);
    if ($since < $lockoutSeconds) {
        $remaining = $lockoutSeconds - $since;
        $message = "Too many attempts. Try again in {$remaining} seconds.";
    } else {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_last_attempt'] = null;
    }
}

if (empty($message) && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $posted_csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $posted_csrf)) {
        $message = "Invalid request.";
    } else {
        $email = trim(strtolower($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $message = "Email and password are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please provide a valid email address.";
        } else {
            $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

          
            if ($user && password_verify($password, $user['password'])) {
                
                $_SESSION['login_attempts'] = 0;
                $_SESSION['login_last_attempt'] = null;

                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                header("Location: ../index.php");
                exit();
            } else {
                
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                $_SESSION['login_last_attempt'] = time();
                $message = "Invalid email or password.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="login-container">
    <h2>Login</h2>

    <?php if (!empty($message)): ?>
        <p style="color:red;">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <input type="email" name="email" placeholder="Email" required>
        <br><br>
        <input type="password" name="password" placeholder="Password" required>
        <br><br>
        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
</body>
</html>