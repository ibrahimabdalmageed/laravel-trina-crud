<?php

namespace Trinavo\TrinaCrud\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use ReflectionClass;
use Trinavo\TrinaCrud\Models\TrinaCrudModel;
use Trinavo\TrinaCrud\Traits\HasCrud;
use Throwable;
use Trinavo\TrinaCrud\Services\TrinaCrudModelHelper;

class SyncTrinaCrudModelsCommand extends Command
{
    protected $signature = 'trinacrud:sync-models';
    protected $description = 'Sync models that extend TrinaCrudModel with the trinacrud_models table';

    public function handle(TrinaCrudModelHelper $modelHelper)
    {
        $this->info('🔍 Scanning for models that extend TrinaCrudModel...');

        $paths = Config::get('trina-crud.model_paths', []);
        $paths[] = base_path('vendor/trinavo/laravel-trina-crud/src/Models');
        $paths = array_filter($paths);
        $paths = array_unique($paths);

        if (empty($paths)) {
            $this->warn('⚠️ No paths configured for scanning.');
            return;
        }

        foreach ($paths as $path) {
            $this->info("🔍 Scanning: $path");
            if (!is_dir($path)) {
                $this->warn("⚠️ Path does not exist: $path");
                continue;
            }

            $files = $this->getPhpFiles($path);

            foreach ($files as $file) {
                $class = $this->getClassFromFile($file);

                try {
                    $reflection = new ReflectionClass($class);

                    if (in_array(HasCrud::class, class_uses_recursive($class)) && !$reflection->isAbstract()) {
                        $modelShortName = $reflection->getShortName();
                        $trinaCrudModels[] = $class;

                        TrinaCrudModel::firstOrCreate(
                            ['class_name' => $class],
                            [
                                'model_name' => $modelHelper->makeModelNameFromClass($class),
                                'model_short' => $modelShortName,
                                'caption' => Str::title(Str::snake($modelShortName, ' ')),
                                'multi_caption' => Str::plural(Str::title(Str::snake($modelShortName, ' '))),
                                'page_size' => 20,
                            ]
                        );

                        $this->call('trinacrud:sync-columns', ['modelName' => $class]);

                        $this->info("✅ Synced: $modelShortName");
                    }
                } catch (Throwable $e) {
                    $this->warn("⚠️ Reflection failed for: {$class} - {$e->getMessage()}");
                }
            }
        }

        // Remove models that no longer exist
        $modelsToDelete = TrinaCrudModel::whereNotIn('class_name', $trinaCrudModels)->get();
        foreach ($modelsToDelete as $model) {
            $model->delete();
            $this->warn("🗑️ Removed: {$model->class_name}");
        }

        $this->info('✅ Model synchronization complete.');
    }

    private function getPhpFiles(string $directory): array
    {
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        $files = [];

        foreach ($rii as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function getClassFromFile(string $file): ?string
    {
        $contents = file_get_contents($file);
        if (!$contents) {
            return null;
        }

        $namespace = null;
        $class = null;

        if (preg_match('/^namespace\s+([^;]+);/m', $contents, $matches)) {
            $namespace = trim($matches[1]);
        }

        if (preg_match('/^class\s+([^\s]+)/m', $contents, $matches)) {
            $class = trim($matches[1]);
        }

        return $class && $namespace ? "{$namespace}\\{$class}" : null;
    }
}
