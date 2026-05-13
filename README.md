# Inertia Table

[![run-tests](https://github.com/humweb/inertia-table/actions/workflows/run-tests.yml/badge.svg)](https://github.com/humweb/inertia-table/actions/workflows/run-tests.yml)

Server-driven data tables for Laravel + Inertia.js + Vue 3. Define your columns, filters, sorts, and search on the backend — the frontend renders it all automatically with per-table partial reloads.

## Installation

```bash
composer require humweb/inertia-table
```

Publish the config (optional):

```bash
php artisan vendor:publish --tag="inertia-table-config"
```

## Quick Start

### 1. Define a Resource

A Resource declares your table's columns, filters, model, and query behavior:

```php
use Humweb\Table\Resource;
use Humweb\Table\Fields\{FieldCollection, ID, Text, Badge};
use Humweb\Table\Filters\{FilterCollection, SelectFilter, TextFilter};

class UserResource extends Resource
{
    protected string $model = User::class;
    public string|Sort $defaultSort = 'name';
    protected array $with = ['team'];

    public function fields(): FieldCollection
    {
        return FieldCollection::make([
            ID::make('ID')->sortable(),
            Text::make('Name')->sortable()->searchable(),
            Text::make('Email')->sortable()->searchable(),
            Badge::make('Status')->sortable()->meta([
                'map' => [
                    'active' => ['label' => 'Active', 'class' => 'badge-green'],
                    'inactive' => ['label' => 'Inactive', 'class' => 'badge-gray'],
                ],
            ]),
        ]);
    }

    public function filters(): FilterCollection
    {
        return FilterCollection::make([
            SelectFilter::make('status', 'Status', [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ]),
            TextFilter::make('name', 'Name'),
        ]);
    }
}
```

### 2. Use in a Controller

#### Single table

```php
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Users/Index')
            ->table(fn (InertiaTable $table) =>
                UserResource::make($request)->toResponse($table)
            );
    }
}
```

#### Multiple tables on one page

```php
public function index(Request $request)
{
    return Inertia::render('Staff/Teams/Show', [
        'team' => $team,
    ])
        ->table('members', fn (InertiaTable $table) =>
            MemberResource::make($request)->toResponse($table)
        )
        ->table('invitations', fn (InertiaTable $table) =>
            InvitationResource::make($request)->toResponse($table)
        );
}
```

Each table is a lazy closure, so when the frontend does a partial reload targeting one table (e.g. `only: ['tables.members']`), only that table's query runs — the other stays untouched.

### 3. Frontend (Vue 3)

#### Single table

```vue
<script setup lang="ts">
import { DataTable } from '@/components/Table/v2'
</script>

<template>
  <DataTable />
</template>
```

#### Multiple tables

```vue
<script setup lang="ts">
import { DataTable } from '@/components/Table/v2'
</script>

<template>
  <DataTable table-key="members" />
  <DataTable table-key="invitations" />
</template>
```

#### Using the composable directly

```vue
<script setup lang="ts">
import { useTable } from '@/components/Table/v2'

const members = useTable('members')
const invitations = useTable('invitations')
</script>

<template>
  <input
    :value="members.search.value.global?.value ?? ''"
    @input="members.updateGlobalSearch(($event.target as HTMLInputElement).value)"
  />
  <div v-for="record in members.records.value" :key="record.id">
    {{ record.name }}
  </div>
</template>
```

---

## Backend API

### Resource

Extend `Humweb\Table\Resource` to define a table. Required methods:

| Method | Returns | Purpose |
|---|---|---|
| `fields()` | `FieldCollection` | Column definitions |
| `filters()` | `FilterCollection` | Filter definitions (optional, defaults to empty) |

Key properties:

| Property | Type | Default | Purpose |
|---|---|---|---|
| `$model` | `string` | — | Eloquent model class |
| `$defaultSort` | `string\|Sort` | `'id'` | Default sort column or Sort instance |
| `$with` | `array` | `[]` | Eager-loaded relationships |
| `$primaryKey` | `string` | `'id'` | Record identifier |
| `$parameters` | `array` | `[]` | Route parameters passed to custom filters |

#### Custom parameter filters

Define `filter{StudlyKey}($value)` methods on your resource. Parameters set via `addParameter()` auto-dispatch to these methods:

```php
$resource->addParameter('team_id', $team->id);

// In resource:
public function filterTeamId($value): void
{
    $this->query->where('team_id', $value);
}
```

#### Custom global search

Override `globalFilter()` to replace the default OR-across-searchable-fields behavior:

```php
public function globalFilter($query, $value): void
{
    $query->where(function ($q) use ($value) {
        $q->where('name', 'ilike', "%{$value}%")
          ->orWhere('email', 'ilike', "%{$value}%");
    });
}
```

#### Runtime transforms

```php
$resource->runtimeTransform(function ($record) {
    $record['full_name'] = $record['first_name'] . ' ' . $record['last_name'];
    return $record;
});
```

### Fields

All fields extend `Humweb\Table\Fields\Field` and use the `make()` static constructor.

#### Available field types

| Class | Component | Purpose |
|---|---|---|
| `ID` | `id-field` | Primary key |
| `Text` | `text-field` | Text column |
| `Textarea` | `textarea-field` | Long text |
| `Number` | `number-field` | Numeric |
| `Date` | `date-field` | Date/datetime |
| `Boolean` | `boolean-field` | True/false badge |
| `Badge` | `badge-field` | Status badge with map |
| `Currency` | `currency-field` | Formatted currency |
| `Percent` | `percent-field` | Progress bar |
| `Image` | `image-field` | Image thumbnail |
| `Avatar` | `avatar-field` | Round avatar |
| `Link` | `link-field` | Clickable link |
| `Relation` | `relation-field` | Related model link |
| `Computed` | `computed-field` | Server-computed value |
| `Actions` | `action-field` | Row action buttons |

#### Field modifiers

```php
Text::make('Name')
    ->sortable()                          // Enable server-side sorting (BasicSort)
    ->sortable(new PowerJoinSort('team', 'name'))  // Sort via relation
    ->sortable(new AggregateSort('posts', 'count')) // Sort by withCount
    ->sortableOnClient()                  // Client-side sort (no server round-trip)
    ->sortField('name_lower')             // Sort on a different column than display
    ->searchable()                        // Include in column search
    ->visible(false)                      // Hidden by default
    ->visibility(true)                    // Allow toggling visibility
    ->nullable()                          // Mark as nullable
    ->meta(['tooltip' => 'Full name'])    // Arbitrary metadata sent to frontend
```

### Filters

All filters extend `Humweb\Table\Filters\Filter`.

| Class | Component | Purpose |
|---|---|---|
| `TextFilter` | `text-filter` | Free text input |
| `SelectFilter` | `select-filter` | Dropdown select |
| `BooleanFilter` | `boolean-filter` | Yes/No/Any |
| `DateRangeFilter` | `date-range-filter` | From/to date picker |
| `NumberRangeFilter` | `number-range-filter` | Min/max number |
| `EnumFilter` | `enum-filter` | Enum value select |
| `ScopeFilter` | `scope-filter` | Named query scope |
| `RelationshipFilter` | `relationship-filter` | Filter by related model |
| `EmptyNotEmptyFilter` | `empty-filter` | Null/empty check |
| `TrashedFilter` | `select-filter` | Soft delete filter |

#### Filter modifiers

```php
TextFilter::make('name', 'Name')
    ->exact()                 // Exact match instead of LIKE
    ->startsWith()            // LIKE 'value%'
    ->endsWith()              // LIKE '%value'
    ->fullSearch()            // LIKE '%value%' (default)
    ->relation('team', 'name') // Filter within a relationship
    ->rules('string|max:100') // Validation rules
```

### Sort Strategies

Sorts implement `Humweb\Table\Sorts\Sort` and are passed to `->sortable()`:

| Class | Purpose | Example |
|---|---|---|
| `BasicSort` | Simple `ORDER BY` (default). Delegates to Power Joins for dotted paths. | `->sortable()` |
| `PowerJoinSort` | Sort by a column on a related model via Power Joins. | `->sortable(new PowerJoinSort('author', 'name'))` |
| `AggregateSort` | Sort by `withCount`, `withSum`, `withAvg`, etc. | `->sortable(new AggregateSort('orders', 'sum', 'total'))` |
| `SubquerySort` | Sort by an arbitrary subquery (escape hatch). | `->sortable(new SubquerySort(fn ($q) => ...))` |
| `CallbackSort` | Sort via a custom callback. | `->sortable(new CallbackSort(fn ($q, $desc, $prop) => ...))` |
| `NullsLastSort` | Sort with NULLs always at the bottom. | `->sortable(new NullsLastSort())` |

#### Collection sorts (client-side on server)

For sorts that require fetching all records and sorting in PHP (e.g. computed values):

| Class | Purpose |
|---|---|
| `BasicCollectionSort` | Sort a collection with auto type detection |
| `CallbackCollectionSort` | Custom collection sort callback |

```php
Text::make('Score')
    ->sortable(new BasicCollectionSort(SortType::Integer), SortMode::Collection)
```

### Query Pipeline

The `Resource` builds queries through a `QueryPipeline` of discrete `QueryStage` objects. The default pipeline runs these stages in order:

1. `ApplyEagerLoads` — `$with` relationships
2. `ApplyDefaultSort` — fallback sort when no `?sort=` param
3. `ApplySorts` — user-requested sort from `?sort=` param
4. `ApplyGlobalSearch` — `?search[global]=` (OR across searchable fields)
5. `ApplyCustomFilters` — parameter-based `filter*()` methods
6. `ApplySearch` — per-column `?search[name]=`
7. `ApplyFilters` — `FilterCollection` application from `?filters[status]=`

#### Customizing the pipeline

Override `pipeline()` in your resource to add, replace, or reorder stages:

```php
protected function pipeline(QueryPipeline $pipeline): QueryPipeline
{
    // Add a custom stage before sorting
    $pipeline->before(ApplySorts::class, new MyCustomStage());

    // Replace the default global search
    $pipeline->replace(ApplyGlobalSearch::class, new MyGlobalSearch());

    // Add a stage after filters
    $pipeline->after(ApplyFilters::class, new ApplyTenantScope($this->tenantId));

    return $pipeline;
}
```

#### Creating custom stages

Implement `QueryStage`:

```php
use Humweb\Table\Pipeline\QueryStage;
use Humweb\Table\TableRequest;
use Illuminate\Database\Eloquent\Builder;

class ApplyTenantScope implements QueryStage
{
    public function __construct(private int $tenantId) {}

    public function handle(Builder $query, TableRequest $request, Closure $next): Builder
    {
        $query->where('tenant_id', $this->tenantId);

        return $next($query);
    }
}
```

### TableRequest

`TableRequest` wraps the HTTP request with table-key awareness. For the `default` key, params are unprefixed (`?sort=name`). For named keys, params are prefixed (`?members.sort=name`).

```php
$tableRequest = new TableRequest($request, 'members');
$tableRequest->getSortParam();    // reads ?members.sort=
$tableRequest->getSearchParams(); // reads ?members.search[...]=
$tableRequest->getFilterParams(); // reads ?members.filters[...]=
$tableRequest->getPage();         // reads ?members.page=
$tableRequest->getPerPage();      // reads ?members.perPage=
```

### Multi-Table Response Macro

The `->table()` macro on `Inertia\Response` supports two signatures:

```php
// Single table (key = 'default', prop = 'table')
->table(fn (InertiaTable $table) => ...)

// Named table (prop = 'tables.{key}')
->table('members', fn (InertiaTable $table) => ...)
->table('invitations', fn (InertiaTable $table) => ...)
```

Each table is registered as a lazy closure. On the initial page visit both resolve. On partial reloads (e.g. sorting/filtering), Inertia's `only` parameter ensures only the targeted table re-evaluates.

---

## Frontend API

All frontend code lives in `resources/js/components/Table/v2/`.

### `useTable(key?, options?)`

The core composable. Call it with a table key to bind to a specific table's data from the Inertia page props.

```typescript
import { useTable } from '@/components/Table/v2'

const table = useTable('members', {
  debounceMs: 300,
  preserveScroll: true,
  additionalOnly: ['team'],
})
```

#### Options

| Option | Type | Default | Purpose |
|---|---|---|---|
| `debounceMs` | `number` | `250` | Debounce delay for search/filter changes |
| `preserveScroll` | `boolean` | `true` | Preserve scroll position on reload |
| `additionalOnly` | `string[]` | `[]` | Extra Inertia `only` keys to include in partial reloads |

#### Return value

| Property | Type | Description |
|---|---|---|
| `key` | `string` | Table identifier |
| `sort` | `Ref<string \| null>` | Current sort (e.g. `'name'` or `'-name'`) |
| `page` | `Ref<number>` | Current page |
| `perPage` | `Ref<number>` | Items per page |
| `columns` | `ComputedRef<TableColumn[]>` | All column definitions |
| `visibleColumns` | `ComputedRef<TableColumn[]>` | Only visible columns |
| `filters` | `ComputedRef<TableFilterItem[]>` | Filter definitions with values |
| `search` | `ComputedRef<TableSearchMap>` | Search field state |
| `hasGlobalSearch` | `ComputedRef<boolean>` | Whether global search is available |
| `records` | `ComputedRef<T[]>` | Current records (client-sorted if applicable) |
| `pagination` | `ComputedRef<PaginationData>` | Pagination metadata |
| `isLoading` | `Ref<boolean>` | Request in-flight indicator |

#### Methods

| Method | Signature | Description |
|---|---|---|
| `handleSort` | `(attribute: string) => void` | Cycle sort: null -> asc -> desc -> null |
| `updateFilter` | `(key: string \| number, value: unknown) => void` | Set a filter value |
| `updateSearch` | `(key: string, value: unknown) => void` | Set a column search value |
| `updateGlobalSearch` | `(value: unknown) => void` | Set global search value |
| `enableSearch` | `(key: string) => void` | Enable a column search field |
| `removeSearch` | `(key: string) => void` | Disable and clear a search field |
| `setPage` | `(page: number) => void` | Navigate to page |
| `setPerPage` | `(perPage: number) => void` | Change per-page (resets to page 1) |
| `toggleColumnVisibility` | `(attribute: string, visible: boolean) => void` | Show/hide a column |
| `refresh` | `() => void` | Force reload this table |

### `<DataTable>` Component

The main component. Initializes `useTable` and provides it to child components via `provide('table')`.

```vue
<DataTable
  table-key="members"
  :enable-row-selection="true"
  selection-key="id"
  caption="Team members"
  aria-label="Team members table"
>
  <!-- Override any section with slots -->
  <template #toolbar="{ table }">
    <MyCustomToolbar :table="table" />
  </template>

  <template #cell:status="{ record, field }">
    <MyStatusBadge :status="record.status" />
  </template>

  <template #pagination="{ table }">
    <MyPagination :pagination="table.pagination.value" />
  </template>
</DataTable>
```

#### Props

| Prop | Type | Default | Description |
|---|---|---|---|
| `tableKey` | `string` | `'default'` | Table key matching the backend |
| `enableRowSelection` | `boolean` | `false` | Show row checkboxes |
| `selectionKey` | `string` | `'id'` | Record property for selection identity |
| `hideToolbar` | `boolean` | `false` | Hide the toolbar |
| `caption` | `string` | `''` | Accessible table caption |
| `ariaLabel` | `string` | `''` | Accessible table label |
| `options` | `UseTableOptions` | `{}` | Options forwarded to `useTable` |

#### Slots

| Slot | Scope | Description |
|---|---|---|
| `toolbar` | `{ table }` | Replace the entire toolbar |
| `table` | `{ table, records }` | Replace the entire table element |
| `head` | `{ columns, sortHandler, sort }` | Replace the `<thead>` |
| `body` | `{ records, columns }` | Replace the `<tbody>` |
| `cell:{attribute}` | `{ record, field }` | Override a specific column cell |
| `pagination` | `{ table }` | Replace pagination |

### Sub-components

All sub-components inject `useTable` via `inject('table')` and can be used standalone:

| Component | Purpose |
|---|---|
| `TableToolbar` | Search, filters, column visibility |
| `TableHeader` / `TableHeaderCell` | Sortable column headers |
| `TableBody` / `TableBodyCell` | Record rows with field rendering |
| `TablePagination` | Page navigation and per-page select |
| `FieldRenderer` | Resolves field component by `component` type |
| `FilterRenderer` | Resolves filter component by `component` type |
| `GlobalSearch` | Search input for global search |
| `ColumnSearch` | Active column search fields |
| `ColumnSearchDropdown` | Dropdown to enable column searches |

### Imports

```typescript
// Components
import { DataTable, TableHeader, TableBody, TablePagination } from '@/components/Table/v2'

// Composable
import { useTable } from '@/components/Table/v2'

// Types
import type { TableColumn, UseTableReturn, PaginationData } from '@/components/Table/v2'
```

---

## Configuration

```php
// config/inertia-table.php
return [
    'pagination' => [
        'max_per_page' => 100,
        'default_per_page' => 15,
    ],
];
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [ryun](https://github.com/humweb)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
