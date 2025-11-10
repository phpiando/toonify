<?php

/**
 * Toonify Examples
 * 
 * This file demonstrates various usage patterns of the Toonify library
 */

require_once __DIR__ . '/vendor/autoload.php';

use Phpiando\Toonify\Toonify;

echo "=== Toonify Library Examples ===\n\n";

// Example 1: Basic JSON to TOON conversion
echo "Example 1: JSON to TOON\n";
echo "-----------------------\n";
$json = '{"name":"Alice","age":30,"active":true}';
echo "JSON: $json\n";
$toon = Toonify::fromJson($json);
echo "TOON: $toon\n";
echo "Token savings: " . round((1 - strlen($toon) / strlen($json)) * 100) . "%\n\n";

// Example 2: TOON to JSON conversion
echo "Example 2: TOON to JSON\n";
echo "-----------------------\n";
$toon = "['name':'Bob';'age':25;'verified':f]";
echo "TOON: $toon\n";
$json = Toonify::toJson($toon);
echo "JSON: $json\n\n";

// Example 3: Working with arrays
echo "Example 3: Arrays\n";
echo "-----------------\n";
$data = [
    'items' => [1, 2, 3, 4, 5],
    'tags' => ['php', 'library', 'toon']
];
echo "PHP Array: " . json_encode($data) . "\n";
$toonify = new Toonify();
$toon = $toonify->encode($data);
echo "TOON: $toon\n\n";

// Example 4: Nested structures
echo "Example 4: Nested Structures\n";
echo "----------------------------\n";
$complex = [
    'user' => [
        'id' => 123,
        'profile' => [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]
    ],
    'settings' => [
        'theme' => 'dark',
        'notifications' => true
    ]
];
$json = json_encode($complex);
$toon = $toonify->encode($complex);
echo "JSON length: " . strlen($json) . " chars\n";
echo "TOON length: " . strlen($toon) . " chars\n";
echo "Space saved: " . (strlen($json) - strlen($toon)) . " chars (" . 
     round((1 - strlen($toon) / strlen($json)) * 100) . "%)\n";
echo "TOON: $toon\n\n";

// Example 5: Round-trip conversion
echo "Example 5: Round-trip Conversion\n";
echo "--------------------------------\n";
$original = ['status' => 'success', 'count' => 42, 'data' => ['a', 'b', 'c']];
echo "Original: " . json_encode($original) . "\n";
$toon = $toonify->encode($original);
echo "As TOON: $toon\n";
$restored = $toonify->decode($toon);
echo "Restored: " . json_encode($restored) . "\n";
echo "Match: " . ($original == $restored ? 'YES' : 'NO') . "\n\n";

// Example 6: All data types
echo "Example 6: All Data Types\n";
echo "-------------------------\n";
$allTypes = [
    'string' => 'hello',
    'integer' => 42,
    'float' => 3.14,
    'boolean_true' => true,
    'boolean_false' => false,
    'null_value' => null,
    'array' => [1, 2, 3],
    'object' => ['nested' => 'value']
];
$json = json_encode($allTypes);
$toon = $toonify->encode($allTypes);
echo "JSON: $json\n";
echo "TOON: $toon\n";
echo "Token efficiency: " . round((strlen($json) - strlen($toon)) / strlen($json) * 100) . "% reduction\n\n";

// Example 7: Special characters handling
echo "Example 7: Special Characters\n";
echo "-----------------------------\n";
$special = [
    'message' => "It's a test: data; with, special [characters] (values)",
    'escaped' => 'Line 1\nLine 2'
];
$json = json_encode($special);
$toon = $toonify->encode($special);
echo "Original: " . json_encode($special) . "\n";
echo "TOON: $toon\n";
$decoded = $toonify->decode($toon);
echo "Decoded: " . json_encode($decoded) . "\n";
echo "Match: " . ($special == $decoded ? 'YES' : 'NO') . "\n\n";

echo "=== Examples Complete ===\n";
