# 🎒 toonify

PHP library for conversion between JSON and TOON (Token-Oriented Object Notation) - an optimized format for saving tokens in Large Language Models (LLMs).

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.3-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

## ☕ Sponsors
If you find this plugin useful, consider sponsoring its development:
- [Sponsor on GitHub](https://github.com/sponsors/phpiando)

## 📖 What is TOON?

TOON (Token-Oriented Object Notation) is a compact and readable serialization format, specifically designed to reduce token usage when sending structured data to LLMs. It can save **30-60% of tokens** compared to JSON.

### Why use TOON?

- 💸 **Token savings**: 30-60% fewer tokens than JSON
- 🤖 **Optimized for LLMs**: Better understanding and accuracy
- 📊 **Perfect for tabular data**: Uniform arrays of objects
- 🔄 **Lossless conversion**: Converts to and from JSON without data loss

### Quick Comparison

**JSON (123 tokens):**
```json
{
  "title": "Employees",
  "total": 3,
  "data": [
    {
      "id": 1,
      "name": "Roni",
      "email": "roni@phpiando.com"
    },
    {
      "id": 2,
      "name": "Sommerfeld",
      "email": "sommerfeld@phpiando.com"
    },
    {
      "id": 3,
      "name": "PHPiando",
      "email": "phpiando@phpiando.com"
    }
  ]
}
```

**TOON (64 tokens - 48% savings):**
```toon
title: Employees
total: 3
data[3]{id,name,email}:
  1,Roni,roni@phpiando.com
  2,Sommerfeld,sommerfeld@phpiando.com
  3,PHPiando,phpiando@phpiando.com
```

## 🚀 Installation

```bash
composer require phpiando/toonify
```

### Requirements

- PHP 8.3 or higher
- ext-json
- ext-mbstring

## 📚 Basic Usage

### Simple Conversion

```php
use Toonify\Toon;

// PHP Array to TOON
$data = [
  'title' => 'Employees',
  'total' => 3,
  'data' => [
    ['id' => 1, 'name' => 'Roni', 'email' => 'roni@phpiando.com'],
    ['id' => 2, 'name' => 'Sommerfeld', 'email' => 'sommerfeld@phpiando.com'],
    ['id' => 3, 'name' => 'PHPiando', 'email' => 'phpiando@phpiando.com'],
  ]
];

$toon = Toonify::encode($data);
echo $toon;
// title: Employees
// total: 3
// data[3]{id,name,email}:
//   1,Roni,roni@phpiando.com
//   2,Sommerfeld,sommerfeld@phpiando.com
//   3,PHPiando,phpiando@phpiando.com

// TOON to PHP Array
$decoded = Toonify::decode($toon);
```

### From JSON String

```php
$json = '{"name": "Roni Sommerfeld", "age": 37}';
$toon = Toonify::fromJsonString($json);

$jsonBack = Toonify::toJsonString($toon);
```

### From Local Files

```php
// JSON → TOON
$toon = Toonify::fromJsonDisk('/path/to/data.json');
file_put_contents('/path/to/output.toon', $toon);

// TOON → JSON
$json = Toonify::toJsonDisk('/path/to/data.toon');
file_put_contents('/path/to/output.json', $json);
```

### From URLs

```php
// Fetch JSON from URL and convert to TOON
$toon = Toonify::fromJsonUrl('https://api.example.com/data.json');

// Fetch TOON from URL and convert to JSON
$json = Toonify::toJsonUrl('https://example.com/data.toon');
```

### Extract TOON from Markdown (LLM Responses)

A special feature for working with LLM responses that frequently return data in markdown blocks:

```php
$llmResponse = <<<'MARKDOWN'
Here is the data:

```toon
users[2]{id,name}:
  1,Roni
  2,PHPiando
```

Hope this helps!
MARKDOWN;

// Extract only TOON content
$toon = Toonify::extractFromMarkdown($llmResponse);

// Or convert directly to JSON
$json = Toonify::toJsonString($toon);
```

## ⚙️ Configuration Options

### Encoding Options

```php
$toon = Toonify::encode($data, [
  'delimiter' => ',',      // ',', "\t" or '|'
  'indent' => 2,           // Spaces per level
  'lengthMarker' => '',    // Length prefix (optional)
]);
```

**Delimiter examples:**

```php
// Comma (default)
$toon = Toonify::encode($data, ['delimiter' => ',']);
// users[2,]{id,name}:

// Tab (more token efficient)
$toon = Toonify::encode($data, ['delimiter' => "\t"]);
// users[2	]{id,name}:

// Pipe
$toon = Toonify::encode($data, ['delimiter' => '|']);
// users[2|]{id,name}:
```

### Decoding Options

```php
$data = Toonify::decode($toon, [
  'strict' => true,        // Strict validation (default: true)
  'indent' => 2,           // Expected spaces per level
]);
```

## 📋 Supported Formats

### Simple Objects
```php
['name' => 'Roni Sommerfeld', 'age' => 37]
```
```toon
name: Roni Sommerfeld
age: 37
```

### Primitive Arrays
```php
[1, 2, 3, 4, 5]
```
```toon
[5,]: 1,2,3,4,5
```

### Tabular Arrays (Sweet Spot!)
```php
[
  ['id' => 1, 'name' => 'Roni'],
  ['id' => 2, 'name' => 'PHPiando']
]
```
```toon
[2,]{id,name}:
  1,Roni
  2,PHPiando
```

### Mixed Arrays
```php
[
  ['x' => 1],
  42,
  'hello'
]
```
```toon
[3,]:
  - x: 1
  - 42
  - hello
```

## 🎯 Use Cases

### 1. Send data to LLMs
```php
$repositories = fetchGitHubRepos();
$toon = Toonify::encode($repositories, ['delimiter' => "\t"]);

// Save tokens in prompt
$prompt = "Analyze these repositories:\n\n" . $toon;
$response = $llm->complete($prompt);
```

### 2. Process LLM responses
```php
$response = $llm->complete("List 5 products in TOON format");
$toon = Toonify::extractFromMarkdown($response);
$products = Toonify::decode($toon);
```

### 3. Optimized APIs
```php
// Endpoint that returns TOON instead of JSON
header('Content-Type: text/plain');
$data = $database->query('SELECT * FROM users');
echo Toonify::encode($data);
```

### 4. Compact logs
```php
$logData = [
  'timestamp' => time(),
  'events' => $events
];
file_put_contents('log.toon', Toonify::encode($logData));
```

## 🧪 Testing

```bash
composer test
```

## 📊 Benchmarks

Run examples to see token savings:

```bash
php examples/basic.php
```

Typical results:
- Simple objects: ~20-30% savings
- Tabular arrays: ~40-60% savings
- Mixed arrays: ~25-35% savings

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the project
2. Create a feature branch (`git checkout -b feature/MyFeature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/MyFeature`)
5. Open a Pull Request

## 📄 License

This project is under the MIT license. See the [LICENSE](LICENSE) file for more details.

## 🔗 Useful Links

- [Official TOON specification](https://github.com/toon-format/spec)
- [Reference TypeScript implementation](https://github.com/toon-format/toon)
- [Online playground](https://jsontoon.net/)

## 🙏 Credits

TOON was created by [Johann Schopplich](https://github.com/johannschopplich).

This PHP library is an implementation of the official TOON v1.3 specification.

## 💬 Support

- 🐛 Issues: [GitHub Issues](https://github.com/phpiando/toonify/issues)
- 💡 Discussions: [GitHub Discussions](https://github.com/phpiando/toonify/discussions)


---

Made with ❤️ for the PHP community
