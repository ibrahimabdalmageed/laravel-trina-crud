<?php

namespace Trinavo\TrinaCrud\Utilities;

class StringHelper
{
    /**
     * Convert a string from snake_case to camelCase
     *
     * @param string $string The string to convert
     * @param bool $capitalizeFirstChar Whether to capitalize the first character
     * @return string
     */
    public function toCamelCase(string $string, bool $capitalizeFirstChar = false): string
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        
        if (!$capitalizeFirstChar) {
            $str = lcfirst($str);
        }
        
        return $str;
    }
    
    /**
     * Convert a string from camelCase to snake_case
     *
     * @param string $string The string to convert
     * @return string
     */
    public function toSnakeCase(string $string): string
    {
        if (strpos($string, '_') !== false) {
            // Already snake_case
            return $string;
        }
        
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }
} 