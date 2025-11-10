<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Toonify\Toonify;

echo "=== Basic Example: JSON to TOON ===\n\n";

// Example data
$data = [
    'title' => 'Employees',
    'total' => 3,
    'data' => [
        ['id' => 1, 'name' => 'Roni', 'email' => 'roni@phpiando.com'],
        ['id' => 2, 'name' => 'Sommerfeld', 'email' => 'sommerfeld@phpiando.com'],
        ['id' => 3, 'name' => 'PHPiando', 'email' => 'phpiando@phpiando.com', 'role' => ['id' => 1, 'name' => 'admin']],
    ]
];

echo "Original JSON:\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

// Convert to TOON
$toon = Toonify::encode($data);
echo "Toonify:\n";
echo $toon . "\n\n";

// Convert back to JSON
$decoded = Toonify::decode($toon);
echo "Decoded back:\n";
echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n\n";

// Example with different delimiters
echo "=== With TAB delimiter ===\n";
$toonTab = Toonify::encode($data, ['delimiter' => "\t"]);
echo $toonTab . "\n\n";

echo "=== With PIPE delimiter ===\n";
$toonPipe = Toonify::encode($data, ['delimiter' => '|']);
echo $toonPipe . "\n\n";

// Token savings
$jsonSize = strlen(json_encode($data));
$toonSize = strlen($toon);
$savings = round((($jsonSize - $toonSize) / $jsonSize) * 100, 2);

echo "=== Statistics ===\n";
echo "JSON Size: $jsonSize characters\n";
echo "Toonify Size: $toonSize characters\n";
echo "Savings: $savings%\n";
