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
     * @var array The fillable attributes
     */
    protected array $fillables = [];

    /**
     * Create a new ModelSchema instance
     * 
     * @param string $modelName
     * @param array $fillables
     */
    public function __construct(string $modelName, array $fillables = [])
    {
        $this->modelName = $modelName;
        $this->fillables = $fillables;
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
    public function getFillables(): array
    {
        return $this->fillables;
    }

    /**
     * Set the fillable attributes
     * 
     * @param array $fillables
     * @return self
     */
    public function setFillables(array $fillables): self
    {
        $this->fillables = $fillables;
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
            'modelName' => $this->modelName,
            'fillables' => $this->fillables,
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
