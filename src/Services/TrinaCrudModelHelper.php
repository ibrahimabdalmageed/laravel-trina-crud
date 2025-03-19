<?php

namespace Trinavo\TrinaCrud\Services;

use Trinavo\TrinaCrud\Models\TrinaCrudModel;
use Illuminate\Support\Str;

class TrinaCrudModelHelper
{
    /**
     * Find a TrinaCrudModel by name
     *
     * @param string $modelName The name of the model
     * @return TrinaCrudModel|null
     */
    public function findTrinaCrudModel(string $classOrModelName): ?object
    {
        if (Str::contains($classOrModelName, '\\')) {
            $modelName = $this->makeModelNameFromClass($classOrModelName);
        } else {
            $modelName = $classOrModelName;
        }

        return TrinaCrudModel::where('class_name', $classOrModelName)
            ->orWhere('model_name', $modelName)->first();
    }


    public function isModelExists(string $classOrModelName): bool
    {
        if (Str::contains($classOrModelName, '\\')) {
            $modelName = $this->makeModelNameFromClass($classOrModelName);
        } else {
            $modelName = $classOrModelName;
        }

        return TrinaCrudModel::where('class_name', $classOrModelName)
            ->orWhere('model_name', $modelName)->exists();
    }

    public function makeModelNameFromClass(string $className): string
    {
        return Str::lower(Str::replace('\\', '_', $className));
    }
}
