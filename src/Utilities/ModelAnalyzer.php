<?php

namespace Trinavo\TrinaCrud\Utilities;

use ReflectionClass;
use ReflectionProperty;

class ModelAnalyzer
{
    /**
     * Get all fillable properties from a model class
     *
     * @param string $modelClass The fully qualified class name of the model
     * @return array
     */
    public function getFillableProperties(string $modelClass): array
    {
        if (!class_exists($modelClass)) {
            return [];
        }
        
        $reflection = new ReflectionClass($modelClass);
        
        // Get the fillable property
        if (!$reflection->hasProperty('fillable')) {
            return [];
        }
        
        $fillableProperty = $reflection->getProperty('fillable');
        $fillableProperty->setAccessible(true);
        
        // Create a new instance of the model to get the fillable property value
        $model = $reflection->newInstanceWithoutConstructor();
        
        return $fillableProperty->getValue($model) ?? [];
    }
    
    /**
     * Get all guarded properties from a model class
     *
     * @param string $modelClass The fully qualified class name of the model
     * @return array
     */
    public function getGuardedProperties(string $modelClass): array
    {
        if (!class_exists($modelClass)) {
            return [];
        }
        
        $reflection = new ReflectionClass($modelClass);
        
        // Get the guarded property
        if (!$reflection->hasProperty('guarded')) {
            return [];
        }
        
        $guardedProperty = $reflection->getProperty('guarded');
        $guardedProperty->setAccessible(true);
        
        // Create a new instance of the model to get the guarded property value
        $model = $reflection->newInstanceWithoutConstructor();
        
        return $guardedProperty->getValue($model) ?? [];
    }
    
    /**
     * Check if a model uses a specific trait
     *
     * @param string $modelClass The fully qualified class name of the model
     * @param string $trait The fully qualified trait name
     * @return bool
     */
    public function usesTrait(string $modelClass, string $trait): bool
    {
        if (!class_exists($modelClass)) {
            return false;
        }
        
        $traits = class_uses_recursive($modelClass);
        
        return isset($traits[$trait]);
    }
} 