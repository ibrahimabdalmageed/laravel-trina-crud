<?php

namespace Trinavo\TrinaCrud\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Trinavo\TrinaCrud\Contracts\ModelServiceInterface;

class TrinaCrudController extends Controller
{

    public function getSchema(Request $request): JsonResponse
    {
        return response()->json([
            'models' => app(ModelServiceInterface::class)->getSchema(),
        ]);
    }
}
