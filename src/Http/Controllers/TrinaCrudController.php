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

    public function getSchema(Request $request, ?string $model = null): JsonResponse
    {
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
            $schemas = $schemas->firstWhere('model', $request->model);
        }

        return response()->json($schemas);
    }
}
