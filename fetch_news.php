<?php
require 'db.php';

// Use your News API key
$api_key = 'f8f49c3781944e259ce310d89915cbff';
$news_url = "https://newsapi.org/v2/top-headlines?country=us&apiKey=$api_key";

// Initialize curl
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $news_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Skip SSL verification if needed

// Set User-Agent header to avoid "userAgentMissing" error
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'User-Agent: NewsFetcher/1.0'
]);

$response = curl_exec($ch);

// Check for errors
if ($response === FALSE) {
    die("Error occurred while fetching news: " . curl_error($ch));
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    die("Error occurred while fetching news. HTTP Status Code: $http_code");
}

// Decode JSON response
$news_data = json_decode($response, true);

// Check if the response contains articles
if (!isset($news_data['articles'])) {
    die("No articles found or invalid API response.");
}

// Prepare a query to check for existing articles
$checkStmt = $pdo->prepare("SELECT COUNT(*) FROM stories WHERE link = :url");

// Prepare the insert statement
$stmt = $pdo->prepare("INSERT INTO stories (user_id, title, body, link, image) VALUES (1, :title, :description, :url, :image)");

// Loop through each article and store it in the database if not already present
$added_articles = 0;
foreach ($news_data['articles'] as $article) {
    $title = $article['title'];
    $description = $article['description'] ?: "No description available.";
    $url = $article['url'];
    $image = $article['urlToImage'] ?: "";  // Use empty string if no image found

    // Use utf8mb4-safe binding for special characters
    $title = mb_convert_encoding($title, 'UTF-8', 'UTF-8');
    $description = mb_convert_encoding($description, 'UTF-8', 'UTF-8');

    // Truncate image URL if it exceeds 500 characters
    $image = (strlen($image) > 500) ? substr($image, 0, 500) : $image;

    // Check if the article already exists based on its URL
    $checkStmt->execute([':url' => $url]);
    $articleExists = $checkStmt->fetchColumn();

    if ($articleExists > 0) {
        echo "Skipping duplicate article: " . htmlspecialchars($title) . "<br>";
        continue; // Skip if article already exists
    }

    // Bind values and execute the statement to insert the new article
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':url', $url);
    $stmt->bindParam(':image', $image);
    $stmt->execute();
    $added_articles++;
    echo "Inserted new article: " . htmlspecialchars($title) . "<br>";
}

echo "<p>News articles have been successfully fetched and stored in the database!</p>";
if ($added_articles > 0) {
    echo "<p>$added_articles new articles added to the database.</p>";
} else {
    echo "<p>No new articles were added.</p>";
}

// Link to go back to the index page
echo '<p><a href="index.php" style="font-size:18px; color:blue;">← Go back to Home</a></p>';
?>

