<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Http;

$username = 'iqra.zainab@nayatel.com';
$password = 'ZIWODEMOa@1234';
$proxyUrl = 'https://nayatel-api.aswat.co';

$loginResponse = Http::post("{$proxyUrl}/auth/login", [
    'username' => $username,
    'password' => $password,
]);
$loginData = $loginResponse->json();
$token = $loginData['content']['access_token'] ?? null;

if (!$token) {
    echo "Login failed!\n";
    exit;
}
echo "Token: {$token}\n";

$testUrls = [
    "{$proxyUrl}/v1/stats/live/kpis",
    "{$proxyUrl}/stats/live/kpis",
    "{$proxyUrl}/v1/live/kpis",
    "{$proxyUrl}/live/kpis",
    "{$proxyUrl}/admin/users",
    "{$proxyUrl}/queues",
];

foreach ($testUrls as $url) {
    try {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'access_token' => $token,
        ])->timeout(3)->get($url);
        echo "GET {$url} -> Status: " . $response->status() . "\n";
        if ($response->successful()) {
            echo "  [SUCCESS] Keys: " . implode(', ', array_keys($response->json() ?? [])) . "\n";
        }
    } catch (\Exception $e) {
        echo "GET {$url} -> Exception: " . $e->getMessage() . "\n";
    }
}
