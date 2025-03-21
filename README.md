# Laravel TrinaCrud

[![Latest Version on Packagist](https://img.shields.io/packagist/v/trinavo/trina-crud.svg?style=flat-square)](https://packagist.org/packages/trinavo/trina-crud)
[![Total Downloads](https://img.shields.io/packagist/dt/trinavo/trina-crud.svg?style=flat-square)](https://packagist.org/packages/trinavo/trina-crud)
[![License](https://img.shields.io/packagist/l/trinavo/trina-crud.svg?style=flat-square)](https://packagist.org/packages/trinavo/trina-crud)

A powerful Laravel package that automatically scans your models, generates API endpoints for CRUD operations, and provides a flexible authorization system - all with minimal configuration.

## Features

- **Automatic Model Discovery**: Simply add a trait to your models and they're ready to go
- **Complete CRUD API**: Ready-to-use RESTful API endpoints for all your models
- **Flexible Authorization**: Integrates with Laravel's authorization system and Spatie Permissions
- **Advanced Filtering**: Powerful query filtering options for your API endpoints
- **Relationship Support**: Load and filter by model relationships
- **Record Ownership**: Control which records users can access based on ownership
- **Highly Configurable**: Customize behavior through simple configuration options

## Installation

### Requirements

- PHP 8.0+
- Laravel 9.0+

### Installation Steps

1. Install the package via Composer:

   ```bash
   composer require trinavo/trina-crud
   ```

2. Publish the configuration file:

   ```bash
   php artisan vendor:publish --provider="Trinavo\TrinaCrud\Providers\TrinaCrudServiceProvider" --tag="config"
   ```

3. Run the migrations:

   ```bash
   php artisan migrate
   ```

## Quick Start

### 1. Add the HasCrud trait to your models

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Traits\HasCrud;

class Product extends Model
{
    use HasCrud;
    
    protected $fillable = [
        'name',
        'price',
        'description',
    ];
}
```

### 2. Scan your models

Run the command to scan and register your models:

```bash
php artisan trinacrud:sync-models
```

### 3. Use the API

Your models are now accessible through the API:

```http
GET /api/trina-crud/model/Product         # List all products
GET /api/trina-crud/model/Product/1       # Get a specific product
POST /api/trina-crud/model/Product        # Create a new product
PUT /api/trina-crud/model/Product/1       # Update a product
DELETE /api/trina-crud/model/Product/1    # Delete a product
```

## Configuration

TrinaCrud is highly configurable through the `config/trina-crud.php` file:

### Authorization

Choose your preferred authorization implementation:

```php
// config/trina-crud.php
'authorization_service' => env('TRINA_CRUD_AUTH_TYPE', 'allow_all'),
```

Options:

- `allow_all`: No authorization checks (useful for development)
- `spatie`: Use Spatie Permission package
- `default`: Use Laravel's native Gate system

### Route Prefix

You can customize the API endpoint prefix:

```php
// config/trina-crud.php
'route_prefix' => env('TRINA_CRUD_PREFIX', 'trina-crud'),
```

This allows you to change the base URL for all TrinaCrud routes. For example, if you set it to `api/crud`, your endpoints would be:

```http
GET /api/crud/model/Product         # Instead of /trina-crud/model/Product
```

### Route Structure

TrinaCrud organizes routes into two groups:

1. **Admin Routes**: Administrative endpoints with stricter security

   ```http
   GET /trina-crud/admin/sync-models  # Scan and register models
   ```

2. **Regular API Routes**: Standard CRUD operations

   ```http
   GET /trina-crud/get-schema
   GET /trina-crud/model/{model}
   GET /trina-crud/model/{model}/{id}
   POST /trina-crud/model/{model}
   PUT /trina-crud/model/{model}/{id}
   DELETE /trina-crud/model/{model}/{id}
   ```

### Route Protection

Protect your API routes with middleware:

```php
// config/trina-crud.php
'middleware' => [
    'api',
    'auth:api',
],

'admin_middleware' => [
    'auth:api',
    'can:manage-trina-crud',
],
```

### Model Scanning

Configure which directories to scan for models:

```php
// config/trina-crud.php
'model_paths' => [
    base_path('app/Models'),
    // Add more paths as needed
],
```

### Record Ownership

Control how record ownership is determined:

```php
// config/trina-crud.php
'ownership_service' => env('TRINA_CRUD_OWNERSHIP_TYPE', 'ownable'),
'ownership_field' => env('TRINA_CRUD_OWNERSHIP_FIELD', 'user_id'),
```

## API Usage

### Listing Records

```http
GET /api/trina-crud/model/Product
```

Optional parameters:

- `columns[]`: Specific columns to retrieve
- `with`: Related models to include (comma-separated or array)
- `relation_columns`: Columns to select for each relationship
- `filters`: Query filters
- `per_page`: Number of records per page

Example with filtering:

```http
GET /api/trina-crud/model/Product?filters[price][operator]=between&filters[price][value][]=10&filters[price][value][]=100
```

### Getting a Single Record

```http
GET /api/trina-crud/model/Product/1
```

Optional parameters:

- `columns[]`: Specific columns to retrieve
- `with`: Related models to include
- `relation_columns`: Columns to select for each relationship

### Creating a Record

```http
POST /api/trina-crud/model/Product
{
  "name": "New Product",
  "price": 99.99,
  "description": "Product description"
}
```

### Updating a Record

```http
PUT /api/trina-crud/model/Product/1
{
  "price": 89.99
}
```

### Deleting a Record

```http
DELETE /api/trina-crud/model/Product/1
```

## Advanced Features

### Advanced Filtering

TrinaCrud supports advanced filtering with various operators:

```http
GET /api/trina-crud/model/Product?filters[price][operator]=between&filters[price][value][]=10&filters[price][value][]=100
```

Supported operators:

- `between`: Value must be an array with two elements
- `not_in`: Value must be an array
- `like`: Performs a LIKE query
- `not` or `!=`: Not equal
- `>`, `<`, `>=`, `<=`: Comparison operators

### Relationship Loading

Load related models with your queries:

```http
GET /api/trina-crud/model/Product?with=category,tags&relation_columns[category][]=name
```

### Custom Pagination

Control the number of records per page:

```http
GET /api/trina-crud/model/Product?per_page=50
```

## Security

### Model-Level Permissions

Control access to entire models:

```php
// In your AuthServiceProvider
Gate::define('view-Product', function ($user) {
    return $user->isAdmin();
});

Gate::define('create-Product', function ($user) {
    return $user->isAdmin();
});
```

### Column-Level Permissions

Control access to specific columns:

```php
// In your AuthServiceProvider
Gate::define('view-Product-price', function ($user) {
    return $user->isAdmin();
});

Gate::define('update-Product-price', function ($user) {
    return $user->isAdmin();
});
```

### Using Spatie Permissions

If you're using the Spatie Permission package:

```php
// config/trina-crud.php
'authorization_service' => 'spatie',
'admin_permission' => 'manage-trina-crud',
```

Then create the permission in your application:

```php
// In a seeder or elsewhere
Permission::create(['name' => 'manage-trina-crud']);
```

### Protecting Administrative Routes

The `/sync-models` route is protected with the `trina-crud.admin` middleware, which ensures only authorized users can scan and update model metadata. You can configure this protection in the config file:

```php
// config/trina-crud.php
'admin_middleware' => [
    'auth:api',
    'can:manage-trina-crud',
],
```

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security related issues, please email [security@trinavo.com](mailto:security@trinavo.com) instead of using the issue tracker.

## Credits

- [Trinavo Team](https://github.com/trinavo)
- [All Contributors](https://github.com/trinavo/trina-crud/graphs/contributors)

## License

The MIT License (MIT). Please see [LICENSE.md](LICENSE.md) for more information.
