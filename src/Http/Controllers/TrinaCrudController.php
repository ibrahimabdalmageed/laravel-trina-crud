<?php

namespace Trinavo\TrinaCrud\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
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
        $schemas =  collect($modelService->getSchema($model));

        $schemas = $schemas->map(function (ModelSchema $schema) {
            if (!(app(AuthorizationServiceInterface::class)
                ->authHasModelPermission(
                    $schema->getModelName(),
                    CrudAction::READ
                )
                || app(AuthorizationServiceInterface::class)
                ->authHasModelPermission(
                    $schema->getModelName(),
                    CrudAction::UPDATE
                )
                || app(AuthorizationServiceInterface::class)
                ->authHasModelPermission(
                    $schema->getModelName(),
                    CrudAction::DELETE
                )
                || app(AuthorizationServiceInterface::class)
                ->authHasModelPermission(
                    $schema->getModelName(),
                    CrudAction::CREATE
                )
            )) {
                return null;
            }
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
