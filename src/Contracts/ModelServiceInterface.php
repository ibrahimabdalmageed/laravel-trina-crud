<?php

namespace Trinavo\TrinaCrud\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Trinavo\TrinaCrud\Enums\CrudAction;
use Trinavo\TrinaCrud\Models\ModelSchema;
use Trinavo\TrinaCrud\Traits\HasCrud;

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
     * @return Model|null
     */
    public function find(
        string $modelName,
        int $id,
        array $attributes = [],
        ?array $with = null,
        array $relationAttributes = []
    ): Model|null;


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
     * @param CrudAction $action
     * @return array
     */
    public function getAuthorizedAttributes(string|Model $modelName, CrudAction $action): array;

    /**
     * Scope the query to only include authorized records
     * 
     * @param Builder|Relation $query
     * @param string|Model $model
     * @param CrudAction $action
     * @return Builder|Relation
     */
    public function scopeAuthorizedRecords(Builder|Relation $query, string|Model $model, CrudAction $action): Builder|Relation;

    /**
     * Load the authorized relations
     * 
     * @param Builder|Relation $query
     * @param string|Model $model
     * @param array $relations
     * @param array $columnsByRelation
     * @param CrudAction $action
     * @return Builder|Relation
     */
    public function loadAuthorizedRelations(
        Builder|Relation $query,
        string|Model $model,
        array $relations,
        array $attributesByRelation = [],
        CrudAction $action = CrudAction::READ
    ): Builder|Relation;

    /**
     * Apply filters to a query with permission checks
     * 
     * @param Builder|Relation $query
     * @param string|Model $model
     * @param array $filters
     * @param CrudAction $action
     * @return Builder|Relation
     */
    public function applyAuthorizedFilters(
        Builder|Relation $query,
        string|Model $model,
        array $filters,
        CrudAction $action
    ): Builder|Relation;


    /**
     * Get the model
     * 
     * @param string|Model $modelName
     * @return Model|HasCrud|null
     */
    public function getModel(string|Model $modelName): Model|HasCrud|null;


    /**
     * Verify if the model is a valid Crud model for security purposes
     * 
     * @param string $modelClass The model class to verify, you can use . instead of \ for namespace
     * @return bool
     */
    public function verifyModel(string $modelClass): bool;


    /**
     * Get the schema of all models
     * 
     * @param string|null $modelName
     * @return ModelSchema[]
     */
    public function getSchema(?string $modelName = null): array;

    /**
     * Parse a model file to extract model information
     * 
     * @param string $file The path to the model file
     * @param string|null $namespace The namespace of the model
     * @return ModelSchema|null
     */
    public function parseModelFile(string $file, ?string $namespace = null): ?ModelSchema;
}
