# Testing

Testing guidelines for PHP K8s development.

## Running Tests

```bash
# All tests
vendor/bin/phpunit

# Unit tests only (no cluster required)
vendor/bin/phpunit --filter Test$

# Specific test file
vendor/bin/phpunit tests/PodTest.php

# Integration tests (requires cluster)
CI=true vendor/bin/phpunit
```

## Test Structure

Tests follow this pattern:

```php
public function test_resource_api_interaction()
{
    $this->runCreationTests();
    $this->runGetAllTests();
    $this->runGetTests();
    $this->runUpdateTests();
    $this->runDeletionTests();
}
```

## Writing Tests

See existing test files in `tests/` directory for examples.

## See Also

- [Development Setup](/development/contributing/setup) - Setting up dev environment

---

*Testing documentation for cuppett/php-k8s fork*
