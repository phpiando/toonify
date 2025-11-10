# Contributing to TOON-PHP

Thank you for considering contributing to TOON-PHP! 🎉

## How to Contribute

### Reporting Bugs

If you found a bug, please open an issue including:

- Clear description of the problem
- Steps to reproduce
- Expected vs actual behavior
- PHP version and library version
- Example code (if possible)

### Suggesting Improvements

Suggestions are welcome! Open an issue describing:

- What you would like to see
- Why it would be useful
- Usage examples

### Pull Requests

1. **Fork the repository**

2. **Clone your fork**
   ```bash
   git clone https://github.com/phpiando/toonify.git
   cd toonify
   ```

3. **Install dependencies**
   ```bash
   composer install
   ```

4. **Create a branch**
   ```bash
   git checkout -b feature/my-feature
   ```

5. **Make your changes**
   - Follow existing code style
   - Add tests for new functionality
   - Update documentation if necessary

6. **Run tests**
   ```bash
   composer test
   ```

7. **Run static analysis**
   ```bash
   composer analyse
   ```

8. **Run linter**
   ```bash
   composer cs-fix
   ```

9. **Commit your changes**
   ```bash
   git commit -am 'Add new feature'
   ```

10. **Push to your branch**
    ```bash
    git push origin feature/my-feature
    ```

11. **Open a Pull Request**

## Code Standards

### PHP

- Use PHP 8.3+ with strict types (`declare(strict_types=1)`)
- Follow PSR-12 for code style
- Use type hints whenever possible
- Document public methods with PHPDoc

### Tests

- Write tests for all new functionality
- Keep test coverage high
- Use descriptive test names
- Organize tests by functionality

### Commits

Use descriptive commit messages:

```
type: short description

More detailed description of what was changed and why.
```

Common types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `test`: Adding or modifying tests
- `refactor`: Code refactoring
- `style`: Formatting changes
- `perf`: Performance improvements

## Project Structure

```
toonify/
├── src/
│   ├── Toonify.php              # Main class (facade)
│   ├── Encoder/
│   │   └── Encoder.php
│   ├── Decoder/
│   │   └── Decoder.php
│   ├── Converter/
│   │   ├── JsonToToonConverter.php
│   │   └── ToonToJsonConverter.php
│   ├── Exception/
│   │   └── EncodingException.php
│   │   └── DecodingException.php
│   │   └── FileException.php
│   └── Util/
│       └── StringUtil.php
├── tests/
│   ├── EncoderTest.php
│   ├── DecoderTest.php
│   └── MarkdownExtractionTest.php
└── examples/
    ├── basic.php
    ├── files.php
    └── markdown.php
```

## Review Process

All PRs will go through:

1. Code review
2. Test verification
3. Style check
4. Static analysis
5. Integration testing

## Questions?

If you have questions, feel free to:

- Open a discussion issue
- Comment on an existing PR
- Contact the maintainers

## Code of Conduct

Be respectful and constructive. We want this to be a welcoming environment for everyone.

## License

By contributing, you agree that your contributions will be licensed under the same MIT license as the project.

---

Thank you again for contributing! 🚀
