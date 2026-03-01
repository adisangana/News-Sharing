<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

// Function to extract the Open Graph image from a URL
function fetch_image_from_link($url) {
    $html = file_get_contents($url);
    preg_match('/<meta property="og:image" content="(.*?)"/', $html, $matches);
    return $matches[1] ?? null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $body = trim($_POST['body']);
    $link = trim($_POST['link']);
    $image = fetch_image_from_link($link); // Auto-fetch image from the link
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO stories (user_id, title, body, link, image) VALUES (:user_id, :title, :body, :link, :image)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':body', $body, PDO::PARAM_STR);
    $stmt->bindParam(':link', $link, PDO::PARAM_STR);
    $stmt->bindParam(':image', $image, PDO::PARAM_STR);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit;
    } else {
        echo "Error adding story.";
    }
}
?>

