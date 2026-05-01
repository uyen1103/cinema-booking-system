<?php
$mysqli = new mysqli('localhost', 'root', '', 'movie_booking');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}
$query = "UPDATE Movies SET poster_url = CONCAT('movies/', poster_url) WHERE poster_url IS NOT NULL AND poster_url NOT LIKE 'movies/%' AND poster_url NOT LIKE 'http%'";
if ($mysqli->query($query)) {
    echo 'Query executed successfully' . PHP_EOL;
    echo 'Rows updated: ' . $mysqli->affected_rows . PHP_EOL;
} else {
    echo 'Query failed: ' . $mysqli->error . PHP_EOL;
}
$mysqli->close();
?>
