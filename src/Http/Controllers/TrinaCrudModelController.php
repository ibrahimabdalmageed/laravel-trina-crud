<?php

namespace Trinavo\TrinaCrud\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Trinavo\TrinaCrud\Http\Requests\ModelsController\ValidateTrinaCrudModelCreateRequest;
use Trinavo\TrinaCrud\Http\Requests\ModelsController\ValidateTrinaCrudModelIndexRequest;
use Trinavo\TrinaCrud\Services\TrinaCrudAuthorizationService;
use Trinavo\TrinaCrud\Services\TrinaCrudModelService;

class TrinaCrudModelController extends Controller
{
    /**
     * @var TrinaCrudModelService
     */
    protected $modelService;

    /**
     * @var TrinaCrudAuthorizationService
     */
    protected $authService;

    public function __construct(
        TrinaCrudModelService $modelService,
        TrinaCrudAuthorizationService $authService
    ) {
        $this->modelService = $modelService;
        $this->authService = $authService;
    }

    /**
     * Get a paginated list of model records
     *
     * @param ValidateTrinaCrudModelIndexRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function index(
        String $model,
        ValidateTrinaCrudModelIndexRequest $request
    ) {
        try {
            $columns = $request->input('columns', []);
            $with = $request->has('with') ?
                (is_array($request->with) ? $request->with : explode(',', $request->with)) :
                null;
            $relationColumns = $request->input('relation_columns', []);
            $filters = $request->input('filters', []);
            $perPage = $request->input('per_page', 15);

            return $this->modelService->getModelRecords(
                $model,
                $columns,
                $with,
                $relationColumns,
                $filters,
                $perPage
            );
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get a single model record by ID
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Database\Eloquent\Model
     */
    public function show(Request $request, $id)
    {
        try {
            $modelName = $request->model;
            $columns = $request->input('columns', []);
            $with = $request->has('with') ?
                (is_array($request->with) ? $request->with : explode(',', $request->with)) :
                null;
            $relationColumns = $request->input('relation_columns', []);

            return $this->modelService->getModelRecord(
                $modelName,
                $id,
                $columns,
                $with,
                $relationColumns
            );
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create a new model record
     *
     * @param ValidateTrinaCrudModelCreateRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Database\Eloquent\Model
     */
    public function store(ValidateTrinaCrudModelCreateRequest $request)
    {
        try {
            $modelName = $request->model;

            // Check if user has permission to create this model
            if (!$this->authService->hasModelPermission($modelName, 'create')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Filter input data to exclude non-column fields
            $data = collect($request->all())->except(['model'])->toArray();

            return $this->modelService->createModelRecord($modelName, $data);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update a model record
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Database\Eloquent\Model
     */
    public function update(Request $request, $id)
    {
        try {
            $modelName = $request->model;

            // Check if user has permission to update this model
            if (!$this->authService->hasModelPermission($modelName, 'update')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Filter input data to exclude non-column fields
            $data = collect($request->all())->except(['model'])->toArray();

            return $this->modelService->updateModelRecord($modelName, $id, $data);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a model record
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $modelName = $request->model;

            // Check if user has permission to delete this model
            if (!$this->authService->hasModelPermission($modelName, 'delete')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $result = $this->modelService->deleteModelRecord($modelName, $id);

            return response()->json(['success' => $result]);
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
