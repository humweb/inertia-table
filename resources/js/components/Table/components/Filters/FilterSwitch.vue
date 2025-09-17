<script setup lang="ts">
import { computed } from 'vue';
import TextFilter from './TextFilter.vue';
import SelectFilter from './SelectFilter.vue';
import DateRangeFilter from './DateRangeFilter.vue';
import BooleanFilter from './BooleanFilter.vue';
import NumberRangeFilter from './NumberRangeFilter.vue';
import EnumFilter from './EnumFilter.vue';
import ScopeFilter from './ScopeFilter.vue';
import RelationshipFilter from './RelationshipFilter.vue';
import EmptyNotEmptyFilter from './EmptyNotEmptyFilter.vue';
import TrashedFilter from './TrashedFilter.vue';

type FilterDef = { component?: string; field: string; label?: string; options?: Record<string, string>;[key: string]: any };

const props = defineProps<{
    modelValue: any;
    filter: FilterDef;
}>();

const emit = defineEmits(['update:modelValue']);

const isTrashed = (f: FilterDef) => {
    const keys = Object.keys(f.options || {});
    return (f.label || '').toLowerCase().includes('trash') || (keys.includes('with') && keys.includes('only'));
};

const resolved = computed(() => {
    const c = (props.filter.component || '').toLowerCase();
    switch (c) {
        case 'text-filter':
            return TextFilter;
        case 'select-filter':
            return isTrashed(props.filter) ? TrashedFilter : (props.filter.options ? SelectFilter : TextFilter);
        case 'date-range-filter':
            return DateRangeFilter;
        case 'boolean-filter':
            return BooleanFilter;
        case 'number-range-filter':
            return NumberRangeFilter;
        case 'enum-filter':
            return EnumFilter;
        case 'scope-filter':
            return ScopeFilter;
        case 'relationship-filter':
            return RelationshipFilter;
        case 'empty-filter':
            return EmptyNotEmptyFilter;
        default:
            return TextFilter;
    }
});
</script>

<template>
    <component :is="resolved" :filter="filter" :modelValue="modelValue"
        @update:modelValue="(v: any) => emit('update:modelValue', v)" />
</template>
