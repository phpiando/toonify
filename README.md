# Toonify

A PHP library that converts JSON to TOON and TOON to JSON, designed to optimize data exchange for LLMs. Includes fast encoding/decoding, compact syntax, and easy integration with existing PHP applications. Ideal for projects that need efficient token usage and structured data transformation.

## Features

- 🚀 **Fast Encoding/Decoding**: Efficient conversion between JSON and TOON formats
- 📦 **Compact Syntax**: Optimized for LLM token usage with minimal overhead
- 🔄 **Bidirectional**: Convert JSON to TOON and TOON back to JSON
- ✨ **Easy Integration**: Simple API with static helpers for quick usage
- 🧪 **Well Tested**: Comprehensive test suite with PHPUnit
- 📖 **Type Safe**: Proper type handling for strings, numbers, booleans, arrays, and objects

## TOON Format Specification

TOON (Token-Optimized Object Notation) is a compact data format designed to minimize token usage for LLM interactions:

- **Objects**: `['key1':'value1';'key2':'value2']`
- **Arrays**: `(value1,value2,value3)`
- **Strings**: `'string value'` (with escape sequences for special characters)
- **Numbers**: `42`, `3.14` (no quotes)
- **Booleans**: `t` (true), `f` (false)
- **Null**: `n`
- **Nested structures**: Fully supported with brackets and parentheses

### Format Comparison

JSON:
```json
{"name":"Alice","age":30,"active":true,"tags":["php","developer"]}
```

TOON:
```
['name':'Alice';'age':30;'active':t;'tags':('php','developer')]
```

**Token savings**: ~30-40% reduction in typical use cases

## Installation

Install via Composer:

```bash
composer require phpiando/toonify
```

## Requirements

- PHP 7.4 or higher

## Usage

### Basic Usage

```php
<?php

require_once 'vendor/autoload.php';

use Phpiando\Toonify\Toonify;

// Create an instance
$toonify = new Toonify();

// JSON to TOON
$json = '{"name":"John","age":30,"active":true}';
$toon = $toonify->jsonToToon($json);
echo $toon; // ['name':'John';'age':30;'active':t]

// TOON to JSON
$toon = "['name':'Alice';'age':25]";
$json = $toonify->toonToJson($toon);
echo $json; // {"name":"Alice","age":25}
```

### Static Helpers

```php
// Quick conversions without instantiation
$toon = Toonify::fromJson('{"hello":"world"}');
$json = Toonify::toJson("['hello':'world']");
```

### Working with PHP Arrays

```php
// Encode PHP array to TOON
$data = [
    'user' => 'Bob',
    'score' => 95.5,
    'tags' => ['php', 'developer']
];
$toon = $toonify->encode($data);

// Decode TOON to PHP array
$data = $toonify->decode("['user':'Bob';'score':95.5]");
```

### Advanced Usage

```php
// Pretty print JSON output
$json = $toonify->toonToJson($toon, JSON_PRETTY_PRINT);

// Direct access to encoder/decoder
$encoder = $toonify->getEncoder();
$decoder = $toonify->getDecoder();
```

## Examples

### Simple Object

```php
$json = '{"name":"Alice","age":30}';
$toon = Toonify::fromJson($json);
// Result: ['name':'Alice';'age':30]
```

### Nested Structures

```php
$data = [
    'user' => [
        'name' => 'Bob',
        'email' => 'bob@example.com'
    ],
    'settings' => [
        'theme' => 'dark',
        'notifications' => true
    ]
];

$toon = $toonify->encode($data);
// Result: ['user':['name':'Bob';'email':'bob@example.com'];'settings':['theme':'dark';'notifications':t]]
```

### Arrays and Mixed Types

```php
$data = [
    'items' => [1, 2, 3, 4, 5],
    'mixed' => ['string', 42, true, null, 3.14]
];

$toon = $toonify->encode($data);
// Result: ['items':(1,2,3,4,5);'mixed':('string',42,t,n,3.14)]
```

## Testing

Run the test suite:

```bash
composer install
vendor/bin/phpunit
```

## Use Cases

- **LLM API Communication**: Reduce token usage in API requests/responses
- **Data Serialization**: Compact storage format for structured data
- **Configuration Files**: More readable and compact than JSON
- **Log Processing**: Efficient structured logging format
- **Cache Storage**: Minimize storage space for cached data

## Performance

TOON format typically provides:
- **30-40% token reduction** compared to JSON
- **Fast encoding/decoding** with minimal overhead
- **Memory efficient** processing
- **No external dependencies**

## License

MIT License

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Author

phpiando - [contact@phpiando.com]

## Links

- [GitHub Repository](https://github.com/phpiando/toonify)
- [Issue Tracker](https://github.com/phpiando/toonify/issues)
