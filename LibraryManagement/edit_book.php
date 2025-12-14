<?php
// edit_book.php - Allows any logged-in user to edit a book

session_start();
require 'db.php'; 

$errors = [];
$success = '';

// 1. New Guardrail: Check if ANY user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Check if a book ID was passed in the URL
$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die("Invalid book ID.");
}

// 2. Fetch the book data (for initial form display)
try {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $book = $stmt->fetch();

    if (!$book) {
        die("Book not found.");
    }
    
    // Set variables for the form
    $title = $book['title'];
    $author = $book['author'];
    $price = $book['price'];

} catch (PDOException $e) {
    die("Database error while fetching book details.");
}

// 3. Handle POST Request (Form Submission)
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
            $stmt = $pdo->prepare("UPDATE books SET title = :title, author = :author, price = :price WHERE id = :id");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':author', $author);
            $stmt->bindValue(':price', $price, PDO::PARAM_STR); 
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            // Redirect with success message
            $_SESSION['system_message'] = "Book updated successfully!";
            header('Location: index.php');
            exit;

        } catch (PDOException $e) {
            $errors[] = "Database error: Could not update book.";
            error_log($e->getMessage()); 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Book</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <nav>
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
    </nav>
    <h1>Edit Book: <?= htmlspecialchars($title) ?></h1>
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
        <button type="submit">Update Book</button>
    </form>
</div>
</body>
</html>