<?php
// index.php - Main page to display and manage books for ALL logged-in users

session_start();
require 'db.php'; 

$books = [];
$error_display = ''; 

try {
    // Fetch book ID for Edit/Delete links
    $stmt = $pdo->query("SELECT id, title, author, price FROM books ORDER BY title ASC");
    $books = $stmt->fetchAll();

} catch (\PDOException $e) {
    $error_display = "Could not load books.";
    error_log("Book loading error: " . $e->getMessage()); 
}

$system_message = $_SESSION['system_message'] ?? '';
unset($_SESSION['system_message']);

// Check if ANY user is currently logged in
$is_logged_in = isset($_SESSION['username']);
$username_display = $is_logged_in ? htmlspecialchars($_SESSION['username']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>The Book Store</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
<div class="container">
    <nav>
        <a href="index.php">Home</a>
        
        <?php if ($is_logged_in): ?>
            <a href="add_book.php">Add Book</a>
            <a href="logout.php">Logout (<?= $username_display ?>)</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="signup.php">Sign Up</a>
        <?php endif; ?>
    </nav>
    
    <h1>Welcome to the Book Store</h1>
    
    <?php if ($system_message): ?>
        <div class="success"><?= htmlspecialchars($system_message) ?></div>
    <?php endif; ?>

    <hr>
    <h2>Available Books</h2>
    
    <?php if ($error_display): ?>
        <div class="error"><?= htmlspecialchars($error_display) ?></div>
    <?php elseif (empty($books)): ?>
        <p>No books are currently available.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Price</th>
                <?php if ($is_logged_in): // Show Actions column to ALL logged-in users ?>
                    <th>Actions</th> 
                <?php endif; ?>
            </tr>
            <?php foreach ($books as $book): ?>
            <tr>
                <td><?= htmlspecialchars($book['title']) ?></td>
                <td><?= htmlspecialchars($book['author']) ?></td>
                <td>$<?= htmlspecialchars(number_format($book['price'], 2)) ?></td>
                
                <?php if ($is_logged_in): // Show Edit/Delete links to ALL logged-in users ?>
                <td>
                    <a href="edit_book.php?id=<?= $book['id'] ?>">Edit</a> | 
                    <a href="delete_book.php?id=<?= $book['id'] ?>" 
                       onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
</body>
</html>