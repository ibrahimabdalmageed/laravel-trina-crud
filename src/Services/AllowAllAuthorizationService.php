<?php

namespace Trinavo\TrinaCrud\Services;

use Illuminate\Database\Eloquent\Builder;
use Trinavo\TrinaCrud\Contracts\TrinaCrudAuthorizationServiceInterface;

class AllowAllAuthorizationService implements TrinaCrudAuthorizationServiceInterface
{

    /**
     * @var TrinaCrudModelHelper
     */
    protected $modelHelper;

    public function __construct(TrinaCrudModelHelper $modelHelper)
    {
        $this->modelHelper = $modelHelper;
    }

    /**
     * Check if the user has permission to access a model
     * Always returns true for testing purposes
     *
     * @param string $modelName The name of the model
     * @param string $action The action (view, create, update, delete)
     * @return bool
     */
    public function hasModelPermission(string $modelName, string $action = 'view'): bool
    {
        return true;
    }

    /**
     * Check if the user has permission to access a column
     * Always returns true for testing purposes
     *
     * @param string $modelName The name of the model
     * @param string $columnName The name of the column
     * @param string $action The action (view, update)
     * @return bool
     */
    public function hasColumnPermission(string $modelName, string $columnName, string $action = 'view'): bool
    {
        return true;
    }

    /**
     * Filter a query to only include records the user has access to
     * No filtering for testing purposes
     *
     * @param Builder $query The query builder
     * @param string $modelName The name of the model
     * @return Builder
     */
    public function scopeAuthorizedRecords(Builder $query, string $modelName): Builder
    {
        // No filtering, return all records
        return $query;
    }

    /**
     * Filter columns based on user permissions
     * Returns all requested columns for testing purposes
     *
     * @param string $modelName The name of the model
     * @param array|null $requestedColumns The columns requested by the user
     * @return array
     */
    public function filterAuthorizedColumns(string $modelName, ?array $requestedColumns = null): array
    {
        if ($requestedColumns) {
            return $requestedColumns;
        }

        // Get all columns for the model
        $trinaCrudModel = $this->modelHelper->findTrinaCrudModel($modelName);

        if (!$trinaCrudModel) {
            return [];
        }

        return $trinaCrudModel->columns()->pluck('column_name')->toArray();
    }

    /**
     * Process 'with' relationships and ensure proper authorization
     * Loads all relationships without filtering for testing purposes
     *
     * @param Builder $query The query builder
     * @param string $modelName The name of the model
     * @param array $relations The relations to load
     * @param array $columnsByRelation Optional columns to select for each relation
     * @return Builder
     */
    public function loadAuthorizedRelations(Builder $query, string $modelName, array $relations, array $columnsByRelation = []): Builder
    {
        // Simply load the relations without authorization filtering
        foreach ($relations as $relation) {
            $columns = $columnsByRelation[$relation] ?? null;

            if (!empty($columns)) {
                $query->with([$relation => function ($q) use ($columns) {
                    // Always include the primary key
                    $columns[] = 'id';
                    $q->select(array_unique($columns));
                }]);
            } else {
                $query->with($relation);
            }
        }

        return $query;
    }

    /**
     * Apply filters to a query with permission checks
     * Applies all filters without permission checks for testing
     *
     * @param Builder $query The query builder
     * @param string $modelName The name of the model
     * @param array $filters The filters to apply
     * @return Builder
     */
    public function applyAuthorizedFilters(Builder $query, string $modelName, array $filters): Builder
    {
        foreach ($filters as $column => $value) {
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
}
