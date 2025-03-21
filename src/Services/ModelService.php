<?php

namespace Trinavo\TrinaCrud\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Contracts\ModelServiceInterface;
use Trinavo\TrinaCrud\Contracts\OwnershipServiceInterface;
use Trinavo\TrinaCrud\Enums\CrudAction;
use Trinavo\TrinaCrud\Models\ModelSchema;
use Trinavo\TrinaCrud\Traits\HasCrud;

class ModelService implements ModelServiceInterface
{
    protected AuthorizationServiceInterface $authorizationService;
    protected OwnershipServiceInterface $ownershipService;

    public function __construct(
        AuthorizationServiceInterface $authorizationService,
        OwnershipServiceInterface $ownershipService
    ) {
        $this->authorizationService = $authorizationService;
        $this->ownershipService = $ownershipService;
    }

    /**
     * Get a paginated list of model records with filtering and authorization
     *
     * @param string $modelName The name of the model
     * @param array $attributes The columns to select
     * @param array|null $with The relations to load
     * @param array $relationAttributes The columns to select for each relation
     * @param array $filters The filters to apply
     * @param int $perPage The number of records per page
     * @return LengthAwarePaginator
     * @throws NotFoundHttpException
     */
    public function all(
        string $modelName,
        array $attributes = [],
        ?array $with = null,
        array $relationAttributes = [],
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $user = $this->authorizationService->getUser();

        if ($user) {
            if (!$this->authorizationService->hasModelPermission($modelName, CrudAction::READ)) {
                throw new NotFoundHttpException('You are not authorized to read this model');
            }
        } else {
            if (!$this->authorizationService->hasModelPermission($modelName, CrudAction::READ_ANY)) {
                throw new NotFoundHttpException('You are not authorized to read this model');
            }
        }

        // Find the model
        $model = $this->getModel($modelName);

        if (!$model) {
            throw new NotFoundHttpException('Model not found');
        }

        // Create a new query
        $query = $model->query();

        // Apply ownership filtering
        $query = $this->scopeAuthorizedRecords($query, $model, CrudAction::READ);

        // Filter columns based on permissions
        $authorizedAttributes  = $this->filterAuthorizedAttributes($model, CrudAction::READ, $attributes);

        // Select only authorized columns if specified
        if (!empty($authorizedAttributes)) {
            $query->select($authorizedAttributes);
        }

        // Apply filters
        $query = $this->applyAuthorizedFilters($query, $model, $filters, CrudAction::READ);

        // Load relations if specified
        if (!empty($with)) {
            $query = $this->loadAuthorizedRelations($query, $model, $with, $relationAttributes, CrudAction::READ);
        }

        // Paginate the results
        return $query->paginate($perPage);
    }

    /**
     * Get a single model record by ID with authorization
     *
     * @param string $modelName The name of the model
     * @param int $id The ID of the record
     * @param array $attributes The columns to select
     * @param array|null $with The relations to load
     * @param array $relationAttributes The columns to select for each relation
     * @return Model
     * @throws NotFoundHttpException
     */
    public function find(
        string $modelName,
        int $id,
        array $attributes = [],
        ?array $with = null,
        array $relationAttributes = []
    ): Model {
        $user = $this->authorizationService->getUser();

        if ($user) {
            if (!$this->authorizationService->hasModelPermission($modelName, CrudAction::READ)) {
                throw new NotFoundHttpException('You are not authorized to read this model');
            }
        } else {
            if (!$this->authorizationService->hasModelPermission($modelName, CrudAction::READ_ANY)) {
                throw new NotFoundHttpException('You are not authorized to read this model');
            }
        }

        // Find the model
        $model = $this->getModel($modelName);

        if (!$model) {
            throw new NotFoundHttpException('Model not found');
        }

        // Create a new query
        $query = $model->query();

        // Apply ownership filtering
        $query = $this->scopeAuthorizedRecords($query, $model, CrudAction::READ);

        // Filter columns based on permissions
        $authorizedAttributes  = $this->filterAuthorizedAttributes($model, CrudAction::READ, $attributes);

        // Select only authorized columns if specified
        if (!empty($authorizedAttributes)) {
            $query->select($authorizedAttributes);
        }

        // Load relations if specified
        if (!empty($with)) {
            $query = $this->loadAuthorizedRelations($query, $model, $with, $relationAttributes, CrudAction::READ);
        }

        // Find the record
        $record = $query->find($id);

        if (!$record) {
            throw new NotFoundHttpException('Record not found');
        }

        return $record;
    }

