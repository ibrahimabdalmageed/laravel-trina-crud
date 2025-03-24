<?php

namespace Trinavo\TrinaCrud\Tests;

use Livewire\LivewireServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Trinavo\TrinaCrud\Providers\TrinaCrudServiceProvider;

abstract class TestCase extends BaseTestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            TrinaCrudServiceProvider::class,
            LivewireServiceProvider::class,
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
