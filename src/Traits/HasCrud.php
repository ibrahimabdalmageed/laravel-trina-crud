<?php

namespace Trinavo\TrinaCrud\Traits;

use Illuminate\Support\Facades\Schema;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Enums\CrudAction;

trait HasCrud
{
    /**
     * Get the fillable attributes for a specific CRUD action
     * 
     * @param CrudAction $action The CRUD action
     * @return array
     */
    public function getCrudFillable(CrudAction $action): array
    {
        $authorizationService = app(AuthorizationServiceInterface::class);

        $fillable = $this->getFillable();
        $filteredFillable = [];
        foreach ($fillable as $field) {
            if ($authorizationService->authHasAttributePermission(get_class($this), $field, $action)) {
                $filteredFillable[] = $field;
            }
        }

        return $filteredFillable;
    }

    /**
     * Get the validation rules for a specific CRUD action
     * 
     * @param CrudAction $action The CRUD action
     * @return array
     */
    public function getCrudRules(CrudAction $action): array
    {
        $rules = [];
        $table = $this->getTable();
        $dbColumns = collect(Schema::getColumns($table));
        $columns = Schema::getColumnListing($table);
        $fillable = $this->getCrudFillable($action);
        $primaryKey = $this->getKeyName();

        // Only process fillable columns that are authorized for this action
        $columnsToProcess = array_intersect($columns, $fillable);

        foreach ($columnsToProcess as $column) {
            // Skip primary key for CREATE and UPDATE operations
            // Primary keys are typically auto-incremented or managed by the database
            if ($column === $primaryKey && in_array($action, [CrudAction::CREATE, CrudAction::UPDATE])) {
                continue;
            }

            // Get column info directly from Schema::getColumns
            $columnInfo = $dbColumns->where('name', $column)->first();
            if ($columnInfo) {
                $rules[$column] = $this->buildValidationRules($columnInfo, $action);
            }
        }

        return $rules;
    }

    /**
     * Build validation rules for a specific column based on its database metadata
     * 
     * @param array $columnInfo The column information from Schema::getColumns
     * @param CrudAction $action The CRUD action
     * @return string The validation rule string
     */
    protected function buildValidationRules(array $columnInfo, CrudAction $action): string
    {
        $rules = [];

        // Determine if the field is required based on nullability and default value
        $isNullable = $columnInfo['nullable'];
        $hasDefault = $columnInfo['default'] !== null;
        $isAutoIncrement = isset($columnInfo['auto_increment']) && $columnInfo['auto_increment'] === true;

        // Fields are only required for CREATE if they are not nullable AND have no default value AND are not auto-increment
        if ($action === CrudAction::CREATE && !$isNullable && !$hasDefault && !$isAutoIncrement) {
            $rules[] = 'required';
        } elseif ($action === CrudAction::UPDATE) {
            $rules[] = 'sometimes';
        } else {
            // For fields that are nullable OR have a default value, they're not required
            $rules[] = ($isNullable || $hasDefault) ? 'nullable' : 'required';
        }

        // Detect boolean fields - both native boolean and MySQL tinyint(1)
        $isBoolean = false;
        if ($columnInfo['type_name'] === 'boolean' || $columnInfo['type_name'] === 'bool') {
            $isBoolean = true;
        } elseif ($columnInfo['type_name'] === 'tinyint' && strpos($columnInfo['type'], 'tinyint(1)') !== false) {
            $isBoolean = true;
        }

        // Add type-specific validation rules
        if ($isBoolean) {
            $rules[] = 'boolean';
        } else {
            switch ($columnInfo['type_name']) {
                case 'bigint':
                case 'int':
                case 'integer':
                case 'smallint':
                case 'tinyint':
                    $rules[] = 'integer';
                    break;

                case 'decimal':
                case 'double':
                case 'float':
                    $rules[] = 'numeric';

                    // Extract precision and scale from type if available
                    if (
                        preg_match('/decimal\((\d+),\s*(\d+)\)/', $columnInfo['type'], $matches) ||
                        preg_match('/float\((\d+),\s*(\d+)\)/', $columnInfo['type'], $matches)
                    ) {
                        $precision = (int)$matches[1];
                        $scale = (int)$matches[2];

                        if ($scale > 0) {
                            $rules[] = "decimal:$scale";
                        }
                    }
                    break;

                case 'date':
                    $rules[] = 'date';
                    break;

                case 'datetime':
                case 'timestamp':
                    $rules[] = 'date_format:Y-m-d H:i:s';
                    break;

                case 'char':
                case 'varchar':
                case 'string':
                case 'text':
                    $rules[] = 'string';

                    // Extract string length constraint if available
                    if (
                        preg_match('/varchar\((\d+)\)/', $columnInfo['type'], $matches) ||
                        preg_match('/char\((\d+)\)/', $columnInfo['type'], $matches)
                    ) {
                        $length = (int)$matches[1];
                        if ($length > 0) {
                            $rules[] = "max:$length";
                        }
                    }
                    break;

                case 'json':
                    $rules[] = 'json';
                    break;
            }
        }

        return implode('|', $rules);
    }
}
