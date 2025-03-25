<?php

namespace Trinavo\TrinaCrud\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Trinavo\TrinaCrud\Services\OpenApiService;
use Trinavo\TrinaCrud\Services\ModelService;

class OpenApiController extends Controller
{
    /**
     * @var OpenApiService
     */
    protected OpenApiService $openApiService;

    /**
     * @var ModelService
     */
    protected ModelService $modelService;

    /**
     * Create a new controller instance.
     *
     * @param OpenApiService $openApiService
     * @param ModelService $modelService
     */
    public function __construct(OpenApiService $openApiService, ModelService $modelService)
    {
        $this->openApiService = $openApiService;
        $this->modelService = $modelService;
    }

    /**
     * Get OpenAPI documentation in JSON format
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function json(Request $request): JsonResponse
    {
        $models = $this->modelService->getSchema(authorizedOnly: true);
        $openApi = $this->openApiService->generateOpenApi($models);

        // Check if download parameter is present
        if ($request->has('download')) {
            return response()->json($openApi)
                ->header('Content-Disposition', 'attachment; filename="openapi.json"');
        }

        return response()->json($openApi);
    }

    /**
     * Get OpenAPI documentation in YAML format
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function yaml(Request $request)
    {
        $models = $this->modelService->getSchema(authorizedOnly: true);
        $openApi = $this->openApiService->generateOpenApi($models);

        foreach ($openApi['components']['schemas'] as $key => $schema) {
            if (isset($schema['properties']) && !$schema['properties']) {
                $openApi['components']['schemas'][$key]['properties'] = [];
            }
        }

        // Convert to YAML using Symfony YAML component
        $yaml = \Symfony\Component\Yaml\Yaml::dump($openApi, 10, 2, \Symfony\Component\Yaml\Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        // Check if download parameter is present
        if ($request->has('download')) {
            return response($yaml)
                ->header('Content-Type', 'application/x-yaml')
                ->header('Content-Disposition', 'attachment; filename="openapi.yaml"');
        }

        return response($yaml)
            ->header('Content-Type', 'application/x-yaml');
    }
}
