<?php

namespace Trinavo\TrinaCrud\Tests\Feature\Http\Controllers;

use Illuminate\Container\Attributes\Authenticated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Trinavo\TrinaCrud\Tests\TestCase;
use Trinavo\TrinaCrud\Models\TrinaCrudModel;
use Mockery;
use Illuminate\Pagination\LengthAwarePaginator;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Contracts\ModelServiceInterface;

class TrinaCrudModelControllerFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $authService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test table
        if (!Schema::hasTable('test_users')) {
            Schema::create('test_users', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamps();
            });
        }

        // Create a test model in the TrinaCrudModel table
        TrinaCrudModel::create([
            'class_name' => TestUser::class,
            'caption' => 'Test User',
            'multi_caption' => 'Test Users',
            'model_name' => 'test_user',
            'model_short' => 'test_user',
            'page_size' => 10,
            'order_by' => 'id'
        ]);

        // Create some test data
        TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        TestUser::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        //run sync columns
        $this->artisan('trinacrud:sync-columns', ['model' => TestUser::class]);

        $this->app->bind(TestUser::class, function ($app) {
            return new TestUser();
        });

        // Create mocks for common services
        $this->authService = Mockery::mock(AuthorizationServiceInterface::class);

        $this->authService->shouldReceive('hasPermissionTo')->andReturn(true);
        $this->authService->shouldReceive('getUser')->andReturn(null);

        // Bind the mock to the container
        $this->app->singleton(AuthorizationServiceInterface::class, function ($app) {
            return $this->authService;
        });
    }

    public function testDatabaseConnection()
    {
        // Verify that the TrinaCrudModel was created
        $model = TrinaCrudModel::where('class_name', TestUser::class)->first();
        $this->assertNotNull($model);
        $this->assertEquals('Test User', $model->caption);

        // Verify that the test users were created
        $this->assertEquals(2, TestUser::count());
        $user = TestUser::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('John Doe', $user->name);
    }

    public function testGetModelRecordsReturnsExpectedResults()
    {
        // Get the test data
        $users = TestUser::all();

        // Call the method
        $result = app(ModelServiceInterface::class)->getModelRecords(TestUser::class);

        // Assert the result is a paginator
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);

        // Assert the paginator contains the expected number of items
        $this->assertEquals(2, $result->total());

        // Assert the items in the paginator are the expected models
        $items = $result->items();
        $this->assertCount(2, $items);
        $this->assertEquals('John Doe', $items[0]->name);
        $this->assertEquals('jane@example.com', $items[1]->email);
    }

    public function testGetModelRecordReturnsExpectedResult()
    {
        // Get a test user
        $user = TestUser::first();
        // Call the method
        $result = app(ModelServiceInterface::class)->getModelRecord(TestUser::class, $user->id);

        // Assert the result is the expected model
        $this->assertEquals($user->id, $result->id);
        $this->assertEquals('John Doe', $result->name);
        $this->assertEquals('john@example.com', $result->email);
    }

    public function testCreateModelRecordCreatesNewRecord()
    {
        // Create test data
        $data = [
            'name' => 'New User',
            'email' => 'new@example.com',
        ];

        // Call the method
        $result = app(ModelServiceInterface::class)->createModelRecord(TestUser::class, $data);

        // Assert the result is the expected model
        $this->assertEquals(3, $result->id);
        $this->assertEquals('New User', $result->name);
        $this->assertEquals('new@example.com', $result->email);
    }

    public function testUpdateModelRecordModifiesExistingRecord()
    {
        // Get a test user
        $user = TestUser::first();

        // Create test data
        $data = [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
        ];

        // Call the method
        $result = app(ModelServiceInterface::class)->updateModelRecord(TestUser::class, $user->id, $data);

        // Assert the result is the expected model
        $this->assertEquals($user->id, $result->id);
        $this->assertEquals('Updated User', $result->name);
        $this->assertEquals('updated@example.com', $result->email);
    }

    public function testDeleteModelRecordRemovesRecord()
    {
        // Get a test user
        $user = TestUser::first();

        // Call the method
        $result = app(ModelServiceInterface::class)->deleteModelRecord(TestUser::class, $user->id);

        // Assert the result is true
        $this->assertTrue($result);
    }

    protected function tearDown(): void
    {
        // Drop the test table
        Schema::dropIfExists('test_users');

        Mockery::close();
        parent::tearDown();
    }
}

/**
 * Test model class for the feature test
 */
class TestUser extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'test_users';
    protected $fillable = ['name', 'email'];
}
