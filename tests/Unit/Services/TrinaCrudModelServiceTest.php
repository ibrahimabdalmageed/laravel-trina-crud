<?php

namespace Trinavo\TrinaCrud\Tests\Unit\Services;

use Illuminate\Database\Eloquent\Model;
use Trinavo\TrinaCrud\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Contracts\ModelServiceInterface;
use Trinavo\TrinaCrud\Contracts\OwnershipServiceInterface;
use Trinavo\TrinaCrud\Models\TrinaCrudColumn;
use Trinavo\TrinaCrud\Models\TrinaCrudModel;

class TrinaCrudModelServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuthorizationServiceInterface|Mockery\MockInterface $authorizationService;
    protected ModelServiceInterface|Mockery\MockInterface $trinaCrudModelService;
    protected OwnershipServiceInterface|Mockery\MockInterface $ownershipService;

    protected Model $testModelClass;

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
        $this->authorizationService = Mockery::mock(AuthorizationServiceInterface::class);
        $this->trinaCrudModelService = app(ModelServiceInterface::class);
        $this->ownershipService = Mockery::mock(OwnershipServiceInterface::class);

        $this->authorizationService->shouldReceive('hasPermissionTo')->andReturn(true);

        $this->app->singleton(AuthorizationServiceInterface::class, function () {
            return $this->authorizationService;
        });
        $this->app->singleton(ModelServiceInterface::class, function () {
            return $this->trinaCrudModelService;
        });
        $this->app->singleton(OwnershipServiceInterface::class, function () {
            return $this->ownershipService;
        });


        // Create a test model class
        $this->testModelClass = new class extends Model {
            protected $table = 'test_models';
            protected $fillable = ['name', 'description'];
        };


        $trinaCrudModel = TrinaCrudModel::create([
            'class_name' => 'test_model',
            'model_name' => 'Test Model',
            'model_short' => 'test_model',
            'caption' => 'Test Model',
            'multi_caption' => 'Test Model',
            'page_size' => 20,
            'public_model' => true,
        ]);

        //Artisan::call('trinacrud:sync-columns', ['model' => get_class($this->testModelClass)]);
        TrinaCrudColumn::create([
            'trina_crud_model_id' => $trinaCrudModel->id,
            'column_name' => 'name',
            'column_db_type' => 'string',
            'column_user_type' => 'text',
            'column_label' => 'Name',
            'required' => true,
            'default_value' => null,
            'grid_order' => 1,
            'edit_order' => 1,
            'size' => 255,
            'hide' => false,
        ]);

        TrinaCrudColumn::create([
            'trina_crud_model_id' => $trinaCrudModel->id,
            'column_name' => 'description',
            'column_db_type' => 'text',
            'column_user_type' => 'textarea',
            'column_label' => 'Description',
            'required' => false,
            'default_value' => null,
            'grid_order' => 2,
            'edit_order' => 2,
            'size' => null,
            'hide' => false,
        ]);

        // Register the model in the container
        $this->app->bind('test_model', function () {
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

        // Set up expectations for the authorization service
        $this->authorizationService->shouldReceive('scopeAuthorizedRecords')
            ->once()
            ->andReturnUsing(function ($query) {
                return $query;
            });

        $this->authorizationService->shouldReceive('filterAuthorizedColumns')
            ->once()
            ->with($trinaCrudModel->class_name, ['name', 'description'])
            ->andReturn(['name', 'description']);

        // Call the method
        $result = $this->trinaCrudModelService->getModelRecords(
            $trinaCrudModel->class_name,
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
                return $this->hasMany(get_class($this->testModelClass), 'test_model_id');
            }
        };

        // Register the updated test model class
        $this->app->bind(get_class($this->testModelClass), function () {
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
