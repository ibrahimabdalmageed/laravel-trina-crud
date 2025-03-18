# TrinaCrud

A Laravel package for scanning models, storing model and column information, providing an API to return model info, and supporting CRUD operations with advanced permission handling.

## Installation

```bash
composer require trinavo/flexi-crud
```

## Features

- Automatic model and column scanning
- API for model information
- CRUD operations with permission checks
- Model-level permissions
- Column-level permissions
- Relationship loading with permission checks
- Record ownership filtering
- Advanced query filtering

## Authorization System

TrinaCrud comes with a powerful authorization system that integrates with Laravel's native Gate system. It provides:

### Model-Level Permissions

Control access to entire models:

```php
// In your AuthServiceProvider
Gate::define('view-User', function ($user) {
    return $user->isAdmin();
});

Gate::define('create-User', function ($user) {
    return $user->isAdmin();
});
```

### Column-Level Permissions

Control access to specific columns:

```php
// In your AuthServiceProvider
Gate::define('view-User-email', function ($user) {
    return $user->isAdmin();
});

Gate::define('update-User-email', function ($user) {
    return $user->isAdmin();
});
```

### Record Ownership

Models using the `Ownable` trait will automatically filter records based on ownership:

```php
use Trinavo\TrinaCrud\Traits\Ownable;

class Post extends Model
{
    use Ownable;
    
    // ...
}
```

## API Usage

### Fetching Records

```
GET /api/flexi-crud/models?model=User
```

Parameters:

- `model`: The model name (required)
- `columns`: Array of columns to select
- `with`: Relationships to load (comma-separated or array)
- `relation_columns`: Columns to select for each relationship
- `filters`: Query filters
- `per_page`: Number of records per page

### Creating Records

```
POST /api/flexi-crud/models
```

Parameters:

- `model`: The model name (required)
- Other fields will be filtered based on column permissions

## Advanced Filtering

TrinaCrud supports advanced filtering with various operators:

```
GET /api/flexi-crud/models?model=User&filters[age][operator]=between&filters[age][value][]=18&filters[age][value][]=65
```

Supported operators:

- `between`: Value must be an array with two elements
- `not_in`: Value must be an array
- `like`: Performs a LIKE query
- `not` or `!=`: Not equal
- `>`, `<`, `>=`, `<=`: Comparison operators

## License

MIT
