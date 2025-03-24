<?php

namespace Trinavo\TrinaCrud\Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use Trinavo\TrinaCrud\Tests\Base\TrinaTestCase;
use Trinavo\TrinaCrud\Traits\HasCrud;

class TrinaCrudApiTest extends TrinaTestCase
{
    use RefreshDatabase;

    protected $testModel;
    protected $relatedModel;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test tables and models
        $this->createTestModels();

        $this->withoutMiddleware();

        $this->mockAuthService();
        $this->mockOwnershipService();
    }

    /**
     * Test listing model data via API
     */
    public function testListModelData()
    {
        // Create test data
        app('product_model')->create(['name' => 'Product 1', 'price' => 100, 'description' => 'Description 1']);
        app('product_model')->create(['name' => 'Product 2', 'price' => 200, 'description' => 'Description 2']);
        app('product_model')->create(['name' => 'Product 3', 'price' => 300, 'description' => 'Description 3']);
        // Make API request
        $response = $this->getJson('/api/crud/product_model');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'price', 'description', 'created_at', 'updated_at']
                ],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total'
            ]);
    }

    /**
     * Test listing model data with specific attributes
     */
    public function testListModelDataWithColumns()
    {
        // Create test data
        app('product_model')->create(['name' => 'Product 1', 'price' => 100, 'description' => 'Description 1']);
        app('product_model')->create(['name' => 'Product 2', 'price' => 200, 'description' => 'Description 2']);

        // Make API request with attributes parameter
        $response = $this->getJson('/api/crud/product_model?attributes[]=name&attributes[]=price');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['name', 'price']
                ]
            ]);

        // Ensure only requested attributes are returned
        $response->assertJsonMissing(['description']);
    }

    /**
     * Test listing model data with complex query filters
     */
    public function testListModelDataWithFilters()
    {
        // Create test data
        ProductModel::create(['name' => 'Budget Phone', 'price' => 100, 'description' => 'Affordable']);
        ProductModel::create(['name' => 'Mid-range Phone', 'price' => 300, 'description' => 'Good value']);
        ProductModel::create(['name' => 'Premium Phone', 'price' => 800, 'description' => 'High-end']);
        ProductModel::create(['name' => 'Ultra Phone', 'price' => 1200, 'description' => 'Flagship']);

        // Test 'between' operator
        $response = $this->getJson('/api/crud/product_model?filters[price][operator]=between&filters[price][value][]=200&filters[price][value][]=900');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Mid-range Phone')
            ->assertJsonPath('data.1.name', 'Premium Phone');

        // Test 'like' operator
        $response = $this->getJson('/api/crud/product_model?filters[name][operator]=like&filters[name][value]=%Phone%');

        $response->assertStatus(200)
            ->assertJsonCount(4, 'data');

        // Test multiple filters
        $response = $this->getJson('/api/crud/product_model?filters[price][operator]=>&filters[price][value]=500&filters[name][operator]=like&filters[name][value]=%Phone%');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Premium Phone')
            ->assertJsonPath('data.1.name', 'Ultra Phone');
    }


    /**
     * Test listing model data with complex query filters
     */
    public function testListModelDataWithFiltersPayload()
    {
        // Create test data
        ProductModel::create(['name' => 'Budget Phone', 'price' => 100, 'description' => 'Affordable']);
        ProductModel::create(['name' => 'Mid-range Phone', 'price' => 300, 'description' => 'Good value']);
        ProductModel::create(['name' => 'Premium Phone', 'price' => 800, 'description' => 'High-end']);
        ProductModel::create(['name' => 'Ultra Phone', 'price' => 1200, 'description' => 'Flagship']);

        // Test 'between' operator
        $response = $this->json('GET', '/api/crud/product_model', [

            'filters' => [
                'price' => [
                    'operator' => 'between',
                    'value' => [200, 900]
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Mid-range Phone')
            ->assertJsonPath('data.1.name', 'Premium Phone');

        // Test 'like' operator
        $response = $this->json('GET', '/api/crud/product_model', ['filters' => [
            'name' => [
                'operator' => 'like',
                'value' => '%Phone%'
            ]
        ]]);

        $response->assertStatus(200)
            ->assertJsonCount(4, 'data');

        // Test multiple filters
        $response = $this->json('GET', '/api/crud/product_model', ['filters' => [
            'price' => [
                'operator' => '>=',
                'value' => 500
            ],
            'name' => [
                'operator' => 'like',
                'value' => '%Phone%'
            ]
        ]]);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Premium Phone')
            ->assertJsonPath('data.1.name', 'Ultra Phone');
    }

    /**
     * Test listing model data with relationships
     */
    public function testListModelDataWithRelationships()
    {
        // Create test data
        $category1 = CategoryModel::create(['name' => 'Electronics']);
        $category2 = CategoryModel::create(['name' => 'Accessories']);

        $product1 = ProductModel::create([
            'name' => 'Smartphone',
            'price' => 500,
            'description' => 'Latest model',
            'category_id' => $category1->id
        ]);

        $product2 = ProductModel::create([
            'name' => 'Headphones',
            'price' => 100,
            'description' => 'Wireless',
            'category_id' => $category2->id
        ]);

        // Make API request with relationship
        $response = $this->getJson('/api/crud/product_model?with=category');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'description',
                        'category_id',
                        'created_at',
                        'updated_at',
                        'category' => ['id', 'name', 'created_at', 'updated_at']
                    ]
                ]
            ]);

        // Verify relationship data
        $response->assertJsonPath('data.0.category.name', 'Electronics')
            ->assertJsonPath('data.1.category.name', 'Accessories');
    }

    /**
     * Test filtering by relationship
     */
    public function testFilterByRelationship()
    {
        // Create test data
        $category1 = CategoryModel::create(['name' => 'Electronics']);
        $category2 = CategoryModel::create(['name' => 'Accessories']);

        ProductModel::create([
            'name' => 'Smartphone',
            'price' => 500,
            'description' => 'Latest model',
            'category_id' => $category1->id
        ]);

        ProductModel::create([
            'name' => 'Laptop',
            'price' => 1000,
            'description' => 'Powerful',
            'category_id' => $category1->id
        ]);

        ProductModel::create([
            'name' => 'Headphones',
            'price' => 100,
            'description' => 'Wireless',
            'category_id' => $category2->id
        ]);

        // Filter by category name
        $response = $this->json(
            'GET',
            '/api/crud/product_model',
            [
                'with' => 'category',
                'filters' => [
                    'category.name' => [
                        'operator' => 'like',
                        'value' => 'Electronics'
                    ]
                ]
            ]
        );

        // Assert response
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Smartphone')
            ->assertJsonPath('data.1.name', 'Laptop');
    }

    /**
     * Test getting a single model record
     */
    public function testGetSingleModelRecord()
    {
        // Create test data
        $product = app('product_model')->create(['name' => 'Test Product', 'price' => 150, 'description' => 'Test Description']);

        // Make API request
        $response = $this->getJson("/api/crud/product_model/{$product->id}");

        // Assert response
        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'name' => 'Test Product',
                'price' => 150,
                'description' => 'Test Description'
            ]);
    }

    /**
     * Test creating a model record
     */
    public function testCreateModelRecord()
    {
        // Data for new record
        $data = [
            'name' => 'New Product',
            'price' => 250,
            'description' => 'Brand new product'
        ];

        // Make API request
        $response = $this->postJson('/api/crud/product_model', $data);

        // Assert response
        $response->assertStatus(201)
            ->assertJson([
                'name' => 'New Product',
                'price' => 250,
                'description' => 'Brand new product'
            ]);

        // Verify record was created in database
        $this->assertDatabaseHas('products', $data);
    }

    /**
     * Test updating a model record
     */
    public function testUpdateModelRecord()
    {
        // Create test data
        $product = app('product_model')->create(['name' => 'Old Name', 'price' => 100, 'description' => 'Old Description']);

        // Data for update
        $data = [
            'name' => 'Updated Name',
            'price' => 150
        ];

        // Make API request
        $response = $this->putJson("/api/crud/product_model/{$product->id}", $data);

        // Assert response
        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'name' => 'Updated Name',
                'price' => 150,
                'description' => 'Old Description' // Not updated
            ]);

        // Verify record was updated in database
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price' => 150
        ]);
    }

    /**
     * Test deleting a model record
     */
    public function testDeleteModelRecord()
    {
        // Create test data
        $product = app('product_model')->create(['name' => 'Delete Me', 'price' => 100, 'description' => 'To be deleted']);

        // Make API request
        $response = $this->deleteJson("/api/crud/product_model/{$product->id}");

        // Assert response
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify record was deleted from database
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * Create test models and tables for testing
     */
    private function createTestModels()
    {
        // Create products table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('description')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->timestamps();
        });

        // Create categories table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Bind models to container
        $this->app->bind('product_model', ProductModel::class);
        $this->app->bind('category_model', CategoryModel::class);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');

        Mockery::close();
        parent::tearDown();
    }
}


class TestUser extends Model
{
    use HasCrud;
    protected $table = 'test_users';
    protected $fillable = ['id', 'name', 'email', 'created_at', 'updated_at'];
}

class ProductModel extends Model
{
    use HasCrud;
    protected $table = 'products';
    protected $fillable = ['id', 'name', 'price', 'description', 'category_id', 'created_at', 'updated_at'];

    public function category()
    {
        return $this->belongsTo(app('category_model'), 'category_id');
    }
}

class CategoryModel extends Model
{
    use HasCrud;
    protected $table = 'categories';
    protected $fillable = ['id', 'name', 'created_at', 'updated_at'];

    public function products()
    {
        return $this->hasMany(app('product_model'), 'category_id');
    }
}
