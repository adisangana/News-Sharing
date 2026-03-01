<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

$user_id = $_SESSION['user_id'];

// Fetch stories from the database
$storyStmt = $pdo->query("SELECT id, user_id, title, body, link, image FROM stories ORDER BY id DESC");
$stories = $storyStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Welcome to the News Portal</h1>
        <nav>
            <a href="addstory.html">Add New Story</a> |
            <a href="index.php">Home</a> |
            <a href="fetch_news.php">Fetch Latest News</a> |  <!-- Link to Fetch News -->
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="stories-container">
        <?php if ($storyStmt->rowCount() > 0): ?>
            <?php foreach ($stories as $story): ?>
                <div class="story">
                    <?php if (!empty($story['image'])): ?>
                        <div class="story-image">
                            <img src="<?= htmlspecialchars($story['image']) ?>" alt="Story Image">
                        </div>
                    <?php endif; ?>
                    <div class="story-content">
                        <h2 class="story-title"><?= htmlspecialchars($story['title']) ?></h2>
                        <p class="story-body"><?= htmlspecialchars($story['body']) ?></p>
                        <?php if (!empty($story['link'])): ?>
                            <a class="story-link" href="<?= htmlspecialchars($story['link']) ?>" target="_blank">Read more...</a>
                        <?php endif; ?>

                        <!-- Only show Edit/Delete if the story belongs to the logged-in user -->
                        <?php if ($story['user_id'] == $user_id): ?>
                            <div class="story-controls">
                                <form action="edit_story.php" method="post" style="display:inline;">
                                    <input type="hidden" name="story_id" value="<?= $story['id'] ?>">
                                    <input type="submit" value="Edit Story">
                                </form>
                                <form action="delete_story.php" method="post" style="display:inline;">
                                    <input type="hidden" name="story_id" value="<?= $story['id'] ?>">
                                    <input type="submit" value="Delete Story">
                                </form>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Comments Section -->
                        <div class="comments-section">
                            <h3>Comments</h3>
                            <?php
                            // Fetch comments for the story
                            $commentStmt = $pdo->prepare("SELECT comments.id, comments.comment, comments.user_id, users.username 
                                                          FROM comments 
                                                          JOIN users ON comments.user_id = users.id 
                                                          WHERE story_id = :story_id");
                            $commentStmt->execute([':story_id' => $story['id']]);
                            $comments = $commentStmt->fetchAll();

                            if (count($comments) > 0):
                                foreach ($comments as $comment):
                            ?>
                                    <div class="comment">
                                        <strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                                        <p><?= htmlspecialchars($comment['comment']) ?></p>
                                        
                                        <!-- Show Edit/Delete if comment belongs to logged-in user -->
                                        <?php if ($comment['user_id'] == $user_id): ?>
                                            <form action="edit_comment.php" method="post" style="display:inline;">
                                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                                <input type="hidden" name="story_id" value="<?= $story['id'] ?>">
                                                <input type="submit" value="Edit Comment">
                                            </form>
                                            <form action="delete_comment.php" method="post" style="display:inline;">
                                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                                <input type="submit" value="Delete Comment">
                                            </form>
                                        <?php endif; ?>
                                    </div>
                            <?php
                                endforeach;
                            else:
                                echo "<p>No comments yet.</p>";
                            endif;
                            ?>

                            <!-- Comment Form -->
                            <form action="add_comment.php" method="post">
                                <textarea name="comment" placeholder="Add a comment..." required></textarea>
                                <input type="hidden" name="story_id" value="<?= $story['id'] ?>">
                                <button type="submit">Post Comment</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No stories found!</p>
        <?php endif; ?>
    </div>

    <!-- Footer Section -->
    <footer>
        <div class="footer-container">
            <div class="footer-column">
                <h3>Home</h3>
                <ul>
                    <li><a href="#">Life</a></li>
                    <li><a href="#">Health and Wellness</a></li>
                    <li><a href="#">Technology</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>About</h3>
                <ul>
                    <li><a href="#">Community Guidelines</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Digg</h3>
                <ul>
                    <li><a href="#">Advertise</a></li>
                    <li><a href="#">Merch</a></li>
                    <li><a href="#">Terms</a></li>
                    <li><a href="#">Privacy Notice</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; Copyright 2024 News Portal. All Rights Reserved.</p>
            <p>Opinions expressed on this site are the author's own and do not necessarily reflect the views of others.</p>
        </div>
    </footer>
</body>
</html>

