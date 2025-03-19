<?php

namespace Trinavo\TrinaCrud\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface TrinaCrudAuthorizationServiceInterface
{
    /**
     * Check if the user has permission to access a model
     *
     * @param string $modelName The name of the model
     * @param string $action The action (view, create, update, delete)
     * @return bool
     */
    public function hasModelPermission(string $modelName, string $action = 'view'): bool;

    /**
     * Check if the user has permission to access a column
     *
     * @param string $modelName The name of the model
     * @param string $columnName The name of the column
     * @param string $action The action (view, update)
     * @return bool
     */
    public function hasColumnPermission(string $modelName, string $columnName, string $action = 'view'): bool;

    /**
     * Filter a query to only include records the user has access to
     *
     * @param Builder $query The query builder
     * @param string $modelName The name of the model
     * @return Builder
     */
    public function scopeAuthorizedRecords(Builder $query, string $modelName): Builder;

    /**
     * Filter columns based on user permissions
     *
     * @param string $modelName The name of the model
     * @param array|null $requestedColumns The columns requested by the user
     * @return array
     */
    public function filterAuthorizedColumns(string $modelName, ?array $requestedColumns = null): array;

    /**
     * Process 'with' relationships and ensure proper authorization
     *
     * @param Builder $query The query builder
     * @param string $modelName The name of the model
     * @param array $relations The relations to load
     * @param array $columnsByRelation Optional columns to select for each relation
     * @return Builder
     */
    public function loadAuthorizedRelations(Builder $query, string $modelName, array $relations, array $columnsByRelation = []): Builder;

    /**
     * Apply filters to a query with permission checks
     *
     * @param Builder $query The query builder
     * @param string $modelName The name of the model
     * @param array $filters The filters to apply
     * @return Builder
     */
    public function applyAuthorizedFilters(Builder $query, string $modelName, array $filters): Builder;
}
