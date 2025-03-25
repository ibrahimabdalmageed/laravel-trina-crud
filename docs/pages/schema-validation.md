# Automatic Schema Validation

One of the most powerful features of Laravel TrinaCrud is its ability to automatically generate validation rules based on your database schema. This feature eliminates the redundancy of defining the same constraints in both your database and your application code.

## How Schema Validation Works

TrinaCrud analyzes your database table structure to generate appropriate Laravel validation rules. This process happens in the `getCrudRules` method of the `HasCrud` trait:

1. It retrieves the table name from your model
2. It gets the column listing and detailed column information using Laravel's Schema facade
3. It analyzes each column's type, constraints, and properties
4. It maps these database properties to equivalent Laravel validation rules
5. It adjusts the rules based on the CRUD action being performed

## Database to Validation Rule Mapping

The following table shows how database column properties are mapped to Laravel validation rules:

| Database Property | Laravel Validation Rule |
|-------------------|-------------------------|
| Column Type: `integer`, `bigint`, `smallint`, `tinyint` | `integer` |
| Column Type: `decimal`, `float`, `double` | `numeric` + `decimal:scale` |
| Column Type: `varchar`, `char`, `text` | `string` + `max:length` (for varchar/char) |
| Column Type: `date` | `date` |
| Column Type: `datetime`, `timestamp` | `date_format:Y-m-d H:i:s` |
| Column Type: `boolean` or `tinyint(1)` | `boolean` |
| Column Type: `json` | `json` |
| Nullability: `NOT NULL` without default | `required` (for CREATE) |
| Nullability: `NULL` or has default | `nullable` |

## Examples

Let's look at some examples of how database schemas translate to validation rules:

### Example 1: Basic Product Table

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

**Generated Validation Rules (CREATE)**:

```php
[
    'name' => 'required|string|max:255',
    'description' => 'nullable|string',
    'price' => 'required|numeric|decimal:2',
    'is_active' => 'boolean',
]
```

**Generated Validation Rules (UPDATE)**:

```php
[
    'name' => 'sometimes|string|max:255',
    'description' => 'sometimes|nullable|string',
    'price' => 'sometimes|numeric|decimal:2',
    'is_active' => 'sometimes|boolean',
]
```

### Example 2: User Table

```sql
CREATE TABLE users (
    id bigint UNSIGNED NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    email varchar(255) NOT NULL,
    email_verified_at timestamp NULL,
    password varchar(255) NOT NULL,
    remember_token varchar(100) NULL,
    created_at timestamp NULL,
    updated_at timestamp NULL
);
```

**Generated Validation Rules (CREATE)**:

```php
[
    'name' => 'required|string|max:255',
    'email' => 'required|string|max:255',
    'email_verified_at' => 'nullable|date_format:Y-m-d H:i:s',
    'password' => 'required|string|max:255',
    'remember_token' => 'nullable|string|max:100',
]
```

## Action-Specific Rules

TrinaCrud adjusts validation rules based on the CRUD action being performed:

### CREATE Action

- Non-nullable fields without defaults are marked as `required`
- Primary keys and auto-increment fields are excluded (they're managed by the database)

### UPDATE Action

- All rules include the `sometimes` modifier to validate only if the field is present
- This allows partial updates without requiring all fields

### READ and DELETE Actions

- These actions typically don't use validation rules
- However, the methods are available if you need to validate certain aspects during these operations

## Benefits of Schema Validation

1. **Single Source of Truth**: Your database schema defines both storage constraints and validation rules
2. **Reduced Redundancy**: No need to duplicate constraints in multiple places
3. **Consistency**: Database and application-level validation are always in sync
4. **Time Saving**: Automatic rule generation for common constraints

## Extending Schema Validation

While automatic validation covers most common scenarios, you can always extend or override it:

```php
public function getCrudRules(\Trinavo\TrinaCrud\Enums\CrudAction $action): array
{
    // Get auto-generated rules
    $rules = parent::getCrudRules($action);
    
    // Add custom rules
    if ($action === \Trinavo\TrinaCrud\Enums\CrudAction::CREATE) {
        // Add unique constraint (not detectable from schema)
        $rules['email'] = 'required|string|max:255|email|unique:users';
        
        // Add password confirmation
        $rules['password'] = 'required|string|min:8|confirmed';
        
        // Add a custom rule using Laravel's Rule class
        $rules['name'] = ['required', 'string', 'max:255', new \App\Rules\NoOffensiveWords];
    }
    
    return $rules;
}
```

## Limitations

Schema-based validation has some limitations:

1. It can't detect unique constraints automatically (you need to add these manually)
2. Complex validation like `confirmed` or `exists` fields need to be added manually
3. Custom application-specific rules need to be added separately

## Best Practices

1. Design your database schema carefully, with validation in mind
2. For common constraints (type, length, nullability), rely on auto-generated rules
3. Override `getCrudRules` to add constraints that can't be detected from the schema
4. Test validation rules thoroughly to ensure they match your application's requirements

## Next Steps

- Learn about [custom validation](custom-validation.md) for complex scenarios
- Explore [advanced usage examples](advanced-usage.md) for more sophisticated validation scenarios
