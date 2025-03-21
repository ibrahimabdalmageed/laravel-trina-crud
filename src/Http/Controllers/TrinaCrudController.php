<?php

namespace Trinavo\TrinaCrud\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TrinaCrudController extends Controller
{

    public function getSchema(Request $request): JsonResponse
    {
        return response()->json([
            'models' => [],
        ]);
    }
}
