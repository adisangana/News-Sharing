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
    $title = $_POST['title'];
    $body = $_POST['body'];
    $link = $_POST['link'];

    // Update the story only if it belongs to the current user
    $sql = "UPDATE stories SET title = :title, body = :body, link = :link WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':body', $body);
    $stmt->bindParam(':link', $link);
    $stmt->bindParam(':id', $story_id);
    $stmt->bindParam(':user_id', $user_id);

    if ($stmt->execute()) {
        header('Location: index.php');
        exit;
    } else {
        echo "Error updating story.";
    }
}
?>

