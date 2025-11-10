<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Toonify\Toonify;

echo "=== Advanced Example: LLM Integration ===\n\n";

// Simulates database data
$customers = [
    ['id' => 1, 'name' => 'Acme Corp', 'revenue' => 150000, 'industry' => 'Technology', 'active' => true],
    ['id' => 2, 'name' => 'Global Industries', 'revenue' => 250000, 'industry' => 'Manufacturing', 'active' => true],
    ['id' => 3, 'name' => 'Tech Solutions', 'revenue' => 89000, 'industry' => 'Technology', 'active' => false],
    ['id' => 4, 'name' => 'Finance Group', 'revenue' => 320000, 'industry' => 'Finance', 'active' => true],
    ['id' => 5, 'name' => 'PHPiando Corp', 'revenue' => 125000, 'industry' => 'Technology', 'active' => true],
];

// 1. Convert to TOON with TAB delimiter (more efficient)
$toon = Toonify::encode(['customers' => $customers], ['delimiter' => "\t"]);

echo "Data in TOON (optimized for LLM):\n";
echo $toon . "\n\n";

// Calculate savings
$jsonCompact = json_encode(['customers' => $customers]);
$jsonPretty = json_encode(['customers' => $customers], JSON_PRETTY_PRINT);
$toonSize = strlen($toon);
$jsonCompactSize = strlen($jsonCompact);
$jsonPrettySize = strlen($jsonPretty);

$savingsCompact = round((($jsonCompactSize - $toonSize) / $jsonCompactSize) * 100, 2);
$savingsPretty = round((($jsonPrettySize - $toonSize) / $jsonPrettySize) * 100, 2);

echo "=== Size Comparison ===\n";
echo "JSON Compact:  $jsonCompactSize bytes\n";
echo "JSON Pretty:   $jsonPrettySize bytes\n";
echo "Toonify:       $toonSize bytes\n";
echo "Savings:       $savingsCompact% vs compact, $savingsPretty% vs pretty\n\n";

// 2. Build an LLM prompt
$prompt = <<<PROMPT
Analyze the following customer data and provide insights:

$toon

Answer the following questions:
1. What is the average revenue per industry?
2. How many customers are active vs inactive?
3. Which industries have the highest total revenue?
4. Return a summary in TOON format with the structure:
   - insights[N]{industry,avg_revenue,total_revenue,active_count}

IMPORTANT: Respond with TOON inside a ```toon ... ``` block
PROMPT;

echo "=== LLM Prompt ===\n";
echo $prompt . "\n\n";

// 3. Simulates LLM response
$llmResponse = <<<'RESPONSE'
Analyzing the provided data:

## Insights

1. **Average revenue per industry:**
   - Technology: $119,500
   - Manufacturing: $250,000
   - Finance: $320,000
   - Retail: $125,000

2. **Customer status:**
   - Active: 4 customers
   - Inactive: 1 customer

3. **Industries with highest revenue:**
   1. Finance: $320,000
   2. Manufacturing: $250,000
   3. Retail: $125,000

## Summary Data
```toon
insights[4,]{industry,avg_revenue,total_revenue,active_count}:
  Technology,119500,358500,2
  Manufacturing,250000,250000,1
  Finance,320000,320000,1
  Retail,125000,125000,0
```
RESPONSE;

echo "=== Simulated LLM Response ===\n";
echo $llmResponse . "\n\n";
// 4. Extract TOON from LLM response
$toonContent = Toonify::extractFromMarkdown($llmResponse);
if ($toonContent) {
    echo "✓ TOON extracted from LLM response:\n";
    echo $toonContent . "\n\n";

    // Convert TOON to JSON
    $json = Toonify::toJsonString($toonContent);
    echo "Converted TOON to JSON:\n";
    echo $json . "\n\n";

    // Decode JSON to array
    $data = json_decode($json, true);
    echo "Decoded Data Array:\n";
    print_r($data);
} else {
    echo "✗ No TOON content found in LLM response.\n";
}

echo "\n=== End of LLM Integration Example ===\n";