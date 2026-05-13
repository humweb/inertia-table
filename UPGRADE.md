# Upgrade Guide

## Upgrading from v1 to v2

### Breaking Changes

#### Resource constructor signature changed

The `Resource` constructor now accepts `(Request $request, array $parameters = [])`. If you were passing a driver or other arguments, remove them:

```php
// Before
UserResource::make($request, 'mysql');

// After
UserResource::make($request);
```

The `$driver` property has been removed. All database-specific behavior (e.g. `ILIKE` vs `LIKE`) is now handled automatically.

#### InertiaTable requires TableRequest

`InertiaTable` now requires a `TableRequest` instance instead of a raw `Request`:

```php
// Before
new InertiaTable($request);

// After
new InertiaTable(new TableRequest($request));
```

In practice, you won't need to construct `InertiaTable` directly — the `->table()` response macro handles this for you.

#### `withProps()` removed from InertiaTable

The `withProps()` method has been removed. Use the `->table()` response macro instead, which calls `resolve()` internally:

```php
// Before
return Inertia::render('Users/Index', $table->withProps());

// After
return Inertia::render('Users/Index')
    ->table(fn (InertiaTable $table) =>
        UserResource::make($request)->toResponse($table)
    );
```

#### `buildQuery()` refactored to use QueryPipeline

If you were overriding `buildQuery()` in a resource, the monolithic method has been replaced by a pipeline of discrete stages. Override `pipeline()` instead:

```php
// Before
public function buildQuery(): void
{
    parent::buildQuery();
    $this->query->where('active', true);
}

// After
protected function pipeline(QueryPipeline $pipeline): QueryPipeline
{
    $pipeline->after(ApplyFilters::class, new class implements QueryStage {
        public function handle(Builder $query, TableRequest $request, Closure $next): Builder
        {
            $query->where('active', true);
            return $next($query);
        }
    });

    return $pipeline;
}
```

#### `Inertia::lazy()` / `LazyProp` usage removed

Table props are now registered as plain closures. Inertia evaluates closures on the initial visit and only re-evaluates them on partial reloads that target the specific prop key.

#### `applySearch`, `applyDefaultSort`, `applyGlobalFilter` methods removed

These methods were on the `HasResourceQueries` trait. Their logic is now in individual pipeline stages:

| Removed method | Replacement stage |
|---|---|
| `applySearch()` | `ApplySearch` |
| `applyDefaultSort()` | `ApplyDefaultSort` |
| `applyGlobalFilter()` | `ApplyGlobalSearch` |
| `applyFilters()` | `ApplyFilters` |

#### `UserResource` example class removed

The `Humweb\Table\UserResource` example class was removed from the package. If you were referencing it, create your own resource instead.

### New Features

#### Multi-table support

You can now render multiple independent tables on a single page. Each table has its own key and query parameter namespace:

```php
return Inertia::render('Dashboard')
    ->table('users', fn (InertiaTable $table) =>
        UserResource::make($request)->toResponse($table)
    )
    ->table('orders', fn (InertiaTable $table) =>
        OrderResource::make($request)->toResponse($table)
    );
```

Query parameters are automatically prefixed: `?users.sort=name&orders.page=2`. The `default` key uses unprefixed params for backwards compatibility.

Each table is a lazy closure. Partial reloads with `only: ['tables.users']` ensure only the targeted table re-queries.

On the frontend:

```vue
<DataTable table-key="users" />
<DataTable table-key="orders" />
```

#### Relationship and aggregate sorting

New sort strategies allow sorting on related models and aggregates:

```php
use Humweb\Table\Sorts\{PowerJoinSort, AggregateSort, SubquerySort, CallbackSort, NullsLastSort};

Text::make('Team')
    ->sortable(new PowerJoinSort('team', 'name'));

Text::make('Orders')
    ->sortable(new AggregateSort('orders', 'count'));

Text::make('Revenue')
    ->sortable(new AggregateSort('orders', 'sum', 'total'));

Text::make('Custom')
    ->sortable(new SubquerySort(function (Builder $query, bool $descending) {
        $query->orderBy(
            Order::select('total')->whereColumn('orders.user_id', 'users.id')->limit(1),
            $descending ? 'desc' : 'asc'
        );
    }));
```

#### `sortField` now applied server-side

The `sortField()` modifier on fields is now properly used during sorting. If you have `Text::make('Name')->sortField('name_lower')->sortable()`, the sort will use `name_lower` as the column:

```php
Text::make('Display Name')
    ->sortField('name_normalized')
    ->sortable();
```

#### QueryPipeline for extensibility

The query building process is now a pipeline of `QueryStage` objects. You can insert, replace, or reorder stages:

```php
protected function pipeline(QueryPipeline $pipeline): QueryPipeline
{
    $pipeline->before(ApplySorts::class, new MyPreSortStage());
    $pipeline->replace(ApplyGlobalSearch::class, new MySearch());
    $pipeline->after(ApplyFilters::class, new TenantScope($this->tenantId));

    return $pipeline;
}
```

#### Global search uses OR semantics

Global search now wraps searchable columns in a `WHERE (... OR ... OR ...)` group instead of using AND. This matches expected search behavior.

#### EmptyNotEmptyFilter SQL precedence fix

The `EmptyNotEmptyFilter` now wraps its `whereNull` / `orWhere` conditions in a grouped `where()` closure, preventing unintended row inclusion from SQL precedence issues.

#### Collection-based sorting

For sorting on computed or transformed values that can't be expressed in SQL:

```php
use Humweb\Table\Sorts\{BasicCollectionSort, CallbackCollectionSort, SortMode};

Text::make('Score')
    ->sortable(new BasicCollectionSort(SortType::Integer), SortMode::Collection);

Text::make('Rank')
    ->sortable(new CallbackCollectionSort(function ($collection, $descending, $attribute) {
        return $descending
            ? $collection->sortByDesc($attribute)
            : $collection->sortBy($attribute);
    }), SortMode::Collection);
```

### Migration Checklist

1. Update any direct `InertiaTable` construction to use `TableRequest`.
2. Replace `withProps()` calls with the `->table()` response macro.
3. Replace `buildQuery()` overrides with `pipeline()` overrides.
4. If you referenced `Humweb\Table\UserResource`, create your own resource.
5. Frontend: import from `@/components/Table/v2` and use `<DataTable>` or `useTable()`.
6. Multi-table: pass a string key to `->table('key', fn ...)` and add `table-key="key"` to `<DataTable>`.