    /**
     * Create a new model record with authorization
     *
     * @param string $modelName The name of the model
     * @param array $data The data to create the record with
     * @return Model
     * @throws NotFoundHttpException
     */
    public function create(string $modelName, array $data): Model
    {
        $user = $this->authorizationService->getUser();

        if ($user) {
            if (!$this->authorizationService->hasModelPermission($modelName, CrudAction::CREATE)) {
                throw new NotFoundHttpException('You are not authorized to create this model');
            }
        } else {
            if (!$this->authorizationService->hasModelPermission($modelName, CrudAction::CREATE_ANY)) {
                throw new NotFoundHttpException('You are not authorized to create this model');
            }
        }

        // Find the model
        $model = $this->getModel($modelName);

        if (!$model) {
            throw new NotFoundHttpException('Model not found');
        }

        // Create a new record
        $record = $model->create($data);

        return $record;
    }

    /**
     * Update a model record with authorization
     *
     * @param string $modelName The name of the model
     * @param int $id The ID of the record
     * @param array $data The data to update the record with
     * @return Model
     * @throws NotFoundHttpException
     */
    public function update(string $modelName, int $id, array $data): Model
    {
        $user = $this->authorizationService->getUser();

        if ($user) {
            if (!$this->authorizationService->hasModelPermission($modelName, CrudAction::UPDATE)) {
                throw new NotFoundHttpException('You are not authorized to update this model');
            }
        } else {
            if (!$this->authorizationService->hasModelPermission($modelName, CrudAction::UPDATE_ANY)) {
                throw new NotFoundHttpException('You are not authorized to update this model');
            }
        }

        // Find the model
        $model = $this->getModel($modelName);

        if (!$model) {
            throw new NotFoundHttpException('Model not found');
        }

        // Create a new query
        $query = $model->query();

        // Apply ownership filtering
        $query = $this->scopeAuthorizedRecords($query, $model, CrudAction::UPDATE);

        // Find the record
        $record = $query->find($id);

        if (!$record) {
            throw new NotFoundHttpException('Record not found');
        }

        // Update the record
        $record->update($data);

        return $record;
    }

    /**
     * Delete a model record with authorization
     *
     * @param string $modelName The name of the model
     * @param int $id The ID of the record
     * @return bool
     * @throws NotFoundHttpException
     */
    public function delete(string $modelName, int $id): bool
    {
        $user = $this->authorizationService->getUser();

        if ($user) {
            if (!$this->authorizationService->hasModelPermission($modelName, CrudAction::DELETE)) {
                throw new NotFoundHttpException('You are not authorized to delete this model');
            }
        } else {
            if (!$this->authorizationService->hasModelPermission($modelName, CrudAction::DELETE_ANY)) {
                throw new NotFoundHttpException('You are not authorized to delete this model');
            }
        }

        // Find the model
        $model = $this->getModel($modelName);

        if (!$model) {
            throw new NotFoundHttpException('Model not found');
        }

        // Create a new query
        $query = $model->query();

        // Apply ownership filtering
        $query = $this->scopeAuthorizedRecords($query, $model, CrudAction::DELETE);

        // Find the record
        $record = $query->find($id);

        if (!$record) {
            throw new NotFoundHttpException('Record not found');
        }

        // Delete the record
        return $record->delete();
    }

    /**
     * Get the authorized attributes for a model
     *
     * @param string|Model $model The model
     * @param CrudAction $action The action (view, update)
     * @return array
     */
    public function getAuthorizedAttributes(string|Model $model, CrudAction $action): array
    {
        if (is_string($model)) {
            $model = $this->getModel($model);
        }

        return $model->getCrudFillable($action);
    }

