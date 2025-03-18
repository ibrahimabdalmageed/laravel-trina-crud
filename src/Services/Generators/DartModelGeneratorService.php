<?php

namespace Trinavo\TrinaCrud\Services\Generators;

class DartModelGeneratorService
{
    /**
     * Generate Dart class code for a given model name.
     *
     * @param string $modelName
     * @return string
     */
    public function generateDartClass(string $modelName): string
    {
        $className = ucfirst($modelName);
        return '';
    }
}
