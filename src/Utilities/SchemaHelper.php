<?php

namespace Trinavo\TrinaCrud\Utilities;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SchemaHelper
{
    /**
     * Get column information using Laravel's Schema Builder
     *
     * @param string $table
     * @param string $columnName
     * @return array|null
     */
    public static function getColumnInfo(string $table, string $columnName): ?array
    {
        if (!Schema::hasColumn($table, $columnName)) {
            return null;
        }

        $type = Schema::getColumnType($table, $columnName);

        // Get column details using Doctrine Schema Manager
        try {
            $doctrineColumn = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableDetails($table)
                ->getColumn($columnName);

            $required = !$doctrineColumn->getNotnull();
            $default = $doctrineColumn->getDefault();

            // Try to determine size for the column
            $size = null;
            if (method_exists($doctrineColumn, 'getLength')) {
                $size = $doctrineColumn->getLength();
            } elseif (method_exists($doctrineColumn, 'getPrecision')) {
                $size = $doctrineColumn->getPrecision();
            }
        } catch (\Exception $e) {
            // Fallback to driver-specific methods if Doctrine fails
            $required = false;
            $default = null;
            $size = null;

            // Driver-specific fallbacks for size determination
            if (DB::getDriverName() === 'mysql') {
                $columnData = DB::selectOne("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$columnName]);
                if ($columnData) {
                    $required = $columnData->Null === 'NO';
                    $default = $columnData->Default;
                    if (preg_match('/\((\d+)\)/', $columnData->Type, $matches)) {
                        $size = (int) $matches[1];
                    }
                }
            } elseif (DB::getDriverName() === 'pgsql') {
                $columnData = DB::selectOne(
                    "
                    SELECT is_nullable, column_default, character_maximum_length 
                    FROM information_schema.columns 
                    WHERE table_name = ? AND column_name = ?",
                    [$table, $columnName]
                );
                if ($columnData) {
                    $required = $columnData->is_nullable === 'NO';
                    $default = $columnData->column_default;
                    $size = $columnData->character_maximum_length;
                }
            } elseif (DB::getDriverName() === 'sqlite') {
                $columnData = DB::selectOne("PRAGMA table_info({$table}) WHERE name = ?", [$columnName]);
                if ($columnData) {
                    $required = $columnData->notnull === 1;
                    $default = $columnData->dflt_value;
                    // SQLite doesn't provide direct size information
                }
            }
        }

        return [
            'type' => $type,
            'required' => $required,
            'default' => $default,
            'size' => $size,
        ];
    }

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
