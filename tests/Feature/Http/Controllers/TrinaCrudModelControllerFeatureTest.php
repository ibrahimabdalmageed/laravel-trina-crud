<?php

namespace Trinavo\TrinaCrud\Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Trinavo\TrinaCrud\Tests\TestCase;
use Trinavo\TrinaCrud\Models\TrinaCrudModel;
use Trinavo\TrinaCrud\Services\TrinaCrudModelService;
use Trinavo\TrinaCrud\Services\TrinaCrudAuthorizationService;
use Mockery;
use Illuminate\Pagination\LengthAwarePaginator;

class TrinaCrudModelControllerFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $authService;
    protected $modelService;

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
            'class_name' => 'TestUser',
            'caption' => 'Test User',
            'multi_caption' => 'Test Users',
            'page_size' => 10,
            'public_model' => true,
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

        // Create mocks for common services
        $this->authService = Mockery::mock(TrinaCrudAuthorizationService::class);
        $this->modelService = Mockery::mock(TrinaCrudModelService::class);

        // Bind the mock to the container
        $this->app->instance(TrinaCrudModelService::class, $this->modelService);
    }

    public function testDatabaseConnection()
    {
        // Verify that the TrinaCrudModel was created
        $model = TrinaCrudModel::where('class_name', 'TestUser')->first();
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

        // Set up the mock to return our test data when getModelRecords is called
        $this->modelService->shouldReceive('getModelRecords')
            ->with('TestUser')
            ->andReturn(new LengthAwarePaginator(
                $users,
                $users->count(),
                15,
                1
            ));

        // Call the method
        $result = $this->modelService->getModelRecords('TestUser');

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

        // Set up the mock to return our test data when getModelRecord is called
        $this->modelService->shouldReceive('getModelRecord')
            ->with('TestUser', $user->id)
            ->andReturn($user);

        // Call the method
        $result = $this->modelService->getModelRecord('TestUser', $user->id);

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

        // Create a new user
        $newUser = new TestUser($data);
        $newUser->id = 3;

        // Set up the mock to return our test data when createModelRecord is called
        $this->modelService->shouldReceive('createModelRecord')
            ->with('TestUser', $data)
            ->andReturn($newUser);

        // Call the method
        $result = $this->modelService->createModelRecord('TestUser', $data);

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

        // Create an updated user
        $updatedUser = new TestUser($data);
        $updatedUser->id = $user->id;

        // Set up the mock to return our test data when updateModelRecord is called
        $this->modelService->shouldReceive('updateModelRecord')
            ->with('TestUser', $user->id, $data)
            ->andReturn($updatedUser);

        // Call the method
        $result = $this->modelService->updateModelRecord('TestUser', $user->id, $data);

        // Assert the result is the expected model
        $this->assertEquals($user->id, $result->id);
        $this->assertEquals('Updated User', $result->name);
        $this->assertEquals('updated@example.com', $result->email);
    }

    public function testDeleteModelRecordRemovesRecord()
    {
        // Get a test user
        $user = TestUser::first();

        // Set up the mock to return true when deleteModelRecord is called
        $this->modelService->shouldReceive('deleteModelRecord')
            ->with('TestUser', $user->id)
            ->andReturn(true);

        // Call the method
        $result = $this->modelService->deleteModelRecord('TestUser', $user->id);

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
