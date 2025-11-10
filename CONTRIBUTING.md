# Contributing to Toonify

Thank you for your interest in contributing to Toonify! This document provides guidelines for contributing to the project.

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/YOUR_USERNAME/toonify.git`
3. Install dependencies: `composer install`
4. Create a new branch: `git checkout -b feature/your-feature-name`

## Development Setup

### Prerequisites

- PHP 7.4 or higher
- Composer

### Installing Dependencies

```bash
composer install
```

## Running Tests

We use PHPUnit for testing. All tests must pass before submitting a pull request.

```bash
# Run all tests
vendor/bin/phpunit

# Run tests with detailed output
vendor/bin/phpunit --testdox

# Run specific test file
vendor/bin/phpunit tests/ToonEncoderTest.php
```

## Code Quality

### PHP Syntax Check

Before submitting, ensure your code has no syntax errors:

```bash
php -l src/*.php
php -l tests/*.php
```

### Coding Standards

- Follow PSR-4 autoloading standard
- Use meaningful variable and method names
- Add PHPDoc comments for all public methods
- Keep methods focused and single-purpose

## Writing Tests

All new features must include tests. Tests should:

- Cover both success and error cases
- Use descriptive test method names (e.g., `testEncodeStringWithEscaping`)
- Include edge cases
- Be independent and not rely on execution order

Example test:

```php
public function testNewFeature(): void
{
    $encoder = new ToonEncoder();
    $result = $encoder->encode(['test' => 'value']);
    $this->assertEquals("['test':'value']", $result);
}
```

## Pull Request Process

1. Update the README.md with details of changes if applicable
2. Add tests for any new functionality
3. Ensure all tests pass
4. Update documentation if needed
5. Submit your pull request with a clear description

### PR Description Should Include

- What problem does this solve?
- How does it solve it?
- Any breaking changes?
- Screenshots (if applicable)

## Reporting Bugs

When reporting bugs, please include:

- PHP version
- Operating system
- Steps to reproduce
- Expected behavior
- Actual behavior
- Code sample demonstrating the issue

## Feature Requests

Feature requests are welcome! Please:

- Explain the use case
- Describe the expected behavior
- Provide examples if possible
- Explain how it benefits other users

## Code of Conduct

- Be respectful and inclusive
- Accept constructive criticism gracefully
- Focus on what is best for the community
- Show empathy towards other community members

## Questions?

If you have questions about contributing, feel free to:

- Open an issue for discussion
- Reach out to the maintainers

Thank you for contributing to Toonify!
