<?php

namespace Trinavo\TrinaCrud\Tests\Unit\Utilities;

use Trinavo\TrinaCrud\Tests\TestCase;
use Trinavo\TrinaCrud\Utilities\ModelAnalyzer;

class ModelAnalyzerTest extends TestCase
{
    protected $analyzer;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->analyzer = new ModelAnalyzer();
    }
    
    public function testGetFillablePropertiesReturnsEmptyArrayForNonExistentClass()
    {
        $result = $this->analyzer->getFillableProperties('NonExistentClass');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
    
    public function testGetGuardedPropertiesReturnsEmptyArrayForNonExistentClass()
    {
        $result = $this->analyzer->getGuardedProperties('NonExistentClass');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
    
    public function testUsesTraitReturnsFalseForNonExistentClass()
    {
        $result = $this->analyzer->usesTrait('NonExistentClass', 'SomeTrait');
        $this->assertFalse($result);
    }
    
    public function testGetFillablePropertiesWithTestModel()
    {
        // Define a test model class
        eval('
            namespace Tests\Unit\Utilities;
            
            class TestModel {
                protected $fillable = ["name", "email", "password"];
            }
        ');
        
        $result = $this->analyzer->getFillableProperties('Tests\Unit\Utilities\TestModel');
        $this->assertIsArray($result);
        $this->assertEquals(['name', 'email', 'password'], $result);
    }
    
    public function testGetGuardedPropertiesWithTestModel()
    {
        // Define a test model class
        eval('
            namespace Tests\Unit\Utilities;
            
            class TestModelWithGuarded {
                protected $guarded = ["id", "created_at", "updated_at"];
            }
        ');
        
        $result = $this->analyzer->getGuardedProperties('Tests\Unit\Utilities\TestModelWithGuarded');
        $this->assertIsArray($result);
        $this->assertEquals(['id', 'created_at', 'updated_at'], $result);
    }
    
    public function testUsesTraitWithTestModel()
    {
        // Define a test trait
        eval('
            namespace Tests\Unit\Utilities;
            
            trait TestTrait {
                public function testMethod() {
                    return "test";
                }
            }
        ');
        
        // Define a test model that uses the trait
        eval('
            namespace Tests\Unit\Utilities;
            
            class TestModelWithTrait {
                use TestTrait;
            }
        ');
        
        $result = $this->analyzer->usesTrait('Tests\Unit\Utilities\TestModelWithTrait', 'Tests\Unit\Utilities\TestTrait');
        $this->assertTrue($result);
    }
} 