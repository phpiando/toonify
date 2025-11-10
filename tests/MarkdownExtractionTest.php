<?php

declare(strict_types=1);

namespace Toonify\Tests;

use PHPUnit\Framework\TestCase;
use Toonify\Toonify;

class MarkdownExtractionTest extends TestCase
{
    public function testExtractFromMarkdownCodeBlock(): void
    {
        $markdown = <<<'MD'
Aqui estão os dados:

```toon
users[2,]{id,name}:
  1,Alice
  2,Bob
```

Espero que ajude!
MD;

        $toon = Toonify::extractFromMarkdown($markdown);

        $this->assertNotNull($toon);
        $this->assertStringContainsString('users[2,]{id,name}:', $toon);
        $this->assertStringContainsString('1,Alice', $toon);
    }

    public function testExtractFromUppercaseMarkdownCodeBlock(): void
    {
        $markdown = <<<'MD'
```TOON
name: Alice
age: 30
```
MD;

        $toon = Toonify::extractFromMarkdown($markdown);

        $this->assertNotNull($toon);
        $this->assertStringContainsString('name: Alice', $toon);
    }

    public function testExtractFromPlainToon(): void
    {
        $plainToon = "users[2,]{id,name}:\n  1,Alice\n  2,Bob";

        $extracted = Toonify::extractFromMarkdown($plainToon);

        $this->assertNotNull($extracted);
        $this->assertEquals(trim($plainToon), trim($extracted));
    }

    public function testExtractReturnsNullForNoToon(): void
    {
        $markdown = "Isso é apenas texto sem TOON.";

        $toon = Toonify::extractFromMarkdown($markdown);

        $this->assertNull($toon);
    }

    public function testConvertMarkdownToJson(): void
    {
        $markdown = <<<'MD'
Resposta do LLM:

```toon
products[2,]{sku,price}:
  A1,9.99
  B2,14.50
```

Esses são os produtos.
MD;

        $toon = Toonify::extractFromMarkdown($markdown);
        $this->assertNotNull($toon);

        $json = Toonify::toJsonString($toon);
        $data = json_decode($json, true);

        $this->assertCount(2, $data['products']);
        $this->assertEquals('A1', $data['products'][0]['sku']);
        $this->assertEquals(9.99, $data['products'][0]['price']);
    }

    public function testExtractMultipleLineTypes(): void
    {
        $markdown = <<<'MD'
```toon
user:
  name: Alice
  tags[3,]: php,toon,llm
  projects[2,]{id,name}:
    1,Project A
    2,Project B
```
MD;

        $toon = Toonify::extractFromMarkdown($markdown);
        $this->assertNotNull($toon);

        $data = Toonify::decode($toon);

        $this->assertEquals('Alice', $data['user']['name']);
        $this->assertEquals(['php', 'toon', 'llm'], $data['user']['tags']);
        $this->assertCount(2, $data['user']['projects']);
    }

    public function testExtractWithBackticksInContent(): void
    {
        $markdown = <<<'MD'
Aqui está o código:

```toon
code: "function() { return `hello`; }"
```
MD;

        $toon = Toonify::extractFromMarkdown($markdown);
        $this->assertNotNull($toon);

        $data = Toonify::decode($toon);
        $this->assertStringContainsString('hello', $data['code']);
    }
}
