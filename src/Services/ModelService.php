<?php

namespace Trinavo\TrinaCrud\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Contracts\ModelServiceInterface;
use Trinavo\TrinaCrud\Contracts\OwnershipServiceInterface;
use Trinavo\TrinaCrud\Models\TrinaCrudModel;

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
     * @param array $columns The columns to select
     * @param array|null $with The relations to load
     * @param array $relationColumns The columns to select for each relation
     * @param array $filters The filters to apply
     * @param int $perPage The number of records per page
     * @return LengthAwarePaginator
     * @throws NotFoundHttpException
     */
    public function getModelRecords(
        string $modelName,
        array $columns = [],
        ?array $with = null,
        array $relationColumns = [],
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        // Find the model
        $trinaCrudModel = $this->findTrinaCrudModel($modelName);

        if (!$trinaCrudModel) {
            throw new NotFoundHttpException('Model not found');
        }

        // Get the model class
        $modelClass = $trinaCrudModel->class_name;

        // Create a new query
        $query = app($modelClass)->query();

        // Apply ownership filtering
        $query = $this->scopeAuthorizedRecords($query, $modelName);

        // Filter columns based on permissions
        $authorizedColumns = $this->filterAuthorizedColumns($modelName, $columns);

        // Select only authorized columns if specified
        if (!empty($authorizedColumns)) {
            $query->select($authorizedColumns);
        }

        // Load authorized relations
        if ($with) {
            $query = $this->loadAuthorizedRelations($query, $modelName, $with, $relationColumns);
        }

        // Apply filters
        if (!empty($filters)) {
            $query = $this->applyAuthorizedFilters($query, $modelName, $filters);
        }

        // Paginate the results
        return $query->paginate($perPage);
    }

    /**
     * Get a single model record by ID with authorization
     *
     * @param string $modelName The name of the model
     * @param int $id The ID of the record
     * @param array $columns The columns to select
     * @param array|null $with The relations to load
     * @param array $relationColumns The columns to select for each relation
     * @return Model
     * @throws NotFoundHttpException
     */
    public function getModelRecord(
        string $modelName,
        int $id,
        array $columns = [],
        ?array $with = null,
        array $relationColumns = []
    ): Model {
        // Find the model
        $trinaCrudModel = $this->findTrinaCrudModel($modelName);

        if (!$trinaCrudModel) {
            throw new NotFoundHttpException('Model not found');
        }

        // Get the model class
        $modelClass = $trinaCrudModel->class_name;

        // Create a new query
        $query = app($modelClass)->query();

        // Apply ownership filtering
        $query = $this->scopeAuthorizedRecords($query, $modelName);

        // Filter columns based on permissions
        $authorizedColumns = $this->filterAuthorizedColumns($modelName, $columns);

        // Select only authorized columns if specified
        if (!empty($authorizedColumns)) {
            $query->select($authorizedColumns);
        }

        // Load authorized relations
        if ($with) {
            $query = $this->loadAuthorizedRelations($query, $modelName, $with, $relationColumns);
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
    public function createModelRecord(string $modelName, array $data): Model
    {
        // Find the model
        $trinaCrudModel = $this->findTrinaCrudModel($modelName);

        if (!$trinaCrudModel) {
            throw new NotFoundHttpException('Model not found');
        }

        // Get the model class
        $modelClass = $trinaCrudModel->class_name;

        // Filter data based on permissions
        $authorizedData = [];
        foreach ($data as $key => $value) {
            if ($this->hasColumnPermission($modelName, $key, 'create')) {
                $authorizedData[$key] = $value;
            }
        }

        // Create the record
        return app($modelClass)->create($authorizedData);
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
    public function updateModelRecord(string $modelName, int $id, array $data): Model
    {
        $user = $this->authorizationService->getUser();

        if ($user) {
            if (!$this->hasModelPermission($modelName, 'update')) {
                throw new NotFoundHttpException('You are not authorized to update this model');
            }
        } else {
            if (!$this->hasModelPermission($modelName, 'update_any')) {
                throw new NotFoundHttpException('You are not authorized to update this model');
            }
        }

        // Find the model
        $trinaCrudModel = $this->findTrinaCrudModel($modelName);

        if (!$trinaCrudModel) {
            throw new NotFoundHttpException('Model not found');
        }

        // Get the model class
        $modelClass = $trinaCrudModel->class_name;

        // Create a new query
        $query = app($modelClass)->query();

        // Apply ownership filtering
        $query = $this->scopeAuthorizedRecords($query, $modelName);

        // Find the record
        $record = $query->find($id);

        if (!$record) {
            throw new NotFoundHttpException('Record not found');
        }

        // Filter data based on permissions
        $authorizedData = [];
        foreach ($data as $key => $value) {
            if ($this->hasColumnPermission($modelName, $key, 'update')) {
                $authorizedData[$key] = $value;
            }
        }

        // Update the record
        $record->update($authorizedData);

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
    public function deleteModelRecord(string $modelName, int $id): bool
    {
        $user = $this->authorizationService->getUser();

        if ($user) {
            if (!$this->hasModelPermission($modelName, 'delete')) {
                throw new NotFoundHttpException('You are not authorized to delete this model');
            }
        } else {
            if (!$this->hasModelPermission($modelName, 'delete_any')) {
                throw new NotFoundHttpException('You are not authorized to delete this model');
            }
        }

        // Find the model
        $trinaCrudModel = $this->findTrinaCrudModel($modelName);

        if (!$trinaCrudModel) {
            throw new NotFoundHttpException('Model not found');
        }

        // Get the model class
        $modelClass = $trinaCrudModel->class_name;

        // Create a new query
        $query = app($modelClass)->query();

        // Apply ownership filtering
        $query = $this->scopeAuthorizedRecords($query, $modelName);

        // Find the record
        $record = $query->find($id);

        if (!$record) {
            throw new NotFoundHttpException('Record not found');
        }

        // Delete the record
        return $record->delete();
    }


    /**
     * Check if the user has permission to access a model
     *
     * @param string $modelName The name of the model
     * @param string $action The action (view, create, update, delete)
     * @return bool
     */
    public function hasModelPermission(string $modelName, string $action): bool
    {
        $user = $this->authorizationService->getUser();

        if (!$user) {
            $action = $action . '_any';
        }

        // Convert camelCase to kebab-case for permission names
        $permissionName = Str::kebab($action) . '-' . Str::kebab($modelName);

        // Check if user has the permission
        return $this->authorizationService->hasPermissionTo($permissionName);
    }

    /**
     * Check if the user has permission to access a column
     *
     * @param string $modelName The name of the model
     * @param string $columnName The name of the column
     * @param string $action The action (view, update)
     * @return bool
     */
    public function hasColumnPermission(string $modelName, string $columnName, string $action): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Convert camelCase to kebab-case for permission names
        $columnPermission = Str::kebab($action) . '-' . Str::kebab($modelName) . '-' . Str::kebab($columnName);

        // Check for column-specific permission first
        if ($this->authorizationService->hasPermissionTo($columnPermission)) {
            return true;
        } else {
            return false;
        }
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

        // Check if model uses Ownable trait
        $modelClass = $this->resolveModelClass($modelName);
        if (!$modelClass || !class_exists($modelClass)) {
            return $query;
        }

        if ($this->hasModelPermission($modelName, 'view_any')) {
            return $query;
        }

        // Otherwise only return records owned by the user
        $query = $this->ownershipService->addOwnershipQuery($query, $user->id, $modelClass);

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
            return $this->hasColumnPermission($modelName, $column, 'view');
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
            if (!$this->hasColumnPermission($modelName, $column, 'view')) {
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
        return Str::studly(Str::singular($relation));
    }


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
