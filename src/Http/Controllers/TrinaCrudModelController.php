<?php

namespace Trinavo\TrinaCrud\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Trinavo\TrinaCrud\Contracts\ModelServiceInterface;
use Trinavo\TrinaCrud\Http\Requests\ModelsController\ValidateTrinaCrudModelCreateRequest;
use Trinavo\TrinaCrud\Http\Requests\ModelsController\ValidateTrinaCrudModelIndexRequest;

class TrinaCrudModelController extends Controller
{
    /**
     * @var ModelServiceInterface
     */
    protected $modelService;

    public function __construct(
        ModelServiceInterface $modelService,
    ) {
        $this->modelService = $modelService;
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
            $attributes = $request->input('attributes', []);
            $with = $request->has('with') ?
                (is_array($request->with) ? $request->with : explode(',', $request->with)) :
                null;
            $relationColumns = $request->input('relation_columns', []);
            $filters = $request->input('filters', []);
            $perPage = $request->input('per_page', 15);

            return $this->modelService->all(
                $model,
                $attributes,
                $with,
                $relationColumns,
                $filters,
                $perPage
            );
        } catch (NotFoundHttpException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (Exception $e) {
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
    public function show(
        String $model,
        Request $request,
        $id
    ) {
        try {
            $attributes = $request->input('attributes', []);
            $with = $request->has('with') ?
                (is_array($request->with) ? $request->with : explode(',', $request->with)) :
                null;
            $relationColumns = $request->input('relation_columns', []);

            return $this->modelService->find(
                $model,
                $id,
                $attributes,
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
    public function store(
        String $model,
        ValidateTrinaCrudModelCreateRequest $request
    ) {
        try {

            $data = $request->json();
            if (!$data) {
                $data = $request->all();
            }

            // Filter input data to exclude non-column fields
            $data = collect($data)->except(['model'])->toArray();

            return $this->modelService->create($model, $data);
        } catch (NotFoundHttpException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
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
    public function update(
        String $model,
        Request $request,
        $id
    ) {
        try {

            // Filter input data to exclude non-column fields
            $data = collect($request->all())->except(['model'])->toArray();

            return $this->modelService->update($model, $id, $data);
        } catch (NotFoundHttpException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
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
    public function destroy(
        String $model,
        Request $request,
        $id
    ) {
        try {


            $result = $this->modelService->delete($model, $id);

            return response()->json(['success' => $result]);
        } catch (NotFoundHttpException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
