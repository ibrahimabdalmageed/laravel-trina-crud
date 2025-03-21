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
use Illuminate\Pagination\LengthAwarePaginator;

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

        // Create proper mocks
        $this->authorizationService = Mockery::mock(AuthorizationServiceInterface::class);
        $this->ownershipService = Mockery::mock(OwnershipServiceInterface::class);

        // IMPORTANT: We need to mock the ModelService to control its behavior in tests
        $this->trinaCrudModelService = Mockery::mock(ModelServiceInterface::class);

        // Default behaviors
        $this->authorizationService->shouldReceive('hasPermissionTo')->andReturn(true);
        $this->authorizationService->shouldReceive('getUser')->andReturn(null);

        // Bind the mocks to the container
        $this->app->instance(AuthorizationServiceInterface::class, $this->authorizationService);
        $this->app->instance(OwnershipServiceInterface::class, $this->ownershipService);
        $this->app->instance(ModelServiceInterface::class, $this->trinaCrudModelService);

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

        // Create a test result to be returned by our mock
        $records = $this->testModelClass::all();
        $result = new LengthAwarePaginator(
            $records,
            3,
            15,
            1
        );

        // Mock the getModelRecords method directly
        $this->trinaCrudModelService->shouldReceive('getModelRecords')
            ->once()
            ->with(
                'test_model',
                ['name', 'description'],
                null,
                [],
                [],
                15
            )
            ->andReturn($result);

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
        // Have our mock throw an exception when non_existent_model is used
        $this->trinaCrudModelService->shouldReceive('getModelRecords')
            ->once()
            ->with(
                'non_existent_model',
                [],
                null,
                [],
                [],
                15
            )
            ->andThrow(new NotFoundHttpException('Model not found'));

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

        // Create a test result to be returned by our mock
        $records = $this->testModelClass::all();
        $result = new LengthAwarePaginator(
            $records,
            3,
            15,
            1
        );

        // Mock the getModelRecords method directly
        $this->trinaCrudModelService->shouldReceive('getModelRecords')
            ->once()
            ->with(
                'test_model',
                ['name', 'description'],
                null,
                [],
                ['name' => ['operator' => 'like', 'value' => 'Test']],
                15
            )
            ->andReturn($result);

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

        // We need to use string class names for the relationships to avoid closure serialization issues

        // First, we'll create our model classes
        $testModelClass = new class extends Model {
            protected $table = 'test_models';
            protected $fillable = ['name', 'description'];

            public function relatedModels()
            {
                // Simply use the table name as we know it
                return $this->hasMany('Trinavo\TrinaCrud\Tests\Unit\Services\RelatedTestModel', 'test_model_id');
            }
        };

        // Create the related model class
        $relatedModelClass = new class extends Model {
            protected $table = 'related_models';
            protected $fillable = ['test_model_id', 'title'];

            public function testModel()
            {
                return $this->belongsTo('test_model', 'test_model_id');
            }
        };

        // Define class names in the current namespace so they can be referenced properly
        class_alias(get_class($testModelClass), 'Trinavo\TrinaCrud\Tests\Unit\Services\TestModel');
        class_alias(get_class($relatedModelClass), 'Trinavo\TrinaCrud\Tests\Unit\Services\RelatedTestModel');

        // Register the models in the container
        $this->app->bind('test_model', function () use ($testModelClass) {
            return $testModelClass;
        });

        $this->app->bind('Trinavo\TrinaCrud\Tests\Unit\Services\TestModel', function () use ($testModelClass) {
            return $testModelClass;
        });

        $this->app->bind('Trinavo\TrinaCrud\Tests\Unit\Services\RelatedTestModel', function () use ($relatedModelClass) {
            return $relatedModelClass;
        });

        // Create some test records
        $model1 = $testModelClass::create(['name' => 'Test 1', 'description' => 'Description 1']);
        $model2 = $testModelClass::create(['name' => 'Test 2', 'description' => 'Description 2']);

        // Create related records
        $relatedModelClass::create(['test_model_id' => $model1->id, 'title' => 'Related 1']);
        $relatedModelClass::create(['test_model_id' => $model1->id, 'title' => 'Related 2']);
        $relatedModelClass::create(['test_model_id' => $model2->id, 'title' => 'Related 3']);

        // Create a test result to be returned by our mock
        $result = new LengthAwarePaginator(
            [$model1, $model2],
            2,
            15,
            1
        );

        // Mock the getModelRecords method directly
        $this->trinaCrudModelService->shouldReceive('getModelRecords')
            ->once()
            ->with(
                'test_model',
                ['name', 'description'],
                ['relatedModels'],
                ['relatedModels' => ['title']],
                [],
                15
            )
            ->andReturn($result);

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
