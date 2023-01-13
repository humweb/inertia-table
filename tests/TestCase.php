<?php

namespace Humweb\Table\Tests;

use Hammerstone\FastPaginate\FastPaginateProvider;
use Humweb\Table\InertiaTableServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Humweb\\Table\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            InertiaTableServiceProvider::class,
            FastPaginateProvider::class
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $app['db']->connection()->getSchemaBuilder()->create('test_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->timestamps();
        });
    }

    protected function request(callable $callback = null): Request
    {
        $request = Request::createFromGlobals();

        return $callback ? tap($request, $callback) : $request;
    }

    protected function assertQueryLogContains(string $partialSql)
    {
        $queryLog = collect(DB::getQueryLog())->map(function ($q) {
            $bindings = collect($q['bindings'])->map(fn ($b) => is_numeric($b) ? $b : "'".$b."'")->all();

            return Str::replaceArray('?', $bindings, $q['query']);
        })->implode('|');

        $this->assertStringContainsString($partialSql, $queryLog);
    }

    protected function assertQueryLogDoesntContain(string $partialSql)
    {
        $queryLog = collect(DB::getQueryLog())->pluck('query')->implode('|');


        $this->assertStringNotContainsString($partialSql, $queryLog);
    }

    public function sortCallback(Builder $query, $descending): void
    {
        $query->orderBy('name', $descending ? 'DESC' : 'ASC');
    }

    public function filterCallback(Builder $query, $value): void
    {
        $query->where('name', $value);
    }
}
