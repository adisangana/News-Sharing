<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $comment_id = $_POST['comment_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM comments WHERE id = :id AND user_id = :user_id");
    $stmt->execute([':id' => $comment_id, ':user_id' => $user_id]);
    $comment = $stmt->fetch();

    if ($comment):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Comment</title>
</head>
<body>
    <h1>Edit Your Comment</h1>
    <form action="update_comment.php" method="post">
        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
        <textarea name="comment" required><?= htmlspecialchars($comment['comment']) ?></textarea><br>
        <input type="submit" value="Update Comment">
    </form>
</body>
</html>
<?php
    else:
        echo "Comment not found or you don't have permission to edit it.";
    endif;
}
?>

