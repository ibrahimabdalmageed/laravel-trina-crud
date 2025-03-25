# Advanced Usage

This guide covers advanced features and customization options for Laravel TrinaCrud. These features are optional and not required for basic usage, but they provide additional flexibility when you need it.

## Custom Service Implementations

### Creating a Custom Authorization Service

For applications with complex permission requirements, you may want to implement your own authorization logic:

```php
<?php

namespace App\Services;

use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Enums\CrudAction;

class CustomAuthorizationService implements AuthorizationServiceInterface
{
    public function authModelPermission(string $modelClass, CrudAction $action): bool
    {
        // Example: Integrate with a custom ACL system
        $user = auth()->user();
        $modelName = class_basename($modelClass);
        
        return $this->checkUserPermission($user, $modelName, $action->value);
    }

    public function authHasAttributePermission(string $modelClass, string $attribute, CrudAction $action): bool
    {
        // Example: Field-level permissions
        $user = auth()->user();
        $modelName = class_basename($modelClass);
        
        return $this->checkUserAttributePermission($user, $modelName, $attribute, $action->value);
    }
    
    private function checkUserPermission($user, $model, $action)
    {
        // Your custom permission checking logic
        return true; // Placeholder
    }
    
    private function checkUserAttributePermission($user, $model, $attribute, $action)
    {
        // Your custom attribute permission checking logic
        return true; // Placeholder
    }
}
```

### Custom Ownership Logic

If your application has complex ownership rules, you can implement a custom ownership service:

```php
<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Contracts\OwnershipServiceInterface;

class TeamOwnershipService implements OwnershipServiceInterface
{
    public function isOwner(Model $model): bool
    {
        $user = auth()->user();
        
        // Example: Check if user belongs to the team that owns this resource
        return $model->team_id === $user->team_id;
    }

    public function setOwner(Model $model): void
    {
        $user = auth()->user();
        
        // Example: Set the owner to the user's team
        $model->team_id = $user->team_id;
    }
}
```

## Custom Validation Rules

For more complex validation requirements, you can override the `getCrudRules` method in your model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Traits\HasCrud;
use Trinavo\TrinaCrud\Enums\CrudAction;

class Product extends Model
{
    use HasCrud;
    
    protected $fillable = ['name', 'price', 'category_id', 'is_featured'];
    
    public function getCrudRules(CrudAction $action): array
    {
        // Get the auto-generated rules first
        $rules = parent::getCrudRules($action);
        
        // Add custom rules based on the action
        if ($action === CrudAction::CREATE) {
            $rules['name'] = 'required|string|max:255|unique:products';
            $rules['category_id'] = 'required|exists:categories,id';
        } elseif ($action === CrudAction::UPDATE) {
            $rules['name'] = 'sometimes|string|max:255|unique:products,name,' . $this->id;
            $rules['category_id'] = 'sometimes|exists:categories,id';
        }
        
        // Add conditional validation
        if (request()->has('is_featured') && request()->input('is_featured')) {
            $rules['price'] = 'required|numeric|min:100'; // Featured products must cost at least $100
        }
        
        return $rules;
    }
}
```

## Custom Controller Logic

### Extending the Base Controller

You can extend the base controller to add custom behavior:

```php
<?php

namespace App\Http\Controllers\Api;

use Trinavo\TrinaCrud\Http\Controllers\ApiController;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    protected $model = Product::class;
    
    // Override the store method to add custom logic
    public function store(Request $request)
    {
        // Custom pre-processing
        $data = $request->all();
        $data['slug'] = \Str::slug($data['name']);
        
        // Call the parent method with the modified data
        $request->replace($data);
        $result = parent::store($request);
        
        // Custom post-processing
        // For example, send a notification, update analytics, etc.
        
        return $result;
    }
    
    // Add a custom endpoint
    public function featured()
    {
        $products = Product::where('is_featured', true)->paginate();
        return $this->respondWithCollection($products);
    }
}
```

Then in your routes file:

```php
Route::get('products/featured', [ProductController::class, 'featured']);
```

## Working with Relationships

### Including Relationships in Responses

You can customize which relationships are included in API responses:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Traits\HasCrud;

class Product extends Model
{
    use HasCrud;
    
    protected $fillable = ['name', 'price', 'category_id'];
    
    // Define relationships to auto-load
    protected $with = ['category'];
    
    // Define which relationships can be requested via API
    protected $allowedRelationships = ['category', 'reviews', 'tags'];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
```

## Custom Response Transformations

For advanced response formatting:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Traits\HasCrud;

class Product extends Model
{
    use HasCrud;
    
    protected $fillable = ['name', 'price', 'description'];
    
    public function transformForApi(array $data): array
    {
        // Add computed fields
        $data['price_with_tax'] = $data['price'] * 1.1;
        
        // Format existing fields
        $data['formatted_price'] = '$' . number_format($data['price'], 2);
        
        // Remove sensitive fields
        unset($data['internal_code']);
        
        return $data;
    }
}
```

## Performance Optimization

For large datasets, you can implement custom query optimizations:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Traits\HasCrud;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasCrud;
    
    protected $fillable = ['name', 'price', 'category_id', 'is_active'];
    
    public function scopeCrudQuery(Builder $query)
    {
        // Default scope applied to all CRUD operations
        return $query->where('is_active', true);
    }
    
    public function scopeCrudIndexQuery(Builder $query)
    {
        // Only applied to the index (list) endpoint
        return $query->select(['id', 'name', 'price', 'category_id'])
                    ->with('category:id,name')
                    ->withCount('reviews');
    }
}
```

## Remember

These advanced features are entirely optional. The beauty of TrinaCrud is that you can start with the basic implementation and gradually add customizations as your application grows. You don't need to implement any of these advanced features to get started with TrinaCrud.
