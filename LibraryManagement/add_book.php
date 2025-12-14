<?php
// add_book.php - Allows any logged-in user to add a book

session_start();
require 'db.php'; 

$errors = [];
$success = '';
$title = $author = $price = '';

// 1. New Guardrail: Check if ANY user is logged in
if (!isset($_SESSION['username'])) {
    // If not logged in, redirect them to the login page
    header('Location: login.php');
    exit;
}

// 2. Handle POST Request (Form Submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $price = $_POST['price'] ?? 0;

    // Validation
    if (empty($title)) $errors[] = "Title is required.";
    if (empty($author)) $errors[] = "Author is required.";
    if (!is_numeric($price) || $price <= 0) $errors[] = "Price must be a positive number.";

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO books (title, author, price) VALUES (:title, :author, :price)");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':author', $author);
            $stmt->bindValue(':price', $price, PDO::PARAM_STR); 
            $stmt->execute();
            
            $_SESSION['system_message'] = "Book added successfully!";
            header('Location: index.php');
            exit;

        } catch (PDOException $e) {
            $errors[] = "Database error: Could not add book.";
            error_log("Add Book Error: " . $e->getMessage()); 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Book</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <nav>
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
    </nav>
    <h1>Add New Book</h1>
    
    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
    <?php endif; ?>
    <?php if ($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <form method="POST">
        <label>Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($title) ?>" required>
        <label>Author</label>
        <input type="text" name="author" value="<?= htmlspecialchars($author) ?>" required>
        <label>Price</label>
        <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($price) ?>" required>
        <button type="submit">Add Book</button>
    </form>
</div>
</body>
</html>