# TrinaCrud Tests

This directory contains tests for the TrinaCrud package.

## Requirements

- PHP 8.2+
- Composer
- Laravel 12.0+

## Installation

Make sure you have installed the package dependencies:

```bash
composer install
```

## Running Tests

To run all tests:

```bash
./vendor/bin/pest
```

To run a specific test file:

```bash
./vendor/bin/pest tests/Unit/Services/AuthorizationServiceTest.php
```

To run tests with coverage report:

```bash
./vendor/bin/pest --coverage
```

## Test Structure

- `Unit/`: Contains unit tests for individual components
  - `Services/`: Tests for service classes
- `Feature/`: Contains feature tests that test multiple components together
  - `Http/Controllers/`: Tests for controllers
  - `Http/Requests/`: Tests for form requests

## Mocking

The tests use Mockery for mocking dependencies. Here's an example:

```php
$authService = Mockery::mock(AuthorizationService::class);
$authService->shouldReceive('hasModelPermission')
    ->with('User', 'view')
    ->andReturn(true);
```

## Assertions

The tests use Pest's expectations for assertions. Here's an example:

```php
expect($result)->toBeTrue();
expect($response)->toBeInstanceOf('Illuminate\Support\Collection');
expect($rules)->toHaveKey('model');
```

## Notes on Test Implementation

1. The tests are designed to be isolated and not depend on a database.
2. We use mocks to avoid hitting the database or external services.
3. The tests focus on the authorization system's behavior.
4. We test both the happy path and error cases.

## Troubleshooting

If you encounter issues with the tests:

1. Make sure you have the correct PHP version (8.2+)
2. Ensure all dependencies are installed
3. Check that the package is properly autoloaded
4. Verify that the test database is configured correctly
