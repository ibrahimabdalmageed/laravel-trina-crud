<?php

namespace FirasSaidi\TrinaCrud\Tests\Feature\Http\Requests;

use Trinavo\TrinaCrud\Tests\TestCase;
use Trinavo\TrinaCrud\Http\Requests\ModelsController\ValidateTrinaCrudModelIndexRequest;

class ValidateTrinaCrudModelIndexRequestTest extends TestCase
{

    public function testRulesValidatesModelExists()
    {
        // This test can be run without Laravel's full request validation
        $request = new ValidateTrinaCrudModelIndexRequest();

        // Act
        $rules = $request->rules();

        // Assert
        $this->assertArrayHasKey('model', $rules);
        $this->assertIsArray($rules['model']);
        $this->assertContains('required', $rules['model']);
        $this->assertContains('string', $rules['model']);
    }

    public function testMessagesReturnsValidationMessages()
    {
        // This test can be run without Laravel's full request validation
        $request = new ValidateTrinaCrudModelIndexRequest();

        // Act
        $messages = $request->messages();

        // Assert
        $this->assertArrayHasKey('model.required', $messages);
        $this->assertArrayHasKey('model.string', $messages);
        $this->assertEquals('The model field is required.', $messages['model.required']);
        $this->assertEquals('The model must be a valid string.', $messages['model.string']);
    }
}
