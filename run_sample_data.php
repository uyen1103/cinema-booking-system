<?php
$mysqli = new mysqli('localhost', 'root', '', 'movie_booking');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

$sql = file_get_contents('database/sample_data.sql');
if ($mysqli->multi_query($sql)) {
    echo 'Sample data inserted successfully' . PHP_EOL;
} else {
    echo 'Query failed: ' . $mysqli->error . PHP_EOL;
}
$mysqli->close();
?>