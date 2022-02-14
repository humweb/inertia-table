<?php

namespace Humweb\InertiaTable;

use Humweb\InertiaTable\Commands\InertiaTableCommand;
use Inertia\Response;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InertiaTableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('inertia-table')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_inertia-table_table')
            ->hasCommand(InertiaTableCommand::class);
    }

    public function boot()
    {
        Response::macro('table', function (callable $withTableBuilder = null) {
            $tableBuilder = new InertiaTable(request());

            if ($withTableBuilder) {
                $withTableBuilder($tableBuilder);
            }

            return $tableBuilder->shareProps($this);
        });
    }
}
