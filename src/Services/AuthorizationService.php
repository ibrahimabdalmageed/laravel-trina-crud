<?php

namespace Trinavo\TrinaCrud\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Trinavo\TrinaCrud\Models\TrinaCrudModel;
use Trinavo\TrinaCrud\Traits\Ownable;

class AuthorizationService
{
    /**
     * Check if the user has permission to access a model
     *
     * @param string $modelName The name of the model
     * @param string $action The action (view, create, update, delete)
     * @return bool
     */
    public function hasModelPermission(string $modelName, string $action = 'view'): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Convert action to Laravel's standard ability names
        $ability = match ($action) {
            'view' => 'viewAny',
            'create' => 'create',
            'update' => 'update',
            'delete' => 'delete',
            default => $action
        };

        // First check if there's a policy for the model
        $modelClass = $this->resolveModelClass($modelName);
        if ($modelClass && class_exists($modelClass)) {
            return Gate::allows($ability, $modelClass);
        }

        // Fallback to a generic permission check
        return Gate::allows("{$action}-{$modelName}");
    }

    /**
     * Check if the user has permission to access a column
     *
     * @param string $modelName The name of the model
     * @param string $columnName The name of the column
     * @param string $action The action (view, update)
     * @return bool
     */
    public function hasColumnPermission(string $modelName, string $columnName, string $action = 'view'): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // First check if there's a specific column permission
        if (Gate::has("{$action}-{$modelName}-{$columnName}")) {
            return Gate::allows("{$action}-{$modelName}-{$columnName}");
        }

        // Fallback to model permission
        return $this->hasModelPermission($modelName, $action);
    }

    /**
     * Filter a query to only include records the user has access to
     *
     * @param Builder $query The query builder
     * @param string $modelName The name of the model
     * @return Builder
     */
    public function scopeAuthorizedRecords(Builder $query, string $modelName): Builder
    {
        $user = Auth::user();

        if (!$user) {
            return $query->whereRaw('1 = 0'); // No records if not authenticated
        }

        // Check if model uses Ownable trait
        $modelClass = $this->resolveModelClass($modelName);
        if (!$modelClass || !class_exists($modelClass)) {
            return $query;
        }

        $query->ownedBy($user);

        return $query;
    }

    /**
     * Filter columns based on user permissions
     *
     * @param string $modelName The name of the model
     * @param array|null $requestedColumns The columns requested by the user
     * @return array
     */
    public function filterAuthorizedColumns(string $modelName, ?array $requestedColumns = null): array
    {
        // Get all columns for the model
        $trinaCrudModel = TrinaCrudModel::where('name', $modelName)->first();

        if (!$trinaCrudModel) {
            return $requestedColumns ?? [];
        }

        $allColumns = $trinaCrudModel->columns()->pluck('column_name')->toArray();

        // If no columns requested, use all columns
        $columnsToCheck = $requestedColumns ?? $allColumns;

        // Filter columns based on permissions
        return array_filter($columnsToCheck, function ($column) use ($modelName) {
            return $this->hasColumnPermission($modelName, $column);
        });
    }

    /**
     * Process 'with' relationships and ensure proper authorization
     *
     * @param Builder $query The query builder
     * @param string $modelName The name of the model
     * @param array $relations The relations to load
     * @param array $columnsByRelation Optional columns to select for each relation
     * @return Builder
     */
    public function loadAuthorizedRelations(Builder $query, string $modelName, array $relations, array $columnsByRelation = []): Builder
    {
        foreach ($relations as $relation) {
            // Get the related model name
            $relatedModelName = $this->getRelatedModelName($modelName, $relation);

            // Check if user has permission to view the related model
            if (!$this->hasModelPermission($relatedModelName, 'view')) {
                continue;
            }

            // Get authorized columns for the relation
            $columns = $columnsByRelation[$relation] ?? null;
            $authorizedColumns = $this->filterAuthorizedColumns($relatedModelName, $columns);

            // Load relation with ownership scope and column restrictions
            $query->with([$relation => function ($q) use ($relatedModelName, $authorizedColumns) {
                // Apply ownership filter
                $this->scopeAuthorizedRecords($q, $relatedModelName);

                // Select only authorized columns if specified
                if (!empty($authorizedColumns)) {
                    // Always include the primary key
                    $authorizedColumns[] = 'id';
                    $q->select(array_unique($authorizedColumns));
                }
            }]);
        }

        return $query;
    }

    /**
     * Apply filters to a query with permission checks
     *
     * @param Builder $query The query builder
     * @param string $modelName The name of the model
     * @param array $filters The filters to apply
     * @return Builder
     */
    public function applyAuthorizedFilters(Builder $query, string $modelName, array $filters): Builder
    {
        foreach ($filters as $column => $value) {
            // Skip if user doesn't have permission to filter by this column
            if (!$this->hasColumnPermission($modelName, $column)) {
                continue;
            }

            // Handle different filter types
            if (is_array($value)) {
                // Check for special operators
                if (isset($value['operator'])) {
                    $this->applyOperatorFilter($query, $column, $value);
                } else {
                    // Default to "in" operator for arrays
                    $query->whereIn($column, $value);
                }
            } else {
                // Simple equality filter
                $query->where($column, $value);
            }
        }

        return $query;
    }

    /**
     * Apply a filter with a specific operator
     *
     * @param Builder $query The query builder
     * @param string $column The column to filter
     * @param array $filter The filter configuration
     * @return Builder
     */
    protected function applyOperatorFilter(Builder $query, string $column, array $filter): Builder
    {
        $operator = $filter['operator'] ?? '=';
        $value = $filter['value'] ?? null;

        switch ($operator) {
            case 'between':
                if (is_array($value) && count($value) === 2) {
                    $query->whereBetween($column, $value);
                }
                break;
            case 'not_in':
                if (is_array($value)) {
                    $query->whereNotIn($column, $value);
                }
                break;
            case 'like':
                $query->where($column, 'like', "%{$value}%");
                break;
            case 'not':
            case '!=':
                $query->where($column, '!=', $value);
                break;
            case '>':
            case '<':
            case '>=':
            case '<=':
                $query->where($column, $operator, $value);
                break;
            default:
                $query->where($column, $operator, $value);
        }

        return $query;
    }

    /**
     * Resolve a model class from its name
     *
     * @param string $modelName The name of the model
     * @return string|null
     */
    protected function resolveModelClass(string $modelName): ?string
    {
        // Try to find the model in the TrinaCrudModel table
        $trinaCrudModel = TrinaCrudModel::where('name', $modelName)->first();

        if ($trinaCrudModel && !empty($trinaCrudModel->class_name)) {
            return $trinaCrudModel->class_name;
        }

        // Fallback to guessing the namespace
        $modelClass = 'App\\Models\\' . Str::studly($modelName);

        if (class_exists($modelClass)) {
            return $modelClass;
        }

        return null;
    }

    /**
     * Get the related model name from a relation
     *
     * @param string $modelName The parent model name
     * @param string $relation The relation name
     * @return string
     */
    protected function getRelatedModelName(string $modelName, string $relation): string
    {
        // Try to determine the related model from the relation name
        // This is a simplistic approach - in a real implementation, you might
        // need to inspect the actual relation method

        // Convert camelCase to StudlyCase for model name
        return Str::studly(Str::singular($relation));
    }
}
