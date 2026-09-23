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

        /**
         * Register a table on the Inertia response.
         *
         * Supports multiple tables per page:
         *   ->table('users', fn ($table) => ...)
         *   ->table('roles', fn ($table) => ...)
         *
         * Single-table shorthand (key defaults to 'default'):
         *   ->table(fn ($table) => ...)
         *
         * The request is resolved per call, not captured at boot: the container
         * rebinds it for every request (tests, Octane), and a boot-time copy
         * silently ignores that request's sort, search, filters and page.
         */
        $app = $this->app;

        Response::macro('table', function (string|callable $keyOrHandler, ?callable $handler = null) use ($app) {
            /** @var Response $this */
            if (is_callable($keyOrHandler)) {
                $key = 'default';
                $handler = $keyOrHandler;
            } else {
                $key = $keyOrHandler;
            }

            $tableRequest = new TableRequest($app['request'], $key);
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
