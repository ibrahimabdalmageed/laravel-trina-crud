<?php

namespace Trinavo\TrinaCrud\Tests\Unit\Services;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Trinavo\TrinaCrud\Services\TrinaCrudAuthorizationService;
use Trinavo\TrinaCrud\Services\TrinaCrudModelService;

class TrinaCrudModelServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $authorizationService;
    protected $trinaCrudModelService;
    protected $testModelClass;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the test table
        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Mock the authorization service
        $this->authorizationService = Mockery::mock(TrinaCrudAuthorizationService::class);

        // Create the service with the mocked authorization service
        $this->trinaCrudModelService = new class($this->authorizationService) extends TrinaCrudModelService {
            // Override the method to avoid database lookup
            protected function findTrinaCrudModel(string $modelName): ?object
            {
                // This will be set in the test
                return $this->testTrinaCrudModel ?? null;
            }

            // Property to hold the test model
            public $testTrinaCrudModel;
        };

        // Create a test model class
        $this->testModelClass = new class extends Model {
            protected $table = 'test_models';
            protected $fillable = ['name', 'description'];
        };

        // Register the model in the container
        $this->app->bind(get_class($this->testModelClass), function () {
            return $this->testModelClass;
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('test_models');
        parent::tearDown();
    }

    public function test_it_can_get_model_records()
    {
        // Create some test records
        $this->testModelClass::create(['name' => 'Test 1', 'description' => 'Description 1']);
        $this->testModelClass::create(['name' => 'Test 2', 'description' => 'Description 2']);
        $this->testModelClass::create(['name' => 'Test 3', 'description' => 'Description 3']);

        // Create a mock TrinaCrudModel
        $trinaCrudModel = new \stdClass();
        $trinaCrudModel->class_name = get_class($this->testModelClass);

        // Set the test model on the service
        $this->trinaCrudModelService->testTrinaCrudModel = $trinaCrudModel;

        // Set up expectations for the authorization service
        $this->authorizationService->shouldReceive('scopeAuthorizedRecords')
            ->once()
            ->andReturnUsing(function ($query) {
                return $query;
            });

        $this->authorizationService->shouldReceive('filterAuthorizedColumns')
            ->once()
            ->with('test_model', ['name', 'description'])
            ->andReturn(['name', 'description']);

        // Call the method
        $result = $this->trinaCrudModelService->getModelRecords(
            'test_model',
            ['name', 'description'],
            null,
            [],
            [],
            15
        );

        // Assert the result
        $this->assertEquals(3, $result->total());
        $this->assertEquals('Test 1', $result->items()[0]->name);
        $this->assertEquals('Test 2', $result->items()[1]->name);
        $this->assertEquals('Test 3', $result->items()[2]->name);
    }

    public function test_it_throws_exception_when_model_not_found()
    {
        // Set the test model on the service to null
        $this->trinaCrudModelService->testTrinaCrudModel = null;

        // Expect an exception
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Model not found');

        // Call the method
        $this->trinaCrudModelService->getModelRecords(
            'non_existent_model',
            [],
            null,
            [],
            [],
            15
        );
    }

    public function test_it_can_get_model_records_with_filters()
    {
        // Create some test records
        $this->testModelClass::create(['name' => 'Test 1', 'description' => 'Description 1']);
        $this->testModelClass::create(['name' => 'Test 2', 'description' => 'Description 2']);
        $this->testModelClass::create(['name' => 'Test 3', 'description' => 'Description 3']);

        // Create a mock TrinaCrudModel
        $trinaCrudModel = new \stdClass();
        $trinaCrudModel->class_name = get_class($this->testModelClass);

        // Set the test model on the service
        $this->trinaCrudModelService->testTrinaCrudModel = $trinaCrudModel;

        // Set up expectations for the authorization service
        $this->authorizationService->shouldReceive('scopeAuthorizedRecords')
            ->once()
            ->andReturnUsing(function ($query) {
                return $query;
            });

        $this->authorizationService->shouldReceive('filterAuthorizedColumns')
            ->once()
            ->with('test_model', ['name', 'description'])
            ->andReturn(['name', 'description']);

        $this->authorizationService->shouldReceive('applyAuthorizedFilters')
            ->once()
            ->with(Mockery::type('Illuminate\Database\Eloquent\Builder'), 'test_model', [
                'name' => ['operator' => 'like', 'value' => 'Test']
            ])
            ->andReturnUsing(function ($query) {
                return $query->where('name', 'like', '%Test%');
            });

        // Call the method with filters
        $result = $this->trinaCrudModelService->getModelRecords(
            'test_model',
            ['name', 'description'],
            null,
            [],
            ['name' => ['operator' => 'like', 'value' => 'Test']],
            15
        );

        // Assert the result
        $this->assertEquals(3, $result->total());
    }

    public function test_it_can_get_model_records_with_relations()
    {
        // Create the related table
        Schema::create('related_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_model_id');
            $table->string('title');
            $table->timestamps();

            $table->foreign('test_model_id')->references('id')->on('test_models');
        });

        // Create a related model class
        $relatedModelClass = new class extends Model {
            protected $table = 'related_models';
            protected $fillable = ['test_model_id', 'title'];

            public function testModel()
            {
                return $this->belongsTo(get_class($this->app->make('test_model_class')), 'test_model_id');
            }
        };

        // Register the related model in the container
        $this->app->bind(get_class($relatedModelClass), function () use ($relatedModelClass) {
            return $relatedModelClass;
        });

        // Add a relation to the test model class
        $this->testModelClass = new class extends Model {
            protected $table = 'test_models';
            protected $fillable = ['name', 'description'];

            public function relatedModels()
            {
                return $this->hasMany(get_class(app()->make('related_model_class')), 'test_model_id');
            }
        };

        // Register the updated test model class
        $this->app->bind(get_class($this->testModelClass), function () {
            return $this->testModelClass;
        });

        // Register the classes with specific keys for relationship resolution
        $this->app->bind('test_model_class', function () {
            return $this->testModelClass;
        });

        $this->app->bind('related_model_class', function () use ($relatedModelClass) {
            return $relatedModelClass;
        });

        // Create some test records
        $model1 = $this->testModelClass::create(['name' => 'Test 1', 'description' => 'Description 1']);
        $model2 = $this->testModelClass::create(['name' => 'Test 2', 'description' => 'Description 2']);

        // Create related records
        $relatedModelClass::create(['test_model_id' => $model1->id, 'title' => 'Related 1']);
        $relatedModelClass::create(['test_model_id' => $model1->id, 'title' => 'Related 2']);
        $relatedModelClass::create(['test_model_id' => $model2->id, 'title' => 'Related 3']);

        // Create a mock TrinaCrudModel
        $trinaCrudModel = new \stdClass();
        $trinaCrudModel->class_name = get_class($this->testModelClass);

        // Set the test model on the service
        $this->trinaCrudModelService->testTrinaCrudModel = $trinaCrudModel;

        // Set up expectations for the authorization service
        $this->authorizationService->shouldReceive('scopeAuthorizedRecords')
            ->once()
            ->andReturnUsing(function ($query) {
                return $query;
            });

        $this->authorizationService->shouldReceive('filterAuthorizedColumns')
            ->once()
            ->with('test_model', ['name', 'description'])
            ->andReturn(['name', 'description']);

        $this->authorizationService->shouldReceive('loadAuthorizedRelations')
            ->once()
            ->with(
                Mockery::type('Illuminate\Database\Eloquent\Builder'),
                'test_model',
                ['relatedModels'],
                ['relatedModels' => ['title']]
            )
            ->andReturnUsing(function ($query, $modelName, $relations, $relationColumns) {
                return $query->with($relations);
            });

        // Call the method with relations
        $result = $this->trinaCrudModelService->getModelRecords(
            'test_model',
            ['name', 'description'],
            ['relatedModels'],
            ['relatedModels' => ['title']],
            [],
            15
        );

        // Assert the result
        $this->assertEquals(2, $result->total());

        // Clean up the related table
        Schema::dropIfExists('related_models');
    }
}
