<?php

namespace Trinavo\TrinaCrud\Tests\Unit\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Contracts\OwnershipServiceInterface;
use Trinavo\TrinaCrud\Services\ModelService;
use Trinavo\TrinaCrud\Tests\TestCase;
use Trinavo\TrinaCrud\Traits\HasCrud;

class ModelServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $modelService;
    protected $authService;
    protected $ownershipService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks for dependencies
        $this->authService = Mockery::mock(AuthorizationServiceInterface::class);
        $this->ownershipService = Mockery::mock(OwnershipServiceInterface::class);

        // Create the service instance
        $this->modelService = new ModelService(
            $this->authService,
            $this->ownershipService
        );

        // Bind test models to the container
        $this->app->bind('valid_model', ValidTestModel::class);
        $this->app->bind('invalid_model_no_trait', InvalidTestModelNoTrait::class);
        $this->app->bind('not_a_model', NotAModel::class);
    }

    /**
     * Test verifyModel with a valid model class that uses HasCrud trait
     */
    public function testVerifyModelWithValidModel()
    {
        // Test with class name
        $result = $this->modelService->verifyModel(ValidTestModel::class);
        $this->assertTrue($result, 'Should verify a valid model class');

        // Test with container binding
        $result = $this->modelService->verifyModel('valid_model');
        $this->assertTrue($result, 'Should verify a valid model from container binding');
    }

    /**
     * Test verifyModel with a model that doesn't use HasCrud trait
     */
    public function testVerifyModelWithInvalidModelNoTrait()
    {
        // Test with class name
        $result = $this->modelService->verifyModel(InvalidTestModelNoTrait::class);
        $this->assertFalse($result, 'Should reject a model without HasCrud trait');

        // Test with container binding
        $result = $this->modelService->verifyModel('invalid_model_no_trait');
        $this->assertFalse($result, 'Should reject a model without HasCrud trait from container binding');
    }

    /**
     * Test verifyModel with a class that is not a model
     */
    public function testVerifyModelWithNonModelClass()
    {
        // Test with class name
        $result = $this->modelService->verifyModel(NotAModel::class);
        $this->assertFalse($result, 'Should reject a class that is not a model');

        // Test with container binding
        $result = $this->modelService->verifyModel('not_a_model');
        $this->assertFalse($result, 'Should reject a non-model class from container binding');
    }

    /**
     * Test verifyModel with a non-existent class
     */
    public function testVerifyModelWithNonExistentClass()
    {
        $result = $this->modelService->verifyModel('NonExistentClass');
        $this->assertFalse($result, 'Should reject a non-existent class');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

/**
 * Valid test model with HasCrud trait
 */
class ValidTestModel extends Model
{
    use HasCrud;
    protected $table = 'test_models';
}

/**
 * Invalid test model without HasCrud trait
 */
class InvalidTestModelNoTrait extends Model
{
    protected $table = 'test_models';
}

/**
 * Class that is not a model
 */
class NotAModel
{
    // Not a model
}
