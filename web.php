<?php
// Compatibility entrypoint: redirect all legacy web.php URLs to index.php
$query = $_GET;
$target = 'index.php';
if (!empty($query)) {
    $target .= '?' . http_build_query($query);
}
header('Location: ' . $target);
exit;
