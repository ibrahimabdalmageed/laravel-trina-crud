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
use Trinavo\TrinaCrud\Models\TrinaCrudModel;
use Illuminate\Support\Facades\Artisan;

class TrinaCrudModelServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuthorizationServiceInterface|Mockery\MockInterface $authorizationService;
    protected ModelServiceInterface $modelService;
    protected OwnershipServiceInterface|Mockery\MockInterface $ownershipService;

    protected Model $testModelClass;
    protected Model $relatedModelClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTestModels();

        // Create proper mocks
        $this->authorizationService = Mockery::mock(AuthorizationServiceInterface::class);
        $this->ownershipService = Mockery::mock(OwnershipServiceInterface::class);

        // IMPORTANT: We need to mock the ModelService to control its behavior in tests
        $this->modelService = app(ModelServiceInterface::class);

        // Default behaviors
        $this->authorizationService->shouldReceive('hasPermissionTo')->andReturn(true);
        $this->authorizationService->shouldReceive('getUser')->andReturn(null);

        // Bind the mocks to the container
        $this->app->instance(AuthorizationServiceInterface::class, $this->authorizationService);
        $this->app->instance(OwnershipServiceInterface::class, $this->ownershipService);
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

        $result = $this->modelService->getModelRecords(
            modelName: get_class($this->testModelClass),
            columns: ['name', 'description'],
        );

        // Assert the result
        $this->assertEquals(3, $result->total());
        $this->assertEquals('Test 1', $result->items()[0]->name);
        $this->assertEquals('Test 2', $result->items()[1]->name);
        $this->assertEquals('Test 3', $result->items()[2]->name);
    }

    public function test_it_throws_exception_when_model_not_found()
    {

        // Expect an exception
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Model not found');

        // Call the method
        $this->modelService->getModelRecords('non_existent_model',);
    }

    public function test_it_can_get_model_records_with_filters()
    {
        // Create some test records
        $this->testModelClass::create(['name' => 'Test 1', 'description' => 'Description 1']);
        $this->testModelClass::create(['name' => 'Test 2', 'description' => 'Description 2']);
        $this->testModelClass::create(['name' => 'Test 3', 'description' => 'Description 3']);

        // Call the method with filters
        $result = $this->modelService->getModelRecords(
            modelName: get_class($this->testModelClass),
            columns: ['name', 'description'],
            filters: ['name' => ['operator' => 'like', 'value' => 'Test']],
        );

        // Assert the result
        $this->assertEquals(3, $result->total());
    }

    public function test_it_can_get_model_records_with_relations()
    {


        // Create some test records
        $model1 = $this->testModelClass::create(['name' => 'Test 1', 'description' => 'Description 1']);
        $model2 = $this->testModelClass::create(['name' => 'Test 2', 'description' => 'Description 2']);

        // Create related records
        $this->relatedModelClass::create(['test_model_id' => $model1->id, 'title' => 'Related 1']);
        $this->relatedModelClass::create(['test_model_id' => $model1->id, 'title' => 'Related 2']);
        $this->relatedModelClass::create(['test_model_id' => $model2->id, 'title' => 'Related 3']);

        // Call the method with relations
        $result = $this->modelService->getModelRecords(
            modelName: get_class($this->testModelClass),
            columns: ['name', 'description'],
            with: ['relatedModels'],
            relationColumns: ['relatedModels' => ['title']],
        );

        $this->assertEquals(2, $result->total());
        $this->assertArrayHasKey('related_models', $result->items()[0]->toArray());
    }


    private function createTestModels()
    {

        $this->testModelClass = new class extends Model {
            protected $table = 'test_models';
            protected $fillable = ['name', 'description'];

            public function relatedModels()
            {
                // Simply use the table name as we know it
                return $this->hasMany(app('related_model'), 'test_model_id');
            }
        };

        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        TrinaCrudModel::create([
            'class_name' => get_class($this->testModelClass),
            'model_name' => 'Test Model',
            'model_short' => 'test_model',
            'caption' => 'Test Model',
            'multi_caption' => 'Test Model',
            'page_size' => 20,
        ]);



        // Create the related table
        Schema::create('related_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_model_id');
            $table->string('title');
            $table->timestamps();

            $table->foreign('test_model_id')->references('id')->on('test_models');
        });

        $this->relatedModelClass = new class extends Model {
            protected $table = 'related_models';
            protected $fillable = ['test_model_id', 'title'];

            public function testModel()
            {
                return $this->belongsTo(app('test_model'), 'test_model_id');
            }
        };

        TrinaCrudModel::create([
            'class_name' => get_class($this->relatedModelClass),
            'model_name' => 'Related Test Model',
            'model_short' => 'related_test_model',
            'caption' => 'Related Test Model',
            'multi_caption' => 'Related Test Model',
        ]);

        $this->app->bind('test_model', function ($app) {
            return $this->testModelClass;
        });
        $this->app->bind('related_model', function ($app) {
            return $this->relatedModelClass;
        });

        Artisan::call('trinacrud:sync-columns', ['model' => get_class($this->testModelClass)]);
        Artisan::call('trinacrud:sync-columns', ['model' => get_class($this->relatedModelClass)]);
    }
}
