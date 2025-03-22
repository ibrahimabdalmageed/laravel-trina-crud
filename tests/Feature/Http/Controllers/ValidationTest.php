<?php

namespace Trinavo\TrinaCrud\Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Tests\Base\TrinaTestCase;
use Trinavo\TrinaCrud\Traits\HasCrud;
use Trinavo\TrinaCrud\Enums\CrudAction;

class ValidationTest extends TrinaTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test tables and models
        $this->createTestModels();

        $this->mockAuthService();
        $this->mockOwnershipService();
    }

    /**
     * Test validation when creating a model record with invalid data
     */
    public function testCreateModelWithInvalidData()
    {
        // Data for new record with invalid values
        $data = [
            'name' => '', // Empty name (required)
            'price' => -10, // Negative price (min:0)
            'description' => 'This is a valid description'
        ];

        // Make API request
        $response = $this->postJson('/api/crud/validation_model', $data);

        // Assert validation failure response
        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Validation failed',
            ])
            ->assertJsonValidationErrors(['name', 'price']);
    }

    /**
     * Test validation when creating a model record with valid data
     */
    public function testCreateModelWithValidData()
    {
        // Data for new record with valid values
        $data = [
            'name' => 'Valid Product',
            'price' => 99.99,
            'description' => 'This is a valid description'
        ];

        // Make API request
        $response = $this->postJson('/api/crud/validation_model', $data);

        // Assert successful response
        $response->assertStatus(201)
            ->assertJson([
                'name' => 'Valid Product',
                'price' => 99.99,
                'description' => 'This is a valid description'
            ]);

        // Verify record was created in database
        $this->assertDatabaseHas('validation_models', $data);
    }

    /**
     * Test validation when updating a model record with invalid data
     */
    public function testUpdateModelWithInvalidData()
    {
        // Create test data
        $model = app('validation_model')->create([
            'name' => 'Original Name',
            'price' => 50,
            'description' => 'Original description'
        ]);

        // Data for update with invalid values
        $data = [
            'name' => 'AB', // Too short (min:3)
            'price' => -5 // Negative price (min:0)
        ];

        // Make API request
        $response = $this->putJson("/api/crud/validation_model/{$model->id}", $data);

        // Assert validation failure response
        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Validation failed',
            ])
            ->assertJsonValidationErrors(['name', 'price']);

        // Verify record was not updated in database
        $this->assertDatabaseHas('validation_models', [
            'id' => $model->id,
            'name' => 'Original Name',
            'price' => 50
        ]);
    }

    /**
     * Test validation when updating a model record with valid data
     */
    public function testUpdateModelWithValidData()
    {
        // Create test data
        $model = app('validation_model')->create([
            'name' => 'Original Name',
            'price' => 50,
            'description' => 'Original description'
        ]);

        // Data for update with valid values
        $data = [
            'name' => 'Updated Valid Name',
            'price' => 75.50
        ];

        // Make API request
        $response = $this->putJson("/api/crud/validation_model/{$model->id}", $data);

        // Assert successful response
        $response->assertStatus(200)
            ->assertJson([
                'id' => $model->id,
                'name' => 'Updated Valid Name',
                'price' => 75.50,
                'description' => 'Original description' // Not updated
            ]);

        // Verify record was updated in database
        $this->assertDatabaseHas('validation_models', [
            'id' => $model->id,
            'name' => 'Updated Valid Name',
            'price' => 75.50
        ]);
    }

    /**
     * Create test models and tables for testing
     */
    private function createTestModels()
    {
        // Create validation_models table
        Schema::create('validation_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Bind model to container
        $this->app->bind('validation_model', ValidationModel::class);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('validation_models');

        parent::tearDown();
    }
}

/**
 * Model with validation rules for testing
 */
class ValidationModel extends Model
{
    use HasCrud;
    
    protected $table = 'validation_models';
    protected $fillable = ['id', 'name', 'price', 'description', 'created_at', 'updated_at'];
    
    /**
     * Define validation rules for CRUD operations
     * 
     * @param CrudAction $action The CRUD action
     * @return array
     */
    public function getCrudRules(CrudAction $action): array
    {
        if ($action === CrudAction::CREATE) {
            return [
                'name' => 'required|string|min:3|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string'
            ];
        }
        
        if ($action === CrudAction::UPDATE) {
            return [
                'name' => 'sometimes|string|min:3|max:255',
                'price' => 'sometimes|numeric|min:0',
                'description' => 'nullable|string'
            ];
        }
        
        return [];
    }
}
