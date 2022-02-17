
import qs from "qs";
import forEach from "lodash-es/forEach";
import isEqual from "lodash-es/isEqual";

import pickBy from "lodash-es/pickBy";

export default {
    props: {
        queryBuilderProps: {
            type: Object,
            required: true,
        },
    },

    data() {
        const queryBuilderData = {
            page: this.queryBuilderProps.page || 1,
            sort: this.queryBuilderProps.sort || "",
            filter: this.getFilterForQuery(
                this.queryBuilderProps.search || {},
                this.queryBuilderProps.filters || {}
            ),
            download: 0,
            columns: this.getColumnsForQuery(this.queryBuilderProps.columns || {}),
        };

        return {queryBuilderData, queryBuilderDataIteration: 0};
    },

    methods: {
        getFilterForQuery(search, filters) {
            let filtersWithValue = {};

            const filtersAndSearch = Object.assign({}, filters, search);

            forEach(filtersAndSearch, (filterOrSearch) => {
                if (filterOrSearch.value) {
                    filtersWithValue[filterOrSearch.key] = filterOrSearch.value;
                }
            });

            return filtersWithValue;
        },

        getColumnsForQuery(columns) {
            let enabledColumns = columns.filter((column) => {
                return column.enabled;
            });

            if (enabledColumns.length === Object.keys(columns || {}).length) {
                return [];
            }

            return enabledColumns.map((column) => {
                return column.key;
            });
        },

        staticHeader(columnKey) {
            return this._headerCellData(columnKey, false);
        },

        sortableHeader(columnKey) {
            return this._headerCellData(columnKey, true);
        },

        _headerCellData(columnKey, sortable) {
            let sort = false;

            if (this.queryBuilderData.sort === columnKey) {
                sort = "asc";
            } else if (this.queryBuilderData.sort === `-${columnKey}`) {
                sort = "desc";
            }

            let show = true;

            if (this.queryBuilderProps.columns) {
                const columnData = this.queryBuilderProps.columns[columnKey];

                show = columnData ? columnData.enabled : true;
            }

            return {
                key: columnKey,
                sort,
                show,
                sortable,
                onSort: this.sortBy,
            };
        },

        sortBy(column) {
            this.queryBuilderData.page = 1;
            this.queryBuilderData.sort = this.queryBuilderData.sort === column ? `-${column}` : column;
        },

        showColumn(columnKey) {
            if (!this.queryBuilderProps.columns) {
                return false;
            }

            const column = this.queryBuilderProps.columns[columnKey];

            return column ? column.enabled : false;
        },

        setQueryBuilder(data) {
            this.queryBuilderData.download = 0
            let page = this.queryBuilderData.page || 1;

            let filter = this.getFilterForQuery(
                data.search || {},
                data.filters || {}
            );

            if (!isEqual(filter, this.queryBuilderData.filter)) {
                page = 1;
            }


            this.queryBuilderData = {
                page,
                sort: this.queryBuilderData.sort || "",
                filter,
                columns: this.getColumnsForQuery(data.columns || {}),
            };

            if (data.download) {
                this.queryBuilderData.download = 1
                this.queryBuilderData.filename = data.filename
            }
            this.queryBuilderDataIteration++;
        },
    },

    computed: {
        queryBuilderString() {
            let query = qs.stringify(this.queryBuilderData, {
                filter(prefix, value) {
                    if (typeof value === "object" && value !== null) {
                        return pickBy(value);
                    }
                    return value;
                },

                skipNulls: true,
                strictNullHandling: true,
            });

            if (!query || query === "page=1") {
                query = "remember=forget";
            }

            return query;
        },
    },

    watch: {
        queryBuilderData: {
            deep: true,
            handler() {
                if (this.$inertia) {
                    const query = this.queryBuilderString;
                    if (this.queryBuilderData.download) {
                        this.queryBuilderData.download = 0
                        this.queryBuilderData.filename = ''
                        window.location.href = location.pathname + `?${query}`
                    } else {
                        this.$inertia.get(location.pathname + `?${query}`, {}, {replace: true, preserveState: true});
                    }
                }
            },
        },
    },
};
