# Model & Attribute Security

TrinaCrud provides a sophisticated security system that operates at two levels:

1. **Model Level**: Controls who can perform CRUD operations on entire models
2. **Attribute Level**: Controls which specific attributes a user can view or modify

This granular approach ensures that users only access the data they're authorized to work with.

## The Authorization Service Interface

At the core of TrinaCrud's security system is the `AuthorizationServiceInterface`. This interface defines methods for checking permissions at both model and attribute levels.

The main methods used for permission checking are:

```php
// Check if the current authenticated user has permission to perform an action on a model
public function authHasModelPermission(string $modelName, CrudAction $action): bool;

// Check if the current authenticated user has permission to access/modify an attribute
public function authHasAttributePermission(string $modelName, string $attribute, CrudAction $action): bool;
```

These methods are used throughout TrinaCrud to enforce security, particularly in the `HasCrud` trait:

```php
// From the HasCrud trait
public function getCrudFillable(CrudAction $action): array
{
    $authorizationService = app(AuthorizationServiceInterface::class);

    $fillable = $this->getFillable();
    $filteredFillable = [];
    foreach ($fillable as $field) {
        if ($authorizationService->authHasAttributePermission(get_class($this), $field, $action)) {
            $filteredFillable[] = $field;
        }
    }

    return $filteredFillable;
}
```

## Permission Naming with CrudAction

The `CrudAction` enum is used to generate permission names in a consistent format. It provides two key methods:

```php
// Generate a model-level permission string
public function toModelPermissionString(string $modelName): string
{
    return $this->value . ' ' . str_replace("\\", ".", $modelName);
}

// Generate an attribute-level permission string
public function toAttributePermissionString(string $modelName, string $attribute): string
{
    return $this->value . ' ' . str_replace("\\", ".", $modelName) . ' ' . $attribute;
}
```

For example:

- `CrudAction::READ->toModelPermissionString('App\\Models\\Product')` produces `"read App.Models.Product"`
- `CrudAction::UPDATE->toAttributePermissionString('App\\Models\\Product', 'price')` produces `"update App.Models.Product price"`

## Using the Authorization Service

You should never directly reference Spatie Permission or any other specific implementation in your code. Instead, always use the Authorization Service through dependency injection or the application container:

```php
// Get the authorization service through the container
$authService = app(AuthorizationServiceInterface::class);

// Check if the current user has permission to view products
if ($authService->authHasModelPermission(Product::class, CrudAction::READ)) {
    // User can view products
}

// Check if the current user has permission to update the price attribute
if ($authService->authHasAttributePermission(Product::class, 'price', CrudAction::UPDATE)) {
    // User can update the price
}
```

## How Permissions Are Checked

When a CRUD operation is performed, TrinaCrud checks permissions in this order:

1. **Model Permission**: Does the user have permission for the action on the model?
2. **Attribute Permissions**: For each attribute, does the user have permission to access/modify it?
3. **Ownership** (optional): Is the user the owner of the resource? (when using the Ownable trait)

Only if all applicable permissions pass will the operation succeed.

## Spatie Permission Integration

By default, TrinaCrud includes a Spatie Permission implementation of the Authorization Service interface. This is just one possible implementation - you can create your own without using Spatie if needed.

The Spatie implementation translates the permission strings from `CrudAction` into Spatie's permission system and checks them against the current user's permissions.

## Custom Authorization Service

If you don't want to use Spatie Permission, you can create your own authorization service by implementing the `AuthorizationServiceInterface`:

```php
<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Enums\CrudAction;

class CustomAuthorizationService implements AuthorizationServiceInterface
{
    // Implement the required interface methods
    public function authHasModelPermission(string $modelName, CrudAction $action): bool
    {
        // Your custom logic to check model permissions
        // Use $action->toModelPermissionString($modelName) to get the standard permission string
        $permissionString = $action->toModelPermissionString($modelName);
        
        // Implement your permission check logic
        return true; // Placeholder
    }

    public function authHasAttributePermission(string $modelName, string $attribute, CrudAction $action): bool
    {
        // Your custom logic to check attribute permissions
        // Use $action->toAttributePermissionString($modelName, $attribute) to get the standard permission string
        $permissionString = $action->toAttributePermissionString($modelName, $attribute);
        
        // Implement your permission check logic
        return true; // Placeholder
    }
    
    // Implement the remaining interface methods
    // ...
}
```

Then bind your implementation in a service provider:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use App\Services\CustomAuthorizationService;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(AuthorizationServiceInterface::class, CustomAuthorizationService::class);
    }
}
```

## Examples

### Example 1: Limited Access for Regular Users

A regular user might have:

- Permission to view products (`read App.Models.Product`)
- Permission to see product names and descriptions (`read App.Models.Product name`, `read App.Models.Product description`)
- But no permission to see product costs (`read App.Models.Product cost`)

When this user requests a product, they'll see:

```json
{
    "id": 1,
    "name": "Product Name",
    "description": "Product description here"
}
```

The `cost` field is automatically filtered out.

### Example 2: Different Create and Update Permissions

An inventory manager might have:

- Permission to create products (`create App.Models.Product`)
- Permission to update only certain fields (`update App.Models.Product stock`, `update App.Models.Product location`)
- But not permission to update pricing (`update App.Models.Product price`)

When they try to update a product with:

```json
{
    "stock": 100,
    "location": "Warehouse A",
    "price": 29.99
}
```

Only the `stock` and `location` fields will be updated; the `price` field will be ignored.

## Using Ownership for Security

When you use the `Ownable` trait together with `HasCrud`, the authorization system will also consider ownership:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Traits\HasCrud;
use Trinavo\Ownable\Traits\Ownable;

class Document extends Model
{
    use HasCrud;
    use Ownable;
    
    protected $fillable = ['title', 'content', 'user_id'];
}
```

Now, even if a user has the appropriate permissions, they may only be allowed to update documents they own.

## Best Practices

1. **Be Specific**: Create granular permissions for sensitive attributes
2. **Use Roles**: Group permissions into roles for easier management
3. **Consider Ownership**: Use the `Ownable` trait for user-specific data
4. **Consistent Permissions**: Always use the `CrudAction` enum to generate permission names
5. **Interface, Not Implementation**: Always use the `AuthorizationServiceInterface` rather than directly referencing Spatie or other implementations

## Next Steps

- Learn about [API endpoints](api-endpoints.md) that leverage this security system
- Explore the [Admin Permissions UI](admin-ui.md) for managing permissions visually
