# Installation Guide

This guide will walk you through the process of installing and setting up Laravel TrinaCrud in your project.

## Requirements

- PHP 8.1 or higher
- Laravel 11.0 or higher
- Composer

## Step 1: Install the Package

You can install the package via Composer:

```bash
composer require trinavo/laravel-trina-crud
```

## Step 2: Publish the Configuration

Publish the configuration file to customize TrinaCrud's behavior:

```bash
php artisan vendor:publish --provider="Trinavo\TrinaCrud\Providers\TrinaCrudServiceProvider" --tag="config"
```

This will create a `config/trina-crud.php` file that you can modify according to your needs.

## Step 3: Install Required Dependencies

### Spatie Laravel Permission

TrinaCrud relies on the Spatie Laravel Permission package for authorization. Install it with:

```bash
composer require spatie/laravel-permission
```

Then publish its configuration and run the migrations:

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

For detailed configuration of Spatie Laravel Permission, refer to their [official documentation](https://spatie.be/docs/laravel-permission).

### Laravel Ownable (Optional but Recommended)

For user-based data access control, install the Laravel Ownable package:

```bash
composer require trinavo/laravel-ownable
```

After installation, run the migrations to set up the necessary tables:

```bash
php artisan migrate
```

To use ownership functionality with your models, add the `Ownable` trait:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Trinavo\Ownable\Traits\Ownable;
use Trinavo\TrinaCrud\Traits\HasCrud;

class Product extends Model
{
    use HasCrud;
    use Ownable;
    
    protected $fillable = ['name', 'price', 'description'];
}
```

## Step 4: Configure Routes

TrinaCrud will automatically register routes for your CRUD operations. By default, these routes are prefixed with `api/trina-crud`.

You can customize the route prefix in your `.env` file:

```plaintext
TRINA_CRUD_ROUTE_PREFIX=api/v1
```

Or directly in the `config/trina-crud.php` file:

```php
'route_prefix' => env('TRINA_CRUD_ROUTE_PREFIX', 'api/v1'),
```

## Step 5: Configure Middleware

TrinaCrud uses two middleware groups:

1. `api_middleware`: Applied to all API routes
2. `admin_middleware`: Applied to administrative routes (like permission management)

Configure these in your `config/trina-crud.php` file:

```php
'api_middleware' => ['api', 'auth:sanctum'],
'admin_middleware' => ['web', 'auth', 'role:admin'],
```

## Step 6: Set Up Authorization Service

TrinaCrud uses an authorization service to check permissions. The default implementation is based on Spatie Laravel Permission.

If you need to customize authorization logic, you can extend the `AuthorizationService` class and bind your implementation in a service provider:

```php
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use App\Services\CustomAuthorizationService;

$this->app->bind(AuthorizationServiceInterface::class, CustomAuthorizationService::class);
```

## Step 7: Run Database Migrations

TrinaCrud comes with migrations for permission-related tables. Run the migrations with:

```bash
php artisan migrate
```

## Step 8: Add the HasCrud Trait to Your Models

To enable CRUD operations for a model, add the `HasCrud` trait:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Traits\HasCrud;

class Product extends Model
{
    use HasCrud;
    
    protected $fillable = ['name', 'price', 'description'];
}
```

## Verification

To verify your installation is working correctly, you can try accessing one of the automatically generated API endpoints:

```plaintext
GET /api/trina-crud/products
```

You should receive a JSON response with your data (if authenticated and authorized).

## Next Steps

Now that you've installed TrinaCrud, you may want to:

- Review the [Quick Start Tutorial](quick-start.md) for a complete example
- Learn about [Configuration Options](configuration.md) to customize TrinaCrud
- Understand how [The HasCrud Trait](has-crud-trait.md) works
- Set up the [Admin Permissions UI](admin-ui.md) for managing permissions

## Troubleshooting

If you encounter any issues during installation:

1. Make sure all dependencies are correctly installed
2. Check that migrations have run successfully
3. Verify that your model has the correct fillable attributes defined
4. Ensure your user has the appropriate permissions

For more detailed troubleshooting, see our [Troubleshooting & FAQ](troubleshooting.md) page.
