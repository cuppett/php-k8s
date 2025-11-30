# Contributing

Thank you for considering contributing to PHP K8s!

## Getting Started

1. Fork the repository
2. Clone your fork
3. Set up development environment (see [Development Setup](/development/setup))
4. Create a feature branch
5. Make your changes
6. Write tests
7. Submit a pull request

## Development Workflow

```bash
# Clone your fork
git clone https://github.com/YOUR_USERNAME/php-k8s.git
cd php-k8s

# Install dependencies
composer install

# Run tests
vendor/bin/phpunit

# Run static analysis
vendor/bin/psalm
```

## Code Style

- Follow PSR-12
- Use PHP 8.2+ features (enums, type hints, match expressions)
- Add type hints to all parameters and return types
- Write tests for new features

## Pull Request Process

1. Update documentation
2. Add tests
3. Run tests and static analysis
4. Commit with descriptive message
5. Push to your fork
6. Create pull request to `main` branch

## Reporting Issues

- Search existing issues first
- Provide PHP version, package version
- Include minimal code example
- Include full error message

## See Also

- [Development Setup](/development/setup) - Set up dev environment
- [Testing](/development/testing) - Testing guidelines

---

*Contributing guide for cuppett/php-k8s fork*
