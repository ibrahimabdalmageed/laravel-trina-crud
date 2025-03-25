# API Endpoints

Laravel TrinaCrud automatically generates a complete set of RESTful API endpoints for models that use the `HasCrud` trait. This document explains the available endpoints, their parameters, and how to use them.

## Endpoint Structure

TrinaCrud creates API endpoints following REST conventions. For a model like `Product`, the following endpoints are generated:

| HTTP Method | Endpoint | Action | Description |
|-------------|----------|--------|-------------|
| GET | `/api/{prefix}/products` | Index | List all products |
| GET | `/api/{prefix}/products/{id}` | Show | Get a single product |
| POST | `/api/{prefix}/products` | Store | Create a new product |
| PUT/PATCH | `/api/{prefix}/products/{id}` | Update | Update a product |
| DELETE | `/api/{prefix}/products/{id}` | Destroy | Delete a product |
| GET | `/api/{prefix}/products/get-schema` | Schema | Get model schema information |
| GET | `/api/{prefix}/get-schema` | All Schemas | Get schema information for all models |

Where `{prefix}` is the route prefix defined in your configuration (default: `trina-crud`).

## Endpoint Details

### List Resources (Index)

```http
GET /api/{prefix}/{resource}
```

**Request Parameters:**

- `page` (optional): Page number for pagination
- `per_page` (optional): Number of items per page
- `sort` (optional): Field to sort by, prefix with `-` for descending order
- `filter[field]` (optional): Filter by field value
- `search` (optional): Search term to look for across searchable fields
- `with` (optional): Comma-separated list of relationships to include

**Example Request:**

```http
GET /api/trina-crud/products?page=1&per_page=10&sort=-created_at&filter[is_active]=1&with=category
```

**Example Response:**

```json
{
    "data": [
        {
            "id": 1,
            "name": "Product One",
            "price": 29.99,
            "description": "Product description",
            "is_active": true,
            "category": {
                "id": 1,
                "name": "Electronics"
            }
        },
        ...
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "path": "/api/trina-crud/products",
        "per_page": 10,
        "to": 10,
        "total": 42
    }
}
```

### Get Single Resource (Show)

```http
GET /api/{prefix}/{resource}/{id}
```

**Request Parameters:**

- `with` (optional): Comma-separated list of relationships to include

**Example Request:**

```http
GET /api/trina-crud/products/1?with=category,reviews
```

**Example Response:**

```json
{
    "data": {
        "id": 1,
        "name": "Product One",
        "price": 29.99,
        "description": "Product description",
        "is_active": true,
        "category": {
            "id": 1,
            "name": "Electronics"
        },
        "reviews": [
            {
                "id": 1,
                "rating": 5,
                "comment": "Great product!"
            }
        ]
    }
}
```

### Create Resource (Store)

```http
POST /api/{prefix}/{resource}
```

TrinaCrud accepts both JSON and form data for POST requests. You can submit data in any of these formats:

- JSON body with `Content-Type: application/json`
- Form data with `Content-Type: application/x-www-form-urlencoded`
- Multipart form data with `Content-Type: multipart/form-data` (for file uploads)

**Request Body (JSON):**

```json
{
    "name": "New Product",
    "price": 49.99,
    "description": "A brand new product",
    "is_active": true,
    "category_id": 1
}
```

**Example Response:**

```json
{
    "data": {
        "id": 2,
        "name": "New Product",
        "price": 49.99,
        "description": "A brand new product",
        "is_active": true,
        "category_id": 1,
        "created_at": "2023-06-15T10:30:00.000000Z",
        "updated_at": "2023-06-15T10:30:00.000000Z"
    }
}
```

### Update Resource (Update)

```http
PUT /api/{prefix}/{resource}/{id}
```

Like the store endpoint, the update endpoint accepts both JSON and form data.

**Request Body:**

```json
{
    "price": 39.99,
    "description": "Updated description"
}
```

**Example Response:**

```json
{
    "data": {
        "id": 2,
        "name": "New Product",
        "price": 39.99,
        "description": "Updated description",
        "is_active": true,
        "category_id": 1,
        "created_at": "2023-06-15T10:30:00.000000Z",
        "updated_at": "2023-06-15T10:45:00.000000Z"
    }
}
```

### Delete Resource (Destroy)

```http
DELETE /api/{prefix}/{resource}/{id}
```

**Example Response:**

```json
{
    "message": "Resource deleted successfully"
}
```

### Get Model Schema Information

```http
GET /api/{prefix}/{resource}/get-schema
```

This endpoint returns information about the model, including authorized fields.

**Example Response:**

```json
{
    "model": "App.Models.AddBalanceRequest",
    "fields": [
        "id",
        "amount",
        "currency_id",
        "payment_method_id",
        "attachment",
        "approved",
        "notes",
        "created_at"
    ]
}
```

> Note: The schema endpoint only returns fields that the current user is authorized to read, create, or update based on their permissions. If the user doesn't have permission to access the model, a 403 Forbidden response will be returned.

### Get All Models Schema Information

```http
GET /api/{prefix}/get-schema
```

This endpoint returns schema information for all models that the current user has access to.

**Example Response:**

```json
[
    {
        "model": "App.Models.AddBalanceRequest",
        "fields": [
            "id",
            "amount",
            "currency_id",
            "payment_method_id",
            "attachment",
            "approved",
            "notes",
            "created_at"
        ]
    },
    {
        "model": "App.Models.AppScreen",
        "fields": []
    },
    {
        "model": "App.Models.AppScreenWidget",
        "fields": []
    },
    ...
]
```

> Important: The schema endpoints only include models that the requesting user has permission to access. For each model, only the fields that the user is authorized to access (read, update, or create) are included in the response. Empty field lists indicate that the user has access to the model but not to any specific fields. Models for which the user has no permissions are completely excluded from the response.

## Authorization

All endpoints respect the permissions system. Users will only see and be able to modify data they're authorized to access.

### Model-Level Authorization

If a user doesn't have the necessary model-level permission (e.g., `read App.Models.Product`), they'll receive a 403 Forbidden response when accessing any endpoint for that model.

### Attribute-Level Authorization

When creating or updating resources, unauthorized attributes are automatically filtered out. When reading resources, unauthorized attributes are excluded from the response.

## Error Responses

TrinaCrud provides standardized error responses:

### Validation Error (422 Unprocessable Entity)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": [
            "The name field is required."
        ],
        "price": [
            "The price must be a number."
        ]
    }
}
```

### Not Found Error (404 Not Found)

```json
{
    "message": "Resource not found."
}
```

### Authorization Error (403 Forbidden)

```json
{
    "message": "This action is unauthorized."
}
```

## Customizing Endpoints

While TrinaCrud provides a complete set of endpoints out of the box, you can customize them by creating your own controller that extends the base `ApiController`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Trinavo\TrinaCrud\Http\Controllers\ApiController;

class ProductController extends ApiController
{
    protected $model = Product::class;
    
    // Add a custom endpoint
    public function featured()
    {
        $products = Product::where('is_featured', true)->paginate();
        return $this->respondWithCollection($products);
    }
    
    // Override the index method to add custom behavior
    public function index(Request $request)
    {
        // Add custom logic before calling parent method
        $result = parent::index($request);
        
        // Add custom logic after calling parent method
        
        return $result;
    }
}
```

Then in your routes file:

```php
Route::get('products/featured', [ProductController::class, 'featured']);
```

## Next Steps

- Learn about [Response Formatting](api-responses.md) for customizing API responses
- Explore [Custom Controller Actions](custom-actions.md) for advanced customization
