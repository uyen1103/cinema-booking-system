<?php
$mysqli = new mysqli('localhost', 'root', '', 'movie_booking');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}
$query = "SELECT movie_id, title, poster_url FROM Movies WHERE poster_url LIKE 'movies/%' LIMIT 5";
$result = $mysqli->query($query);
if ($result) {
    echo 'Sample of updated records:' . PHP_EOL;
    while ($row = $result->fetch_assoc()) {
        echo "Movie ID: " . $row['movie_id'] . " | Title: " . $row['title'] . " | URL: " . $row['poster_url'] . PHP_EOL;
    }
} else {
    echo 'Query failed: ' . $mysqli->error . PHP_EOL;
}
$mysqli->close();
?>
