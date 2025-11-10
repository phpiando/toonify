<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Toonify\Toonify;

echo "=== Exemplo: Extraindo TOON de Markdown (Respostas de LLMs) ===\n\n";

// Simula uma resposta de um LLM que inclui TOON em markdown
$llmResponse = <<<'MARKDOWN'
Aqui estão os dados que você solicitou em formato Toonify:

```toon
users[3,]{id,name,email}:
  1,Roni,roni@phpiando.com
  2,Sommerfeld,sommerfeld@phpiando.com
  3,Phpiando,contact@phpiando.com
```

Esses dados mostram três usuários com suas informações básicas.
MARKDOWN;

echo "Resposta do LLM:\n";
echo $llmResponse . "\n\n";

// Extrai o conteúdo TOON do markdown
$toonContent = Toonify::extractFromMarkdown($llmResponse);

if ($toonContent) {
    echo "✓ TOON extraído com sucesso!\n\n";
    echo "Conteúdo Toonify:\n";
    echo $toonContent . "\n\n";

    // Converte para JSON
    $json = Toonify::toJsonString($toonContent);
    echo "Convertido para JSON:\n";
    echo $json . "\n\n";

    // Ou use o método direto
    $jsonDirect = Toonify::toJsonString($toonContent);
    echo "Dados parseados:\n";
    print_r(json_decode($jsonDirect, true));
} else {
    echo "✗ Nenhum conteúdo TOON encontrado\n";
}

echo "\n=== Exemplo 2: Resposta sem blocos de código ===\n\n";

$simpleResponse = <<<'TOON'
products[2,]{sku,qty,price}:
  A1,2,9.99
  B2,1,14.50
TOON;

echo "Resposta simples (sem ```toon):\n";
echo $simpleResponse . "\n\n";

$extracted = Toonify::extractFromMarkdown($simpleResponse);
if ($extracted) {
    echo "✓ Também foi extraído com sucesso!\n";
    $json = Toonify::toJsonString($extracted);
    echo "JSON:\n" . $json . "\n";
}
