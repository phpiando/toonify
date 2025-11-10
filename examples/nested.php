<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Toonify\Toonify;

echo "==== Exemplo Aninhado: JSON para TOON ====\n\n";

echo Toonify::encode([
    'user' => [
        'id' => 1,
        'email' => 'roni@phpiando.com',
        'metadata' => [
            'is_active' => true,
            'age' => 37
        ]
    ]
]);