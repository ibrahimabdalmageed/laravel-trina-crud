# Laravel Trina Crud

## Introduction

TrinaCrud is a Laravel package for rapid CRUD API generation with built-in authorization and validation.

## Features

- 🚀 **Quick Setup**: Add a simple trait to your models to instantly get CRUD endpoints
- 🔒 **Built-in Security**: Integrated with Spatie Permissions for robust authorization
- 🧩 **Flexible**: Customizable routes, middleware, and validation
- 📱 **API Ready**: Perfect for building backends for SPA and mobile applications
- 🛠️ **Permission Management**: Visual interface for managing roles and permissions

## Installation

```bash
composer require trinavo/laravel-trina-crud
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Trinavo\TrinaCrud\Providers\TrinaCrudServiceProvider" --tag="config"
```

## Quick Start

### 1. Install Spatie Permission Package

TrinaCrud uses Spatie Permission for authorization:

```bash
composer require spatie/laravel-permission
```

Follow the [Spatie Permission installation instructions](https://spatie.be/docs/laravel-permission/v5/installation-laravel).

### 2. Install Ownable Package

```bash
composer require trinavo/laravel-ownable
```

### 3. Add the Trait to Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Traits\HasCrud;

class Product extends Model
{
    use HasCrud;
    
    // Define which attributes are mass assignable
    protected $fillable = ['name', 'description', 'price'];
    
    // Optional: Define validation rules
    public static function validationRules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ];
    }
}
```

That's it! Your model now has CRUD API endpoints available at:

- `GET /api/{route_prefix}/products` - List all products
- `GET /api/{route_prefix}/products/{id}` - Get a single product
- `POST /api/{route_prefix}/products` - Create a product
- `PUT /api/{route_prefix}/products/{id}` - Update a product
- `DELETE /api/{route_prefix}/products/{id}` - Delete a product
- `GET /api/{route_prefix}/products/schema` - Get model schema information

## Permission Management

TrinaCrud comes with a built-in permission management interface that makes it easy to:

1. Create and manage permissions
2. Define roles with specific permissions
3. Assign roles to users
4. Visualize permission matrix

To access the permission management screen, visit:

```plaintext
/admin/permissions
```

![Permission Management Screen](https://via.placeholder.com/800x400?text=Permission+Management+Screenshot)

The permission management system uses Livewire components for a smooth, interactive experience without page reloads.

## Security

TrinaCrud takes security seriously with:

- **Route Protection**: All routes are protected by configurable middleware
- **Admin Routes**: Administrative functions like model synchronization use stricter middleware
- **Customizable Authentication**: Configure authentication requirements through the config file
- **Permission-Based Access**: Granular control over who can perform which operations
- **Data Ownership**: Integration with the Ownable package to restrict access based on ownership

### Security Configuration

You can customize security settings in the `config/trina-crud.php` file:

```php
// Example configuration
'admin_middleware' => ['web', 'auth', 'role:admin'],
'api_middleware' => ['api', 'auth:sanctum'],
'route_prefix' => env('TRINA_CRUD_ROUTE_PREFIX', 'trina-crud'),
```

## Customization

### Route Prefixes

You can customize the route prefix through the config file or environment variables:

```plaintext
TRINA_CRUD_ROUTE_PREFIX=api/v1
```

### Custom Validation

Define custom validation rules directly in your model:

```php
public static function validationRules()
{
    return [
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
    ];
}
```

## Support the Development

If you find this package helpful, consider buying me a coffee:

[![Buy Me A Coffee](https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png)](https://buymeacoffee.com/doonfrs)

## License

The TrinaCrud package is open-sourced software licensed under the [MIT license](LICENSE.md).
