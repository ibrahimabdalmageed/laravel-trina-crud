<?php

namespace Trinavo\TrinaCrud\Services;

use Trinavo\TrinaCrud\Models\ModelSchema;
use Illuminate\Support\Facades\Schema;

class OpenApiService
{
    /**
     * Generate complete OpenAPI documentation
     * 
     * @param ModelSchema[] $models List of model class names
     * @return array
     */
    public function generateOpenApi(array $models): array
    {
        $openApi = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => config('app.name', 'Laravel') . ' API',
                'description' => 'API documentation automatically generated by TrinaCrud',
                'version' => '1.0.0',
            ],
            'servers' => [
                [
                    'url' => url('/api/' . config('trina-crud.route_prefix', 'trina-crud')),
                    'description' => 'API Server',
                ],
            ],
            'paths' => [],
            'components' => [
                'schemas' => [],
            ],
        ];

        foreach ($models as $model) {
            $fields = $model->getAuthorizedFields();
            $properties = [];

            // Try to get the model class to access schema information
            $modelClass = "\\{$model->getModelName()}";
            $modelInstance = null;

            try {
                if (class_exists($modelClass)) {
                    $modelInstance = new $modelClass();
                }
            } catch (\Exception $e) {
                // If we can't instantiate the model, we'll use the basic field detection
            }

            // Get table schema information if available
            $table = $modelInstance ? $modelInstance->getTable() : null;
            $columns = null;

            $columns = Schema::getColumns($table);

            foreach ($fields as $field) {
                // If we have schema information, use that to determine field type
                if ($columns && isset($columns[$field])) {
                    $column = $columns[$field];
                    $properties[$field] = $this->getFieldDefinitionFromColumn($column);
                } else {
                    $properties[$field] = [
                        'type' => 'string',
                        'description' => "Value of type: $field"
                    ];
                }
            }

            $itemOpenApi = [
                'type' => 'object',
                'properties' => $properties
            ];

            // Add schema to components
            $openApi['components']['schemas'][$model->getModelName()] = $itemOpenApi;

            // Generate paths for this model
            $resourceName = $this->getResourceName($model->getModelName());
            $this->addResourcePaths($openApi['paths'], $resourceName, $model->getModelName());
        }

        return $openApi;
    }

    /**
     * Add paths for a resource
     * 
     * @param array &$paths
     * @param string $resourceName
     * @param string $modelName
     * @return void
     */
    private function addResourcePaths(array &$paths, string $resourceName, string $modelName): void
    {
        // Index endpoint
        $paths["/{$resourceName}"] = [
            'get' => [
                'summary' => "List all {$resourceName}",
                'description' => "Returns a list of {$resourceName}",
                'operationId' => "getAll{$modelName}",
                'tags' => [$resourceName],
                'parameters' => [
                    [
                        'name' => 'page',
                        'in' => 'query',
                        'description' => 'Page number',
                        'required' => false,
                        'schema' => ['type' => 'integer', 'default' => 1]
                    ],
                    [
                        'name' => 'per_page',
                        'in' => 'query',
                        'description' => 'Items per page',
                        'required' => false,
                        'schema' => ['type' => 'integer', 'default' => 15]
                    ],
                    [
                        'name' => 'sort',
                        'in' => 'query',
                        'description' => 'Field to sort by (prefix with - for descending)',
                        'required' => false,
                        'schema' => ['type' => 'string']
                    ],
                    [
                        'name' => 'with',
                        'in' => 'query',
                        'description' => 'Related resources to include',
                        'required' => false,
                        'schema' => ['type' => 'string']
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Successful operation',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => [
                                                '$ref' => "#/components/schemas/{$modelName}"
                                            ]
                                        ],
                                        'meta' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'current_page' => ['type' => 'integer'],
                                                'from' => ['type' => 'integer'],
                                                'last_page' => ['type' => 'integer'],
                                                'path' => ['type' => 'string'],
                                                'per_page' => ['type' => 'integer'],
                                                'to' => ['type' => 'integer'],
                                                'total' => ['type' => 'integer'],
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '401' => ['description' => 'Unauthorized'],
                    '403' => ['description' => 'Forbidden'],
                ]
            ],
            'post' => [
                'summary' => "Create a new {$modelName}",
                'description' => "Creates a new {$modelName} and returns it",
                'operationId' => "create{$modelName}",
                'tags' => [$resourceName],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => "#/components/schemas/{$modelName}"
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Created successfully',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            '$ref' => "#/components/schemas/{$modelName}"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '400' => ['description' => 'Bad request'],
                    '401' => ['description' => 'Unauthorized'],
                    '403' => ['description' => 'Forbidden'],
                    '422' => ['description' => 'Validation error'],
                ]
            ]
        ];

        // Show, Update, Delete endpoints
        $paths["/{$resourceName}/{id}"] = [
            'get' => [
                'summary' => "Get a specific {$modelName}",
                'description' => "Returns a single {$modelName}",
                'operationId' => "get{$modelName}",
                'tags' => [$resourceName],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'description' => 'ID of the resource',
                        'required' => true,
                        'schema' => ['type' => 'integer']
                    ],
                    [
                        'name' => 'with',
                        'in' => 'query',
                        'description' => 'Related resources to include',
                        'required' => false,
                        'schema' => ['type' => 'string']
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Successful operation',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            '$ref' => "#/components/schemas/{$modelName}"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '401' => ['description' => 'Unauthorized'],
                    '403' => ['description' => 'Forbidden'],
                    '404' => ['description' => 'Not found'],
                ],
            ],
            'put' => [
                'summary' => "Update a {$modelName}",
                'description' => "Updates a {$modelName} and returns it",
                'operationId' => "update{$modelName}",
                'tags' => [$resourceName],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'description' => 'ID of the resource',
                        'required' => true,
                        'schema' => ['type' => 'integer']
                    ]
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => "#/components/schemas/{$modelName}"
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Updated successfully',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            '$ref' => "#/components/schemas/{$modelName}"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '400' => ['description' => 'Bad request'],
                    '401' => ['description' => 'Unauthorized'],
                    '403' => ['description' => 'Forbidden'],
                    '404' => ['description' => 'Not found'],
                    '422' => ['description' => 'Validation error'],
                ],
            ],
            'delete' => [
                'summary' => "Delete a {$modelName}",
                'description' => "Deletes a {$modelName}",
                'operationId' => "delete{$modelName}",
                'tags' => [$resourceName],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'description' => 'ID of the resource',
                        'required' => true,
                        'schema' => ['type' => 'integer']
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Deleted successfully',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '401' => ['description' => 'Unauthorized'],
                    '403' => ['description' => 'Forbidden'],
                    '404' => ['description' => 'Not found'],
                ]
            ]
        ];

        // Schema endpoint
        $paths["/{$resourceName}/get-schema"] = [
            'get' => [
                'summary' => "Get schema for {$modelName}",
                'description' => "Returns schema information for {$modelName}",
                'operationId' => "getSchema{$modelName}",
                'tags' => [$resourceName],
                'responses' => [
                    '200' => [
                        'description' => 'Successful operation',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'model' => ['type' => 'string'],
                                        'fields' => [
                                            'type' => 'array',
                                            'items' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '401' => ['description' => 'Unauthorized'],
                    '403' => ['description' => 'Forbidden'],
                ]
            ]
        ];
    }

    /**
     * Get field definition from database column schema
     * 
     * @param \Doctrine\DBAL\Schema\Column $column
     * @return array
     */
    private function getFieldDefinitionFromColumn($columnInfo): array
    {


        $colType = $columnInfo['type_name'];

        $isBoolean = false;
        if ($colType === 'boolean' || $colType === 'bool') {
            $isBoolean = true;
        } elseif ($colType === 'tinyint' && strpos($colType, 'tinyint(1)') !== false) {
            $isBoolean = true;
        }

        // Determine field type based on database column type (similar to HasCrud::buildValidationRules)

        // Detect boolean fields
        if ($isBoolean) {
            $definition['type'] = 'boolean';
            $definition['description'] = 'Boolean value';
            return $definition;
        }


        // Handle other types
        switch ($colType) {
            case 'bigint':
            case 'integer':
            case 'smallint':
                $definition['type'] = 'integer';
                $definition['format'] = ($colType === 'bigint') ? 'int64' : 'int32';
                $definition['description'] = 'Integer value';
                break;

            case 'decimal':
            case 'float':
                $definition['type'] = 'number';
                $definition['format'] = ($colType === 'decimal') ? 'decimal' : 'float';
                $definition['description'] = 'Numeric value';
                break;
            case 'date':
                $definition['type'] = 'string';
                $definition['format'] = 'date';
                $definition['description'] = 'Date value (YYYY-MM-DD)';
                break;

            case 'datetime':
            case 'datetimetz':
            case 'timestamp':
                $definition['type'] = 'string';
                $definition['format'] = 'date-time';
                $definition['description'] = 'Date and time value';
                break;

            case 'string':
            case 'text':
                $definition['type'] = 'string';

                $length = 0;
                if (
                    preg_match('/varchar\((\d+)\)/', $colType, $matches) ||
                    preg_match('/char\((\d+)\)/', $colType, $matches)
                ) {
                    $length = (int)$matches[1];
                }

                $definition['maxLength'] = $length;
                $definition['description'] = "String value (max $length characters)";
                break;

            case 'json':
            case 'array':
                $definition['type'] = 'object';
                $definition['additionalProperties'] = true;
                $definition['description'] = 'JSON data';
                break;

            default:
                $definition['type'] = 'string';
                $definition['description'] = "Value of type: {$colType}";
        }

        return $definition;
    }


    /**
     * Get resource name from model name
     * 
     * @param string $modelName
     * @return string
     */
    private function getResourceName(string $modelName): string
    {
        return str_replace('\\', '.', $modelName);
    }
}
