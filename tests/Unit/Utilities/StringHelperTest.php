<?php

namespace FirasSaidi\TrinaCrud\Tests\Unit\Utilities;

use FirasSaidi\TrinaCrud\Tests\TestCase;
use Trinavo\TrinaCrud\Utilities\StringHelper;

class StringHelperTest extends TestCase
{
    public function testToCamelCase()
    {
        // Create a new instance of the StringHelper class
        $helper = new StringHelper();
        
        // Test converting snake_case to camelCase
        $this->assertEquals('camelCase', $helper->toCamelCase('camel_case'));
        $this->assertEquals('camelCaseString', $helper->toCamelCase('camel_case_string'));
        
        // Test that it doesn't change already camelCase strings
        $this->assertEquals('camelCase', $helper->toCamelCase('camelCase'));
        
        // Test with uppercase first letter
        $this->assertEquals('CamelCase', $helper->toCamelCase('camel_case', true));
    }
    
    public function testToSnakeCase()
    {
        // Create a new instance of the StringHelper class
        $helper = new StringHelper();
        
        // Test converting camelCase to snake_case
        $this->assertEquals('camel_case', $helper->toSnakeCase('camelCase'));
        $this->assertEquals('camel_case_string', $helper->toSnakeCase('camelCaseString'));
        
        // Test that it doesn't change already snake_case strings
        $this->assertEquals('snake_case', $helper->toSnakeCase('snake_case'));
    }
} 