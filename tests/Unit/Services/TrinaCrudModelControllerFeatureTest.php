<?php

namespace Trinavo\TrinaCrud\Tests\Unit\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Illuminate\Pagination\LengthAwarePaginator;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Contracts\ModelServiceInterface;
use Trinavo\TrinaCrud\Contracts\OwnershipServiceInterface;
use Trinavo\TrinaCrud\Tests\Base\TrinaTestCase;
use Trinavo\TrinaCrud\Traits\HasCrud;

class TrinaCrudModelControllerFeatureTest extends   TrinaTestCase
{
    use RefreshDatabase;

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
        // Create some test data
        TestUser::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        TestUser::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        $this->app->bind(TestUser::class, function ($app) {
            return new TestUser();
        });

        // Create mocks for common services
        $this->mockAuthService();
        $this->mockOwnershipService();
    }

    public function testDatabaseConnection()
    {
        // Verify that the test users were created
        $this->assertEquals(2, TestUser::count());
        $user = TestUser::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('John Doe', $user->name);
    }

    public function testGetModelRecordsReturnsExpectedResults()
    {
        // Call the method
        $result = app(ModelServiceInterface::class)->all(TestUser::class);

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
        $result = app(ModelServiceInterface::class)->find(TestUser::class, $user->id);

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
        $result = app(ModelServiceInterface::class)->create(TestUser::class, $data);

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
        $result = app(ModelServiceInterface::class)->update(TestUser::class, $user->id, $data);

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
        $result = app(ModelServiceInterface::class)->delete(TestUser::class, $user->id);

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
class TestUser extends Model
{
    use HasCrud;
    protected $table = 'test_users';
    protected $fillable = ['id', 'name', 'email'];
}
