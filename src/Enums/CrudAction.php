<?php

namespace Trinavo\TrinaCrud\Enums;

enum CrudAction: string
{
    case READ = 'read';
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case READ_ANY = 'read_any';
    case CREATE_ANY = 'create_any';
    case UPDATE_ANY = 'update_any';
    case DELETE_ANY = 'delete_any';

    /**
     * Get the corresponding "any" action for the current action
     *
     * @return self
     */
    public function toAnyAction(): self
    {
        return match($this) {
            self::READ => self::READ_ANY,
            self::CREATE => self::CREATE_ANY,
            self::UPDATE => self::UPDATE_ANY,
            self::DELETE => self::DELETE_ANY,
            default => $this
        };
    }

    /**
     * Check if this action is an "any" action
     *
     * @return bool
     */
    public function isAnyAction(): bool
    {
        return in_array($this, [
            self::READ_ANY,
            self::CREATE_ANY,
            self::UPDATE_ANY,
            self::DELETE_ANY
        ]);
    }

    /**
     * Convert the enum to a permission string for use with authorization systems
     *
     * @param string $modelName
     * @return string
     */
    public function toPermissionString(string $modelName): string
    {
        return $this->value . '-' . \Illuminate\Support\Str::kebab($modelName);
    }
}
