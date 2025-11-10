<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Toonify\Toonify;

echo "=== Exemplo Primitivo: JSON para TOON ===\n\n";

echo Toonify::encode([
    'tags' => ['reading', 'gaming', 'coding']
]);

echo "\n\n";

echo Toonify::encode("Hello, Toonify!");

echo "\n\n";

echo Toonify::encode([
    'items' => [
        ['sku' => 'A1', 'qty' => 2, 'price' => 9.99],
        ['sku' => 'B2', 'qty' => 1, 'price' => 14.5]
    ]
]);