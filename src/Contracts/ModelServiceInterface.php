<?php

namespace Trinavo\TrinaCrud\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;

interface ModelServiceInterface
{
    public function getModelRecords(
        string $modelName,
        array $columns = [],
        ?array $with = null,
        array $relationColumns = [],
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator;

    public function getModelRecord(
        string $modelName,
        int $id,
        array $columns = [],
        ?array $with = null,
        array $relationColumns = []
    ): Model;


    /**
     * Create a new model record
     * 
     * @param string $modelName
     * @param array $data
     * @return Model
     */
    public function createModelRecord(string $modelName, array $data): Model;

    /**
     * Update an existing model record
     * 
     * @param string $modelName
     * @param int $id
     * @param array $data
     * @return Model
     */
    public function updateModelRecord(string $modelName, int $id, array $data): Model;

    /**
     * Delete a model record
     * 
     * @param string $modelName
     * @param int $id
     * @return bool
     */
    public function deleteModelRecord(string $modelName, int $id): bool;


    /**
     * Check if the user has permission to perform an action on a model
     * 
     * @param string $modelName
     * @param string $action
     * @return bool
     */
    public function hasModelPermission(string $modelName, string $action): bool;

    /**
     * Check if the user has permission to perform an action on a column
     * 
     * @param string $modelName
     * @param string $columnName
     * @param string $action
     * @return bool
     */
    public function hasColumnPermission(string $modelName, string $columnName, string $action): bool;

    /**
     * Scope the query to only include authorized records
     * 
     * @param Builder|Relation $query
     * @param string $modelName
     * @return Builder|Relation
     */
    public function scopeAuthorizedRecords(Builder|Relation $query, string $modelName): Builder|Relation;

    /**
     * Filter the columns to only include authorized columns
     * 
     * @param string $modelName
     * @param array|null $requestedColumns
     * @return array
     */
    public function filterAuthorizedColumns(string $modelName, ?array $requestedColumns = null): array;

    /**
     * Load the authorized relations
     * 
     * @param Builder $query
     * @param string $modelName
     * @param array $relations
     * @param array $columnsByRelation
     * @return Builder
     */
    public function loadAuthorizedRelations(
        Builder|Relation $query,
        string $modelName,
        array $relations,
        array $columnsByRelation = []
    ): Builder|Relation;

    /**
     * Apply the authorized filters
     * 
     * @param Builder $query
     * @param string $modelName
     * @param array $filters
     * @return Builder
     */
    public function applyAuthorizedFilters(Builder|Relation $query, string $modelName, array $filters): Builder|Relation;

    /**
     * Check if the model exists
     * 
     * @param string $classOrModelName
     * @return bool
     */
    public function isModelExists(string $classOrModelName): bool;

    /**
     * Find the TrinaCrudModel
     * 
     * @param string $classOrModelName
     * @return object|null
     */
    public function findTrinaCrudModel(string $classOrModelName): ?object;

    /**
     * Make the model name from the class name
     * 
     * @param string $className
     * @return string
     */
    public function makeModelNameFromClass(string $className): string;
}
