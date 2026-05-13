<?php

declare(strict_types=1);

namespace Humweb\Table;

use Illuminate\Support\ServiceProvider;
use Inertia\Response;

class InertiaTableServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/inertia-table.php', 'inertia-table');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/inertia-table.php' => config_path('inertia-table.php'),
            ], 'inertia-table-config');
        }

        $request = $this->app['request'];

        /**
         * Register a table on the Inertia response.
         *
         * Supports multiple tables per page:
         *   ->table('users', fn ($table) => ...)
         *   ->table('roles', fn ($table) => ...)
         *
         * Single-table shorthand (key defaults to 'default'):
         *   ->table(fn ($table) => ...)
         */
        Response::macro('table', function (string|callable $keyOrHandler, ?callable $handler = null) use ($request) {
            /** @var Response $this */
            if (is_callable($keyOrHandler)) {
                $key = 'default';
                $handler = $keyOrHandler;
            } else {
                $key = $keyOrHandler;
            }

            $tableRequest = new TableRequest($request, $key);
            $tableBuilder = new InertiaTable($tableRequest);

            $propKey = $key === 'default' ? 'table' : "tables.{$key}";

            $this->with($propKey, function () use ($handler, $tableBuilder) {
                if ($handler) {
                    $handler($tableBuilder);
                }

                return $tableBuilder->resolve();
            });

            return $this;
        });
    }
}
