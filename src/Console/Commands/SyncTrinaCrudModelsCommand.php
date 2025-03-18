<?php

namespace Trinavo\TrinaCrud\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use ReflectionClass;
use Trinavo\TrinaCrud\Models\TrinaCrudModel;
use Throwable;

class SyncTrinaCrudModelsCommand extends Command
{
    protected $signature = 'trinacrud:sync-models';
    protected $description = 'Sync models that extend TrinaCrudModel with the trinacrud_models table';

    public function handle()
    {
        $this->info('🔍 Scanning for models that extend TrinaCrudModel...');

        $paths = Config::get('trinacrud.model_paths', []);
        $paths[] = base_path('vendor/trinavo/laravel-trinacrud/src/Models');
        $paths = array_filter($paths);
        $paths = array_unique($paths);

        if (empty($paths)) {
            $this->warn('⚠️ No paths configured for scanning.');
            return;
        }

        $flexiCrudModels = [];
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

                    if ($reflection->isSubclassOf('Trinavo\TrinaCrud\Support\TrinaCrudModel') && !$reflection->isAbstract()) {
                        $modelName = $reflection->getShortName();
                        $trinaCrudModels[] = $class;

                        TrinaCrudModel::firstOrCreate(
                            ['class_name' => $class],
                            [
                                'caption' => Str::title(Str::snake($modelName, ' ')),
                                'multi_caption' => Str::plural(Str::title(Str::snake($modelName, ' '))),
                                'page_size' => 20,
                            ]
                        );

                        $this->call('trinacrud:sync-columns', ['modelName' => $class]);

                        $this->info("✅ Synced: $modelName");
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
