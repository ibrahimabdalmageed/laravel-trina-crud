<?php

namespace Trinavo\TrinaCrud\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;

interface ModelServiceInterface
{
    /**
     * Get all model records
     * 
     * @param string $modelName
     * @param array $attributes
     * @param array|null $with
     * @param array $relationAttributes
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function all(
        string $modelName,
        array $attributes = [],
        ?array $with = null,
        array $relationAttributes = [],
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator;

    /**
     * Get a model record by ID
     * 
     * @param string $modelName
     * @param int $id
     * @param array $attributes
     * @param array|null $with
     * @param array $relationAttributes
     * @return Model
     */
    public function find(
        string $modelName,
        int $id,
        array $attributes = [],
        ?array $with = null,
        array $relationAttributes = []
    ): Model;


    /**
     * Create a new model record
     * 
     * @param string $modelName
     * @param array $data
     * @return Model
     */
    public function create(string $modelName, array $data): Model;

    /**
     * Update an existing model record
     * 
     * @param string $modelName
     * @param int $id
     * @param array $data
     * @return Model
     */
    public function update(string $modelName, int $id, array $data): Model;

    /**
     * Delete a model record
     * 
     * @param string $modelName
     * @param int $id
     * @return bool
     */
    public function delete(string $modelName, int $id): bool;


    /**
     * Check if the user has permission to perform an action on a model
     * 
     * @param string $modelName
     * @param string $action
     * @return bool
     */
    public function hasModelPermission(string $modelName, string $action): bool;

    /**
     * Get the authorized attributes for a model
     * 
     * @param string $modelName
     * @param string $action
     * @return array
     */
    public function getAuthorizedAttributes(string|Model $modelName, string $action): array;

    /**
     * Scope the query to only include authorized records
     * 
     * @param Builder|Relation $query
     * @param string|Model $model
     * @param string $action
     * @return Builder|Relation
     */
    public function scopeAuthorizedRecords(Builder|Relation $query, string|Model $model, string $action): Builder|Relation;

    /**
     * Load the authorized relations
     * 
     * @param Builder|Relation $query
     * @param string|Model $model
     * @param array $relations
     * @param array $columnsByRelation
     * @return Builder|Relation
     */
    public function loadAuthorizedRelations(
        Builder|Relation $query,
        string|Model $model,
        array $relations,
        array $attributesByRelation = []
    ): Builder|Relation;

    /**
     * Apply filters to a query with permission checks
     * 
     * @param Builder|Relation $query
     * @param string|Model $model
     * @param array $filters
     * @param string $action
     * @return Builder|Relation
     */
    public function applyAuthorizedFilters(
        Builder|Relation $query,
        string|Model $model,
        array $filters,
        string $action
    ): Builder|Relation;

    /**
     * Get the model
     * 
     * @param string|Model $modelName
     * @return ?Model
     */
    public function getModel(string|Model $modelName): ?Model;
}
