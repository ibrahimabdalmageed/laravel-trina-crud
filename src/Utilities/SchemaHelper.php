<?php

namespace Trinavo\TrinaCrud\Utilities;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SchemaHelper
{

    /**
     * Get table column listing across different database drivers
     *
     * @param string $table
     * @return array
     */
    public static function getColumnListing(string $table): array
    {
        return Schema::getColumnListing($table);
    }

    /**
     * Check if a table exists in the database
     *
     * @param string $table
     * @return bool
     */
    public static function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    /**
     * Get all tables from the current database
     *
     * @return array
     */
    public static function getAllTables(): array
    {
        return Schema::getAllTables();
    }
}
