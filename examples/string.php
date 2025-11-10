<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Toonify\Toonify;

$json = '{"name": "Roni Sommerfeld", "age": 37}';
$toon = Toonify::fromJsonString($json);

$jsonBack = Toonify::toJsonString($toon);

echo "=== String Example: JSON to TOON ===\n\n";
echo $toon . "\n\n";

echo "=== String Example: TOON to JSON ===\n\n";
echo $jsonBack . "\n";