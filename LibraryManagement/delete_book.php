<?php
// delete_book.php - Allows any logged-in user to delete a book

session_start();
require 'db.php'; 

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

// 2. Perform the DELETE operation
try {
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    // 3. Redirect with a success message
    $_SESSION['system_message'] = "Book deleted successfully!";
    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    // 4. Log error and redirect with failure message
    error_log("Delete error: " . $e->getMessage()); 
    $_SESSION['system_message'] = "Error deleting book.";
    header('Location: index.php');
    exit;
}
?>