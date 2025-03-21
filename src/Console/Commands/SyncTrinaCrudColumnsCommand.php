<?php

namespace Trinavo\TrinaCrud\Console\Commands;

use Illuminate\Console\Command;
use Trinavo\TrinaCrud\Utilities\SchemaHelper;
use Trinavo\TrinaCrud\Models\TrinaCrudModel;
use Trinavo\TrinaCrud\Models\TrinaCrudColumn;

class SyncTrinaCrudColumnsCommand extends Command
{
    protected $signature = 'trinacrud:sync-columns {model}';
    protected $description = 'Sync columns for a specific model with the trinacrud_columns table';

    public function handle()
    {
        $modelClass = $this->argument('model');
        $trinaCrudModel = TrinaCrudModel::where('class_name', $modelClass)->first();

        if (!$trinaCrudModel) {
            $this->error("❌ Model '{$modelClass}' not found in trinacrud_models table.");
            return;
        }

        if (!class_exists($modelClass)) {
            $this->error("❌ Model class '{$modelClass}' does not exist.");
            return;
        }

        $model = app($modelClass);
        $table = $model->getTable();
        $columns = SchemaHelper::getColumnListing($table);
        $existingColumns = TrinaCrudColumn::where('trina_crud_model_id', $trinaCrudModel->id)->pluck('column_name')->toArray();

        foreach ($columns as $columnName) {
            $columnInfo = SchemaHelper::getColumnInfo($table, $columnName);

            if (!$columnInfo) {
                $this->warn("⚠️ Could not retrieve column information for: {$columnName}");
                continue;
            }

            $data = [
                'trina_crud_model_id' => $trinaCrudModel->id,
                'column_name' => $columnName,
                'column_db_type' => $columnInfo['type'],
                'column_user_type' => null, // Can be set later
                'column_label' => ucwords(str_replace('_', ' ', $columnName)),
                'required' => $columnInfo['required'],
                'default_value' => $columnInfo['default'],
                'grid_order' => null,
                'edit_order' => null,
                'size' => $columnInfo['size'],
                'hide' => false,
            ];

            TrinaCrudColumn::updateOrCreate(
                ['trina_crud_model_id' => $trinaCrudModel->id, 'column_name' => $columnName],
                $data
            );

            $this->info("✅ Synced column: {$columnName}");
        }

        // Remove columns that no longer exist in the table
        $columnsToDelete = array_diff($existingColumns, $columns);

        if (!empty($columnsToDelete)) {
            TrinaCrudColumn::where('trina_crud_model_id', $trinaCrudModel->id)
                ->whereIn('column_name', $columnsToDelete)
                ->delete();

            foreach ($columnsToDelete as $deletedColumn) {
                $this->warn("🗑️ Removed column: {$deletedColumn}");
            }
        }

        $this->info('✅ Column synchronization complete.');
    }
}
