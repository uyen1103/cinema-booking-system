<?php
$mysqli = new mysqli('localhost', 'root', '', 'movie_booking');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}
$query = "DESCRIBE Movies";
$result = $mysqli->query($query);
if ($result) {
    echo 'Movies table structure:' . PHP_EOL;
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . PHP_EOL;
    }
} else {
    echo 'Query failed: ' . $mysqli->error . PHP_EOL;
}
$mysqli->close();
?>
