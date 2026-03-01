<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $story_id = $_POST['story_id'];
    $user_id = $_SESSION['user_id'];
    $comment = trim($_POST['comment']);

    $sql = "INSERT INTO comments (story_id, user_id, comment) VALUES (:story_id, :user_id, :comment)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':story_id', $story_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit;
    } else {
        echo "Error adding comment.";
    }
}
?>