    /**
     * Filter a query to only include records the user has access to
     *
     * @param Builder $query The query builder
     * @param string $modelName The name of the model
     * @param CrudAction $action The action (view, update)
     * @return Builder
     */
    public function scopeAuthorizedRecords(Builder|Relation $query, string|Model $model, CrudAction $action): Builder|Relation
    {
        if (is_string($model)) {
            $model = $this->getModel($model);
        }

        return $this->ownershipService->addOwnershipQuery(
            $query,
            $model,
            $action->value
        );
    }

    /**
     * Filter columns based on user permissions
     *
     * @param string|Model $model
     * @param CrudAction $action
     * @param array|null $requestedAttributes
     * @return array
     */
    public function filterAuthorizedAttributes(string|Model $model, CrudAction $action, ?array $requestedAttributes = null): array
    {
        if (is_string($model)) {
            $model = $this->getModel($model);
        }

        return $model->getCrudFillable($action);
    }

    /**
     * Process 'with' relationships and ensure proper authorization
     *
     * @param Builder $query The query builder
     * @param string $modelName The name of the model
     * @param array $relations The relations to load
     * @param array $attributesByRelation Optional columns to select for each relation
     * @param CrudAction $action The action (view, update)
     * @return Builder|Relation
     */
    public function loadAuthorizedRelations(
        Builder|Relation $query,
        string|Model $model,
        array $relations,
        array $attributesByRelation = [],
        CrudAction $action = CrudAction::READ,
    ): Builder|Relation {
        if (is_string($model)) {
            $model = $this->getModel($model);
        }

        foreach ($relations as $relation) {

            // Get the related model name
            $relatedModel = $this->getRelatedModel($model, $relation);

            // Check if user has permission to view the related model
            if (!$this->authorizationService->hasModelPermission($relatedModel, $action)) {
                continue;
            }

            // Get authorized columns for the relation
            $attributes = $attributesByRelation[$relation] ?? null;
            $authorizedAttributes  = $this->filterAuthorizedAttributes($relatedModel, $action, $attributes);

            // Load relation with ownership scope and column restrictions
            $query->with([$relation => function ($q) use ($relatedModel, $authorizedAttributes, $action) {
                // Apply ownership filter
                $this->scopeAuthorizedRecords($q, $relatedModel, $action);

                // Select only authorized columns if specified
                if (!empty($authorizedAttributes)) {
                    // Always include the primary key
                    $authorizedAttributes[] = 'id';
                    $q->select(array_unique($authorizedAttributes));
                }
            }]);
        }

        return $query;
    }

    /**
     * Apply filters to a query with permission checks
     *
     * @param Builder|Relation $query The query builder
     * @param string|Model $model The model
     * @param array $filters The filters to apply
     * @param CrudAction $action The action (view, update)
     * @return Builder|Relation
     */
    public function applyAuthorizedFilters(
        Builder|Relation $query,
        string|Model $model,
        array $filters,
        CrudAction $action
    ): Builder|Relation {
        if (empty($filters)) {
            return $query;
        }

        $modelInstance = is_string($model) ? $this->getModel($model) : $model;
        if (!$modelInstance) {
            return $query;
        }

        $fillables = $modelInstance->getFillable();

        foreach ($filters as $attribute => $value) {
            // Check if this is a relationship filter (contains a dot)
            if (str_contains($attribute, '.')) {
                list($relation, $relationAttribute) = explode('.', $attribute, 2);

                // Check if the relation exists on the model
                if (!method_exists($modelInstance, $relation)) {
                    continue;
                }

                // Handle relationship filtering
                if (is_array($value) && isset($value['operator'])) {
                    $operator = $value['operator'];
                    $filterValue = $value['value'] ?? null;

                    $query->whereHas($relation, function ($subQuery) use ($relationAttribute, $operator, $filterValue) {
                        if ($operator === 'like') {
                            $subQuery->where($relationAttribute, 'like', "%{$filterValue}%");
                        } else {
                            $this->applyOperatorFilter($subQuery, $relationAttribute, [
                                'operator' => $operator,
                                'value' => $filterValue
                            ]);
                        }
                    });
                } else {
                    // Simple equality filter on relationship
                    $query->whereHas($relation, function ($subQuery) use ($relationAttribute, $value) {
                        $subQuery->where($relationAttribute, $value);
                    });
                }

                continue;
            }

            // Regular attribute filtering (non-relationship)
            if (!in_array($attribute, $fillables)) {
                continue;
            }

            // Handle different filter types
            if (is_array($value)) {
                // Check for special operators
                if (isset($value['operator'])) {
                    $this->applyOperatorFilter($query, $attribute, $value);
                } else {
                    // Default to "in" operator for arrays
                    $query->whereIn($attribute, $value);
                }
            } else {
                // Simple equality filter
                $query->where($attribute, $value);
            }
        }

        return $query;
    }

