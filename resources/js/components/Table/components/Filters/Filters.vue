<template>
    <div v-for="(filter, index) in filters" :key="filter.field">
        <div class="relative inline-flex items-center gap-1">
            <FilterSwitch :filter="filter" :modelValue="filter.value"
                @update:modelValue="(v: any) => handleChange(index, v, filter)" />

            <div v-if="filter.component === 'text-filter'" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button @click.prevent="handleChange(index, '')"
                    class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-0">
                    <span class="sr-only">Remove search</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <button v-if="showClear(filter)"
                class="bt btn-sm bg-gray-50 hover:bg-gray-100 border px-3 font-normal text-sm border-gray-300 rounded active:focus:ring-0 active:focus:outline-none inline"
                @click="clear(index)" title="Clear filter">X
            </button>
        </div>
        <p v-if="errors[filter.field]" class="mt-1 text-sm text-red-600">{{ errors[filter.field] }}</p>
    </div>

</template>

<script setup lang="ts">
import { computed } from 'vue';
import debounce from 'lodash-es/debounce';
import find from 'lodash-es/find';
import FilterSwitch from './FilterSwitch.vue';

type TableFiltersList = Array<Record<string, any>> | Record<string, any>;
type UpdateFilterHandler = (index: number | string, value: any) => void;

const props = defineProps<{
    filters: TableFiltersList;
    onChange: UpdateFilterHandler;
    errors: Record<string, string>;
}>();

const emitChange = debounce((key: any, value: any) => {
    props.onChange(key, value);
}, 300);

const handleChange = (key: any, value: any, _filter?: any) => {
    emitChange(key, value);
};

const clear = (key: any) => {
    props.onChange(key, null);
};

const showClear = (filter: any) => {
    const c = (filter.component || '').toLowerCase();
    return c === 'select-filter' || c === 'date-range-filter';
};

const hasEnabledFilter = computed(() => {
    return find(props.filters as any, (f: any) => f.value !== '' && f.value != null) !== undefined;
});
</script>
