<?php

namespace Trinavo\TrinaCrud\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Trinavo\TrinaCrud\Contracts\TrinaCrudAuthorizationServiceInterface;


class TrinaCrudModelService
{
    protected TrinaCrudAuthorizationServiceInterface $authorizationService;

    protected TrinaCrudModelHelper $modelHelper;

    public function __construct(
        TrinaCrudAuthorizationServiceInterface $authorizationService,
        TrinaCrudModelHelper $modelHelper
    ) {
        $this->authorizationService = $authorizationService;
        $this->modelHelper = $modelHelper;
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
        $trinaCrudModel = $this->modelHelper->findTrinaCrudModel($modelName);

        if (!$trinaCrudModel) {
            throw new NotFoundHttpException('Model not found');
        }

        // Get the model class
        $modelClass = $trinaCrudModel->class_name;

        // Create a new query
        $query = app($modelClass)->query();

        // Apply ownership filtering
        $query = $this->authorizationService->scopeAuthorizedRecords($query, $modelName);

        // Filter columns based on permissions
        $authorizedColumns = $this->authorizationService->filterAuthorizedColumns($modelName, $columns);

        // Select only authorized columns if specified
        if (!empty($authorizedColumns)) {
            $query->select($authorizedColumns);
        }

        // Load authorized relations
        if ($with) {
            $query = $this->authorizationService->loadAuthorizedRelations($query, $modelName, $with, $relationColumns);
        }

        // Apply filters
        if (!empty($filters)) {
            $query = $this->authorizationService->applyAuthorizedFilters($query, $modelName, $filters);
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
        $trinaCrudModel = $this->modelHelper->findTrinaCrudModel($modelName);

        if (!$trinaCrudModel) {
            throw new NotFoundHttpException('Model not found');
        }

        // Get the model class
        $modelClass = $trinaCrudModel->class_name;

        // Create a new query
        $query = app($modelClass)->query();

        // Apply ownership filtering
        $query = $this->authorizationService->scopeAuthorizedRecords($query, $modelName);

        // Filter columns based on permissions
        $authorizedColumns = $this->authorizationService->filterAuthorizedColumns($modelName, $columns);

        // Select only authorized columns if specified
        if (!empty($authorizedColumns)) {
            $query->select($authorizedColumns);
        }

        // Load authorized relations
        if ($with) {
            $query = $this->authorizationService->loadAuthorizedRelations($query, $modelName, $with, $relationColumns);
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
        $trinaCrudModel = $this->modelHelper->findTrinaCrudModel($modelName);

        if (!$trinaCrudModel) {
            throw new NotFoundHttpException('Model not found');
        }

        // Get the model class
        $modelClass = $trinaCrudModel->class_name;

        // Filter data based on permissions
        $authorizedData = [];
        foreach ($data as $key => $value) {
            if ($this->authorizationService->hasColumnPermission($modelName, $key, 'create')) {
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
        // Find the model
        $trinaCrudModel = $this->modelHelper->findTrinaCrudModel($modelName);

        if (!$trinaCrudModel) {
            throw new NotFoundHttpException('Model not found');
        }

        // Get the model class
        $modelClass = $trinaCrudModel->class_name;

        // Create a new query
        $query = app($modelClass)->query();

        // Apply ownership filtering
        $query = $this->authorizationService->scopeAuthorizedRecords($query, $modelName);

        // Find the record
        $record = $query->find($id);

        if (!$record) {
            throw new NotFoundHttpException('Record not found');
        }

        // Filter data based on permissions
        $authorizedData = [];
        foreach ($data as $key => $value) {
            if ($this->authorizationService->hasColumnPermission($modelName, $key, 'update')) {
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
        // Find the model
        $trinaCrudModel = $this->modelHelper->findTrinaCrudModel($modelName);

        if (!$trinaCrudModel) {
            throw new NotFoundHttpException('Model not found');
        }

        // Get the model class
        $modelClass = $trinaCrudModel->class_name;

        // Create a new query
        $query = app($modelClass)->query();

        // Apply ownership filtering
        $query = $this->authorizationService->scopeAuthorizedRecords($query, $modelName);

        // Find the record
        $record = $query->find($id);

        if (!$record) {
            throw new NotFoundHttpException('Record not found');
        }

        // Delete the record
        return $record->delete();
    }
}