    /**
     * Apply a filter with a specific operator
     *
     * @param Builder $query The query builder
     * @param string $attribute The column to filter
     * @param array $filter The filter configuration
     * @return Builder
     */
    protected function applyOperatorFilter(Builder $query, string $attribute, array $filter): Builder
    {
        $operator = $filter['operator'] ?? '=';
        $value = $filter['value'] ?? null;

        switch ($operator) {
            case 'between':
                if (is_array($value) && count($value) === 2) {
                    $query->whereBetween($attribute, $value);
                }
                break;
            case 'not_in':
                if (is_array($value)) {
                    $query->whereNotIn($attribute, $value);
                }
                break;
            case 'like':
                $query->where($attribute, 'like', "%{$value}%");
                break;
            case 'not':
            case '!=':
                $query->where($attribute, '!=', $value);
                break;
            case '>':
            case '<':
            case '>=':
            case '<=':
                $query->where($attribute, $operator, $value);
                break;
            default:
                $query->where($attribute, $operator, $value);
        }

        return $query;
    }

    public function verifyModel(string $modelClass): bool
    {

        if (App::bound($modelClass)) {
            $instance = App::make($modelClass);
            $reflection = new ReflectionClass($instance);
            $modelClass = $reflection->getName();
        }

        // Check if the class exists
        if (!class_exists($modelClass)) {
            return false;
        }

        // Check if the model has the HasCrud trait before creating it
        if (!in_array(HasCrud::class, class_uses_recursive($modelClass))) {
            return false;
        }

        // Check if modelClass is instance of Model without creating it
        if (!is_subclass_of($modelClass, Model::class)) {
            return false;
        }

        return true;
    }

    public function getModel(string|Model $model): ?Model
    {
        $modelClass = is_string($model) ? $model : get_class($model);

        if (!$this->verifyModel($modelClass)) {
            return null;
        }

        if (is_string($model)) {
            $model = app($modelClass);
        }

        return $model;
    }

    public function getRelatedModel(string|Model $model, string $relation): ?Model
    {
        $model = $this->getModel($model);
        if (!$model) {
            return null;
        }
        $relatedModelName = $model->$relation()->getQuery()->getModel()->getMorphClass();
        return $this->getModel($relatedModelName);
    }

    /**
     * Get the schema of all models
     *
     * @return array
     */
    public function getSchema(): array
    {
        //scan all model paths from config model_paths
        $models = [];
        foreach (config('trina-crud.model_paths') as $path) {
            $namespace = null;
            $files = glob($path . '/*.php');
            foreach ($files as $file) {
                $modelSchema = $this->parseModelFile($file, $namespace);
                if ($modelSchema) {
                    $models[] = $modelSchema;
                }
            }
        }
        return $models;
    }

    /**
     * Parse a model file to extract model information
     *
     * @param string $file The path to the model file
     * @param string|null $namespace The namespace of the model
     * @return ModelSchema|null
     */
    public function parseModelFile(string $file, ?string $namespace = null): ?ModelSchema
    {
        //get the class name from the file name
        $className = str_replace('.php', '', $file);
        $className = str_replace(dirname($file) . '/', '', $className);

        if (!$namespace) {
            $lines = file($file, 10);
            foreach ($lines as $line) {
                if (preg_match('/namespace\s+([\\a-zA-Z0-9_]+);/', $line, $matches)) {
                    $namespace = $matches[1];
                    break;
                }
            }
        }

        $fullClassName = $namespace . '\\' . $className;

        if (!$this->verifyModel($fullClassName)) {
            return null;
        }

        // Get the model instance to extract fillable attributes
        $model = $this->getModel($fullClassName);
        $fillables = $model ? $model->getFillable() : [];

        return new ModelSchema($fullClassName, $fillables);
    }
}
