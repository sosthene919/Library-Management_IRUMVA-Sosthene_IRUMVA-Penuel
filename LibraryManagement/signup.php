<?php

session_start();
require 'db.php'; 

if (isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($username) || empty($password) || empty($password_confirm)) {
        $errors[] = "All fields are required.";
    }

    if ($password !== $password_confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->execute();
            
            $_SESSION['signup_success'] = "Account created successfully! Please log in.";
            header('Location: login.php');
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                 $errors[] = "The username '{$username}' is already taken.";
            } else {
                 $errors[] = "Database error: Could not create account.";
                 error_log("Signup error: " . $e->getMessage()); 
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <nav>
        <a href="index.php">Home</a>
        <a href="login.php">Login</a>
    </nav>
    <h1>Create New Account</h1>
    
    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
    <?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required>
        
        <label>Password</label>
        <input type="password" name="password" required>
        
        <label>Confirm Password</label>
        <input type="password" name="password_confirm" required>
        
        <button type="submit">Sign Up</button>
    </form>
</div>
</body>
</html>