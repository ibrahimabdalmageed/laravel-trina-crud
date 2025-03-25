<?php

namespace Trinavo\TrinaCrud\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Trinavo\TrinaCrud\Contracts\AuthorizationServiceInterface;
use Trinavo\TrinaCrud\Contracts\ModelServiceInterface;
use Trinavo\TrinaCrud\Enums\CrudAction;
use Trinavo\TrinaCrud\Models\ModelSchema;

class TrinaCrudController extends Controller
{

    public function getSchema(?string $model = null): JsonResponse
    {
        // Validate model parameter - reject if it contains suspicious patterns
        if ($model !== null) {
            // Prevent directory traversal and special characters
            if (preg_match('/[\/\\\\\.]{2,}|[^a-zA-Z0-9_\.]/', $model)) {
                return response()->json(['error' => 'Invalid model name format'], 422);
            }
        }

        $modelService = app(ModelServiceInterface::class);
        $schemas =  collect($modelService->getSchema($model, authorizedOnly: true));

        $schemas = $schemas->map(function (ModelSchema $schema) {
            return [
                'model' => $schema->getModelName(),
                'fields' => $schema->getAuthorizedFields(),
            ];
        });

        $schemas = $schemas->filter();

        //reindex the array
        $schemas = $schemas->values();

        if ($model) {
            $schemas = $schemas->firstWhere('model', $model);
        }

        return response()->json($schemas);
    }
}
