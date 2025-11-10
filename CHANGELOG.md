# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-11-10

### Added
- Initial release of Toonify library
- `ToonEncoder` class for converting PHP data and JSON to TOON format
- `ToonDecoder` class for parsing TOON format back to PHP data
- `Toonify` facade class with convenience methods for easy integration
- Support for all JSON data types:
  - Strings (with escape sequences)
  - Numbers (integers and floats)
  - Booleans (compact `t`/`f` notation)
  - Null (compact `n` notation)
  - Objects (using `['key':'value']` syntax)
  - Arrays (using `(value1,value2)` syntax)
- Nested structure support for complex data
- Static helper methods (`fromJson()` and `toJson()`)
- Comprehensive test suite with 44 tests
- Full documentation with usage examples
- PHPUnit configuration
- Composer package configuration
- MIT License
- Examples file demonstrating various use cases
- CONTRIBUTING.md guide for contributors
- This CHANGELOG.md file

### Features
- 30-40% token reduction compared to JSON in typical use cases
- Fast encoding and decoding with minimal overhead
- Type-safe handling of all data types
- Proper escaping for special characters
- PSR-4 autoloading support
- PHP 7.4+ compatibility

### Documentation
- Comprehensive README with:
  - Installation instructions
  - Usage examples
  - TOON format specification
  - Performance comparison with JSON
  - Use cases and benefits
- Inline code documentation (PHPDoc)
- Working examples file

[1.0.0]: https://github.com/phpiando/toonify/releases/tag/v1.0.0
