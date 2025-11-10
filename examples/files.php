<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Toonify\Toonify;

echo "=== Example: Working with Files and URLs ===\n\n";

// 1. Create a test JSON file
$testData = [
    'products' => [
        ['sku' => 'A1', 'name' => 'Widget', 'price' => 9.99, 'qty' => 100],
        ['sku' => 'B2', 'name' => 'Gadget', 'price' => 14.50, 'qty' => 50],
        ['sku' => 'C3', 'name' => 'Doohickey', 'price' => 7.25, 'qty' => 200],
    ]
];

$jsonPath = __DIR__ . '/test-data.json';
$toonPath = __DIR__ . '/test-data.toon';

file_put_contents($jsonPath, json_encode($testData, JSON_PRETTY_PRINT));
echo "✓ JSON file created: $jsonPath\n";

// 2. Convert JSON file to TOON
$toon = Toonify::fromJsonDisk($jsonPath);
file_put_contents($toonPath, $toon);
echo "✓ Converted to Toonify: $toonPath\n\n";

echo "Toonify Content:\n";
echo $toon . "\n\n";

// 3. Read TOON file and convert to JSON
$jsonFromToon = Toonify::toJsonDisk($toonPath);
echo "JSON retrieved from Toonify:\n";
echo $jsonFromToon . "\n\n";

// 4. Example with URL (simulated locally)
echo "=== Example with 'URL' (local file) ===\n";
try {
    // In production, you would use a real URL
    // $toonFromUrl = Toonify::fromJsonUrl('https://api.example.com/data.json');

    echo "To use real URLs, use:\n";
    echo "  \$toon = Toonify::fromJsonUrl('https://api.example.com/data.json');\n";
    echo "  \$json = Toonify::toJsonUrl('https://example.com/data.toon');\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// 5. Cleanup
echo "Cleaning temporary files...\n";
if (file_exists($jsonPath)) unlink($jsonPath);
if (file_exists($toonPath)) unlink($toonPath);
echo "✓ Completed!\n";
