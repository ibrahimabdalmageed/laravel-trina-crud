<?php

namespace Trinavo\TrinaCrud\Enums;

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
        return $this->value . ' ' . str_replace("\\", ".", $modelName);
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
        return $this->value . ' ' . str_replace("\\", ".", $modelName) . ' ' . $attribute;
    }
}
