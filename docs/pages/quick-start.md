# Quick Start Tutorial

This tutorial will guide you through setting up a complete CRUD API with Laravel TrinaCrud in just a few minutes.

## Prerequisites

Before you begin, make sure you have:

- A Laravel 11.0+ project set up
- Basic understanding of Laravel and its command line interface
- Composer installed on your system

## Step 1: Install TrinaCrud

First, install the TrinaCrud package using Composer:

```bash
composer require trinavo/laravel-trina-crud
```

## Step 2: Install Required Dependencies

TrinaCrud requires the following packages by default:

```bash
composer require spatie/laravel-permission
```

Laravel Ownable is also required as it's used in the default configuration for user-based ownership:

```bash
composer require trinavo/laravel-ownable
```

> **Note:** Both packages are essential for TrinaCrud to work with its default configuration. The Spatie Laravel Permission package handles authorization, while Laravel Ownable manages the ownership of resources.

## Step 3: Publish Configuration

Publish the TrinaCrud configuration file:

```bash
php artisan vendor:publish --provider="Trinavo\TrinaCrud\Providers\TrinaCrudServiceProvider" --tag="config"
```

And Spatie Permission migrations:

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

## Step 4: Run Migrations

Run migrations to create the necessary database tables:

```bash
php artisan migrate
```

## Step 5: Create a Model

Let's create a simple Product model for demonstration:

```bash
php artisan make:model Product -m
```

This will create a model and a migration file. We'll configure both in the next step.

## Step 6: Add the HasCrud and Ownable Traits

Edit your Product model to use the HasCrud and Ownable traits:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Traits\HasCrud;
use Trinavo\Ownable\Traits\Ownable;

class Product extends Model
{
    use HasCrud;
    use Ownable;
    
    protected $fillable = [
        'name',
        'price',
        'description',
        'is_active'
    ];
}
```

Create your product migration file with the basic fields:

```php
// database/migrations/xxxx_xx_xx_create_products_table.php
public function up()
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->decimal('price', 10, 2);
        $table->text('description')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}
```

Now run the migrations:

```bash
php artisan migrate
```

> **Note:** The Ownable package handles ownership through a many-to-many relationship with separate ownership tables. You don't need to modify your model's table structure at all.

## Step 7: Set Up Permissions

Create basic permissions for your model by seeding them into the database. Create a seeder:

```bash
php artisan make:seeder PermissionSeeder
```

Then add the following to the seeder:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Create permissions for Product model
        $permissions = [
            'read App.Models.Product',
            'create App.Models.Product',
            'update App.Models.Product',
            'delete App.Models.Product',
            
            // Attribute-level permissions
            'read App.Models.Product name',
            'read App.Models.Product price',
            'read App.Models.Product description',
            'read App.Models.Product is_active',
            
            'create App.Models.Product name',
            'create App.Models.Product price',
            'create App.Models.Product description',
            'create App.Models.Product is_active',
            
            'update App.Models.Product name',
            'update App.Models.Product price',
            'update App.Models.Product description',
            'update App.Models.Product is_active',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create admin role and assign all permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo($permissions);
    }
}
```

Update your DatabaseSeeder.php to call this seeder:

```php
public function run()
{
    $this->call(PermissionSeeder::class);
}
```

Run the seeder:

```bash
php artisan db:seed
```

## Step 8: Assign Permissions to a User

Let's assign the admin role to a user. You can do this in tinker:

```bash
php artisan tinker
```

```php
$user = \App\Models\User::find(1); // Replace 1 with your user's ID
$user->assignRole('admin');
```

## Step 9: Test Your API

At this point, TrinaCrud has already registered the API routes for you. You can test them using a tool like Postman or curl.

Available endpoints:

- `GET /api/trina-crud/products` - List all products
- `GET /api/trina-crud/products/{id}` - Get a specific product
- `POST /api/trina-crud/products` - Create a new product
- `PUT /api/trina-crud/products/{id}` - Update a product
- `DELETE /api/trina-crud/products/{id}` - Delete a product
- `GET /api/trina-crud/products/get-schema` - Get schema information

Let's create a product:

```bash
curl -X POST http://your-app.test/api/trina-crud/products \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "name": "Sample Product",
    "price": 19.99,
    "description": "This is a sample product description",
    "is_active": true
  }'
```

## Step 10: Try Out Schema Endpoint

TrinaCrud provides a schema endpoint that returns information about the model's fields based on your permissions:

```bash
curl -X GET http://your-app.test/api/trina-crud/products/get-schema \
  -H "Authorization: Bearer YOUR_TOKEN"
```

You should see something like:

```json
{
    "model": "App.Models.Product",
    "fields": [
        "id",
        "name",
        "price",
        "description",
        "is_active",
        "created_at",
        "updated_at"
    ]
}
```

## User Ownership (Already Configured)

Since TrinaCrud uses the Laravel Ownable package by default, user ownership functionality is already set up. The Ownable package provides these key features:

- Automatically assigns the authenticated user as an owner when a model is created
- Allows models to have multiple owners (users, teams, etc.)
- Implements convenient methods like `isOwnedBy()`, `addOwner()`, and `removeOwner()`
- Includes helpful query scopes like `ownedBy()` and `mine()`

All of this is handled through separate database tables created by the Ownable package when you run migrations. You don't need to add any columns to your model's table.

With the Ownable trait added to your model in Step 6, TrinaCrud will automatically:

- Assign ownership when resources are created
- Filter queries so users only see resources they own
- Handle all ownership-related logic behind the scenes

## What's Next?

Now that you have a basic CRUD API set up, you might want to:

- Customize validation rules for your models
- Add more complex permission rules
- Create custom controller methods
- Explore response formatting options

Check out the following documentation pages for more information:

- [Configuration Options](configuration.md)
- [The HasCrud Trait](has-crud-trait.md)
- [Model & Attribute Security](model-attribute-security.md)
- [API Endpoints](api-endpoints.md)
- [Advanced Usage](advanced-usage.md)
