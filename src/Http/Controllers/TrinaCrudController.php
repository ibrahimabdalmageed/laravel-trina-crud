<?php

namespace Trinavo\TrinaCrud\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Trinavo\TrinaCrud\Models\TrinaCrudModel;

class TrinaCrudController extends Controller
{
    public function syncModels(): JsonResponse
    {
        Artisan::call('trinacrud:sync-models');
        $output = Artisan::output();

        return response()->json([
            'status' => 'success',
            'message' => 'Model synchronization completed.',
            'output' => $output,
        ]);
    }

    public function getSchema(Request $request): JsonResponse
    {
        $query = TrinaCrudModel::query();
        if ($request->has('model')) {
            $query->where('model', $request->model);
        }
        if ($request->has('with_columns')) {
            $query->with('columns');
        }

        $models = $query->paginate($request->per_page ?? 10);

        return response()->json([
            'models' => $models,
        ]);
    }
}
