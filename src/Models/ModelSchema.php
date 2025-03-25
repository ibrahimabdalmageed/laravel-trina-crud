<?php

namespace Trinavo\TrinaCrud\Models;

use JsonSerializable;

class ModelSchema implements JsonSerializable
{
    /**
     * @var string The model name
     */
    protected string $modelName;

    /**
     * @var array The fields
     */
    protected array $allFields = [];

    /**
     * Create a new ModelSchema instance
     * 
     * @param string $modelName
     * @param array $allFields
     */
    public function __construct(string $modelName, array $allFields = [])
    {
        $this->modelName = $modelName;
        $this->allFields = $allFields;
    }

    /**
     * Get the model name
     * 
     * @return string
     */
    public function getModelName(): string
    {
        return $this->modelName;
    }

    /**
     * Get the fillable attributes
     * 
     * @return array
     */
    public function getAllFields(): array
    {
        return $this->allFields;
    }

    /**
     * Set the fillable attributes
     * 
     * @param array $allFields
     * @return self
     */
    public function setAllFields(array $allFields): self
    {
        $this->allFields = $allFields;
        return $this;
    }

    /**
     * Convert the object to JSON
     * 
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'model' => $this->modelName,
            'all_fields' => $this->allFields,
        ];
    }

    /**
     * Convert the object to JSON string
     * 
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->jsonSerialize());
    }
}
