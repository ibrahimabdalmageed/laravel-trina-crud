<?php

namespace Trinavo\TrinaCrud\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Trinavo\TrinaCrud\Contracts\ModelServiceInterface;
use Trinavo\TrinaCrud\Models\ModelSchema;

class TrinaCrudController extends Controller
{

    public function getSchema(Request $request): JsonResponse
    {
        $modelService = app(ModelServiceInterface::class);
        $schemas = $modelService->getSchema();
        
        return response()->json([
            'models' => $schemas,
        ]);
    }
}
