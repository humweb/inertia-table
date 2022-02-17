<template>
    <div>
        <div class="flex space-x-4 my-4">

            <slot
                name="tableGlobalSearch"
                :search="search"
                :changeGlobalSearchValue="changeGlobalSearchValue"
            >
                <div v-if="search && search.global"
                     class="flex-grow min-w-min">
                    <TableGlobalSearch
                        :value="search.global.value"
                        :on-change="changeGlobalSearchValue"
                    />
                </div>
            </slot>


            <slot
                name="tableFilter"
                :hasFilters="hasFilters"
                :filters="filters"
                :changeFilterValue="changeFilterValue"
            >
                <TableFilter v-if="hasFilters" :filters="filters" :on-change="changeFilterValue"/>
            </slot>
            <slot
                name="tableAddSearchRow"
                :hasSearchRows="hasSearchRows"
                :search="search"
                :newSearch="newSearch"
                :enableSearch="enableSearch"
            >
                <TableAddSearchRow
                    v-if="hasSearchRows"
                    :rows="search"
                    :new="newSearch"
                    :on-add="enableSearch"
                />
            </slot>
            <slot
                name="tableColumns"
                :hasColumns="hasColumns"
                :columns="columns"
                :changeColumnStatus="changeColumnStatus"
            >
                <TableColumns v-if="hasColumns" :columns="columns" :on-change="changeColumnStatus"/>
            </slot>
            <TableDownload
                ref="rows"
                v-if="hasDownload"
                :on-change="changeDownloadValue"
            />
        </div>

        <slot
            name="tableSearchRows"
            :hasSearchRows="hasSearchRows"
            :search="search"
            :newSearch="newSearch"
            :disableSearch="disableSearch"
            :changeSearchValue="changeSearchValue"
        >
            <TableSearchRows
                ref="rows"
                v-if="hasSearchRows"
                :rows="search"
                :new="newSearch"
                :on-remove="disableSearch"
                :on-change="changeSearchValue"
            />


        </slot>

        <slot name="tableWrapper" :meta="meta">
            <TableWrapper :class="{'mt-2': !onlyData}">
                <slot name="table">
                    <table class="table table-responsive-xl text-sm">
                        <thead class="bg-gray-50">
                        <slot name="head"/>
                        </thead>

                        <tbody class="bg-white text-gray-800 divide-y divide-gray-200">
                        <slot name="body"/>
                        </tbody>
                    </table>
                </slot>

                <slot name="pagination">
                    <Pagination :meta="paginationMeta"/>
                </slot>
            </TableWrapper>
        </slot>
    </div>
</template>

<script>
import Pagination from './Pagination';
import TableAddSearchRow from './TableAddSearchRow';
import TableColumns from './TableColumns';
import TableFilter from './TableFilter';
import TableGlobalSearch from './TableGlobalSearch';
import TableSearchRows from './TableSearchRows';
import TableDownload from './TableDownload';
import TableWrapper from './TableWrapper';

export default {

    components: {
        Pagination,
        TableAddSearchRow,
        TableColumns,
        TableFilter,
        TableGlobalSearch,
        TableSearchRows,
        TableWrapper,
        TableDownload
    },
    props: {
        meta: {
            type: Object,
            default: () => {
                return {};
            },
            required: false,
        },

        columns: {
            type: Object,
            default: () => {
                return {};
            },
            required: false,
        },

        filters: {
            type: Object,
            default: () => {
                return {};
            },
            required: false,
        },
        hasDownload: {
            type: Boolean,
            default: false,
            required: false,
        },

        search: {
            type: Object,
            default: () => {
                return {};
            },
            required: false,
        },
        download: {
            type: [String,Number],
            default: 0
        },
        filename: {
            type: [String],
            default: ''
        },

        onUpdate: {
            type: Function,
            required: false,
        },
    },

    computed: {
        hasFilters() {
            return Object.keys(this.filters || {}).length > 0;
        },

        hasColumns() {
            return Object.keys(this.columns || {}).length > 0;
        },

        hasSearchRows() {
            return Object.keys(this.search || {}).length > 0;
        },

        hasBody() {
            return !!this.$slots.body;
        },

        onlyData() {
            if (this.hasFilters || this.hasColumns || this.hasSearchRows) {
                return false;
            }

            if (!this.search) {
                return true;
            }

            return this.search.global ? false : true;
        },

        paginationMeta() {
            if (this.hasBody) {
                return this.meta;
            }

            const hasPagination = 'meta' in this.meta || ('total' in this.meta && 'to' in this.meta && 'from' in this.meta);

            if (hasPagination) {
                return this.meta;
            }

            return {meta: {total: 0}};
        },
    },

    data() {
        return {
            newSearch: [],
            queryBuilderData: {
                columns: this.columns,
                filters: this.filters,
                search: this.search,
                download: this.download,
                filename: this.filename,
            },
        };
    },

    methods: {
        disableSearch(key) {
            this.newSearch = this.newSearch.filter((search) => search != key);

            this.queryBuilderData.search[key].enabled = false;
            this.queryBuilderData.search[key].value = null;
        },

        enableSearch(key) {
            this.queryBuilderData.search[key].enabled = true;
            this.newSearch.push(key);

            this.$nextTick(() => {
                this.$refs['rows'].focus(key);
            });
        },

        //

        changeDownloadValue(value, filename) {
            if (this.meta.total <= 500) {
                this.queryBuilderData.download = value;
                this.queryBuilderData.filename = filename;
            }
            else {}
        },

        changeSearchValue(key, value) {
            this.queryBuilderData.search[key].value = value;
        },

        changeGlobalSearchValue(value) {
            this.changeSearchValue('global', value);
        },

        changeFilterValue(key, value) {
            this.queryBuilderData.filters[key].value = value;
        },

        changeColumnStatus(column, status) {
            this.queryBuilderData.columns[column].enabled = status;
        },
    },

    watch: {
        queryBuilderData: {
            deep: true,
            handler(value, old) {
                if (this.onUpdate) {
                    this.onUpdate(this.queryBuilderData);
                }
                this.queryBuilderData.download = 0
            },
        },
    },
};
</script>
