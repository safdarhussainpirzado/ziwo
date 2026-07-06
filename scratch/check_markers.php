<?php
$file = '/home/mrpirzado/projects/130/database/seeders/GeospatialMarkerSeeder.php';
$content = file_get_contents($file);
preg_match("/'encodedData' => '(.*?)',/s", $content, $matches);
if (isset($matches[1])) {
    $decoded = gzdecode(base64_decode($matches[1]));
    $lines = explode("\n", $decoded);
    echo implode("\n", array_slice($lines, 0, 10)) . "\n";
} else {
    echo "Encoded data not found\n";
}
