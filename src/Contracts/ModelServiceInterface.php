<?php

namespace Trinavo\TrinaCrud\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Trinavo\TrinaCrud\Models\ModelSchema;

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
     * Get the authorized attributes for a model
     * 
     * @param string|Model $modelName
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


    /**
     * Verify if the model is a valid Crud model for security purposes
     * 
     * @param string $modelClass
     * @return bool
     */
    public function verifyModel(string $modelClass): bool;


    /**
     * Get the schema of all models
     * 
     * @return array
     */
    public function getSchema(): array;

    /**
     * Parse a model file to extract model information
     * 
     * @param string $file The path to the model file
     * @param string|null $namespace The namespace of the model
     * @return ModelSchema|null
     */
    public function parseModelFile(string $file, ?string $namespace = null): ?ModelSchema;


    /**
     * Filter columns based on user permissions
     * 
     * @param string|Model $model
     * @param string $action
     * @param array|null $requestedAttributes
     * @return array
     */
    public function filterAuthorizedAttributes(string|Model $model, string $action, ?array $requestedAttributes = null): array;
}
