# The HasCrud Trait

The `HasCrud` trait is the core component of the Laravel TrinaCrud package. By adding this trait to your Eloquent models, you instantly enable automatic CRUD operations, validation, and authorization.

## Overview

The `HasCrud` trait turns your model into a "single source of truth" for your application by:

1. Using your model's database schema to generate validation rules
2. Respecting your model's `fillable` attributes
3. Handling authorization at both model and attribute levels
4. Integrating with Laravel's validation system

## Basic Usage

To add CRUD capabilities to a model, simply use the trait:

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

That's it! This model now has complete CRUD functionality through the API endpoints.

## Key Methods

### `getCrudFillable(CrudAction $action): array`

This method returns a filtered list of fillable attributes based on the user's permissions for a specific CRUD action.

```php
// Inside a controller, you could use:
$fillableFields = $product->getCrudFillable(\Trinavo\TrinaCrud\Enums\CrudAction::CREATE);
```

The method checks each fillable attribute against the user's permissions and only returns those the user is authorized to access.

### `getCrudRules(CrudAction $action): array`

This method generates validation rules based on your database schema and the CRUD action being performed:

```php
// Generate validation rules for the CREATE action
$rules = $product->getCrudRules(\Trinavo\TrinaCrud\Enums\CrudAction::CREATE);
```

The rules are tailored to each action:

- `CREATE`: Full validation for required fields
- `UPDATE`: "sometimes" rules to validate only fields that are present
- `READ`: Typically not used, but available for validation during read operations
- `DELETE`: Typically not used, but available for validation before deletion

## How Rule Generation Works

The `HasCrud` trait analyzes your database schema to generate the appropriate validation rules:

1. It reads all columns from your model's database table
2. For each column, it determines the data type, constraints, and other properties
3. It maps these properties to Laravel validation rules:
   - `integer` columns get the `integer` rule
   - `string`/`varchar` columns get the `string` and appropriate `max:length` rules
   - `decimal` columns get `numeric` and `decimal:scale` rules
   - Nullable columns get the `nullable` rule
   - Non-nullable columns without defaults get the `required` rule (for CREATE)

For example, a schema like this:

```sql
CREATE TABLE products (
    id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    description text NULL,
    price decimal(10, 2) NOT NULL,
    is_active tinyint(1) NOT NULL DEFAULT 1,
    created_at timestamp NULL,
    updated_at timestamp NULL
);
```

Would generate these validation rules for the CREATE action:

```php
[
    'name' => 'required|string|max:255',
    'description' => 'nullable|string',
    'price' => 'required|numeric|decimal:2',
    'is_active' => 'boolean',
]
```

## Customizing Validation Rules

You can override the generated rules by implementing your own `getCrudRules` method:

```php
public function getCrudRules(\Trinavo\TrinaCrud\Enums\CrudAction $action): array
{
    // Get the auto-generated rules first
    $rules = parent::getCrudRules($action);
    
    // Add or modify rules for specific actions
    if ($action === \Trinavo\TrinaCrud\Enums\CrudAction::CREATE) {
        // Add unique constraint for name during creation
        $rules['name'] = 'required|string|max:255|unique:products';
        
        // Add a custom rule for price
        $rules['price'] = 'required|numeric|min:0.01';
    }
    
    return $rules;
}
```

## Working with Authorization

The `HasCrud` trait works with the authorization service to control access to models and attributes. When a CRUD action is performed:

1. The trait checks if the user has permission for the action on the model
2. For each attribute, it checks if the user has permission to access that attribute
3. It filters out unauthorized attributes from both input and output

This authorization happens automatically when you use the provided controllers.

## Integrating with Ownable

For user-based data access control, you can combine `HasCrud` with the `Ownable` trait:

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
    
    protected $fillable = ['name', 'price', 'description'];
}
```

This allows the authorization system to consider ownership when determining access permissions.

## Under the Hood

The `HasCrud` trait uses the following components:

1. **Schema Introspection**: Uses Laravel's Schema facade to read column information
2. **Authorization Service**: Checks permissions via the `AuthorizationServiceInterface`
3. **CrudAction Enum**: Defines the available CRUD actions (CREATE, READ, UPDATE, DELETE)

## Best Practices

- Define your `fillable` attributes carefully
- Let the trait generate validation rules automatically when possible
- Override `getCrudRules` only when you need custom validation
- Use the `Ownable` trait when you need user-based access control

## Next Steps

- Learn how to [customize validation](custom-validation.md)
- Explore [model-level and attribute-level security](model-attribute-security.md)
- See [advanced usage examples](advanced-usage.md)
