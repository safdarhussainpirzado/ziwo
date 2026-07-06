<?php
require 'vendor/autoload.php'; // In case we need it, but likely not

$file = 'database/seeders/GeospatialMarkerSeeder.php';
$content = file_get_contents($file);
preg_match("/\\\$encodedData = '(.*?)';/s", $content, $matches);
$encodedData = str_replace(["\n", "\r", " "], '', $matches[1]);
$decoded = base64_decode($encodedData);

echo "Decoded length: " . strlen($decoded) . "\n";
echo "First 10 bytes (hex): " . bin2hex(substr($decoded, 0, 10)) . "\n";

echo "Trying gzdecode...\n";
$data = @gzdecode($decoded);
if ($data !== false) {
    echo "gzdecode SUCCESS! length: " . strlen($data) . "\n";
} else {
    echo "gzdecode FAILED\n";
}

echo "Trying gzinflate...\n";
$data = @gzinflate($decoded);
if ($data !== false) {
    echo "gzinflate SUCCESS! length: " . strlen($data) . "\n";
} else {
    echo "gzinflate FAILED\n";
}

echo "Trying gzuncompress...\n";
$data = @gzuncompress($decoded);
if ($data !== false) {
    echo "gzuncompress SUCCESS! length: " . strlen($data) . "\n";
} else {
    echo "gzuncompress FAILED\n";
}
