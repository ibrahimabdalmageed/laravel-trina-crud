# Laravel Trina Crud

## Introduction

TrinaCrud is a Laravel package for rapid CRUD API generation with built-in authorization and validation.

## Features

- 🚀 **Quick Setup**: Add a simple trait to your models to instantly get CRUD endpoints
- 🔒 **Built-in Security**: Integrated with Spatie Permissions for robust authorization
- 🧩 **Flexible**: Customizable routes, middleware, and validation
- 📱 **API Ready**: Perfect for building backends for SPA and mobile applications
- 🛠️ **Permission Management**: Visual interface for managing roles and permissions
- 📊 **Single Source of Truth**: Your model and database schema drive validation and security
- 🔄 **Auto-Generated Validation**: Rules automatically derived from database schema

## Requirements

- PHP 8.1 or higher
- Laravel 11.0 or higher
- Composer

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
You should publish the migration and the config/permission.php config file with:

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```


Follow the [Spatie Permission installation instructions](https://spatie.be/docs/laravel-permission/v5/installation-laravel).

### 2. Install Ownable Package (Optional but Recommended)

```bash
composer require trinavo/laravel-ownable
```

Run the migrations:

```bash
php artisan migrate
```

### 3. Add the Trait to Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Traits\HasCrud;
use Trinavo\Ownable\Traits\Ownable; // Optional for user-based access control

class Product extends Model
{
    use HasCrud;
    use Ownable; // Optional
    
    // Define which attributes are mass assignable
    protected $fillable = ['name', 'description', 'price'];
}
```

That's it! Your model now has CRUD API endpoints available at:

- `GET /api/{route_prefix}/products` - List all products
- `GET /api/{route_prefix}/products/{id}` - Get a single product
- `POST /api/{route_prefix}/products` - Create a product
- `PUT /api/{route_prefix}/products/{id}` - Update a product
- `DELETE /api/{route_prefix}/products/{id}` - Delete a product
- `GET /api/{route_prefix}/products/schema` - Get model schema information

## The HasCrud Trait

The `HasCrud` trait is a powerful utility that uses your model as the single source of truth:

### Single Source of Truth

The trait utilizes your existing Laravel model definition (including table schema and fillable attributes) to:

- Generate appropriate validation rules automatically
- Apply proper security restrictions based on user permissions
- Handle CRUD operations with minimal configuration

### Automatic Schema-Based Validation

TrinaCrud intelligently generates validation rules based on your database schema:

- Column types are mapped to appropriate Laravel validation rules
- Nullability constraints are respected
- String length limits are applied automatically
- Numeric precision and scale are preserved
- This makes development faster and reduces inconsistencies between your database and validation logic

Example of automatically generated rules for a `products` table:

```php
[
    'name' => 'required|string|max:255',
    'price' => 'required|numeric|decimal:2',
    'description' => 'nullable|string',
    'is_active' => 'boolean',
]
```

### Custom Validation Rules

While automatic rule generation is convenient, you can always override it:

```php
// Override getCrudRules method in your model
public function getCrudRules(\Trinavo\TrinaCrud\Enums\CrudAction $action): array
{
    // Call parent method to get the auto-generated rules
    $rules = parent::getCrudRules($action);
    
    // Add or modify rules as needed
    if ($action === \Trinavo\TrinaCrud\Enums\CrudAction::CREATE) {
        $rules['name'] = 'required|string|max:255|unique:products';
    }
    
    return $rules;
}
```

### Model & Attribute Level Security

TrinaCrud provides fine-grained security control:

- **Model Level**: Control who can create, read, update, or delete entire models
- **Attribute Level**: Control which specific attributes a user can view or modify
- **Action-Specific**: Different permissions for different CRUD operations
- **UI Management**: Permissions can be easily managed through the included admin interface

This granular approach ensures users only see and modify data they're authorized to access.

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
- **Admin Routes**: Administrative functions use stricter middleware
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

## API Documentation with OpenAPI

TrinaCrud automatically generates OpenAPI 3.0 documentation for all your models. This gives you:

- Complete API documentation in standard formats
- Client SDK generation capabilities
- Testing support

### Accessing the Documentation

The OpenAPI documentation is available in two formats:

- JSON format: `/api/{route_prefix}/openapi.json`
- YAML format: `/api/{route_prefix}/openapi.yaml`

Add `?download=true` parameter to download the specification file.

### API Specification Format

The OpenAPI documentation follows standard conventions with:

- Models defined using `$ref: '#/components/schemas/ModelName'` format
- Complete endpoint documentation for each CRUD operation
- Auth requirements and error responses
- Field type detection based on naming conventions

### Client Generation

With the OpenAPI specification, you can generate client libraries for your API in various languages using tools like [OpenAPI Generator](https://github.com/OpenAPITools/openapi-generator).

## Documentation

For detailed documentation, visit the [docs directory](docs/index.md).

## Support the Development

If you find this package helpful, consider buying me a coffee:

[![Buy Me A Coffee](https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png)](https://buymeacoffee.com/doonfrs)

## License

The TrinaCrud package is open-sourced software licensed under the [MIT license](LICENSE.md).
