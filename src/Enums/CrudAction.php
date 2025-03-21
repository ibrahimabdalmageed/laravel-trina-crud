<?php

namespace Trinavo\TrinaCrud\Enums;

use Illuminate\Support\Str;

enum CrudAction: string
{
    case READ = 'read';
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';


    /**
     * Convert the enum to a model permission string for use with authorization systems
     *
     * @param string $modelName
     * @return string
     */
    public function toModelPermissionString(string $modelName): string
    {
        return $this->value . '-' . Str::kebab($modelName);
    }

    /**
     * Convert the enum to an attribute permission string for use with authorization systems
     *
     * @param string $modelName
     * @param string $attribute
     * @return string
     */
    public function toAttributePermissionString(string $modelName, string $attribute): string
    {
        return Str::kebab($modelName) . '_' .
            Str::kebab($attribute) . '_' .
            Str::kebab($this->value);
    }
}
