<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $story_id = $_POST['story_id'];

    // Prepare a delete statement to remove the story from the database
    $sql = "DELETE FROM stories WHERE id = :story_id";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':story_id', $story_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            header('Location: index.php');  // Redirect to index after deletion
            exit;
        } else {
            echo "Error deleting story.";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

