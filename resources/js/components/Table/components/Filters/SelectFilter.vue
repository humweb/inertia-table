<script setup lang="ts">
import { computed } from 'vue';
const props = defineProps<{
    modelValue: string | number | null | undefined | (string | number)[];
    filter: { field: string; label?: string; options: Record<string, string> | Array<{ key: string | number; label: string }>; multiple?: boolean; class?: string } & Record<string, any>;
}>();

const emit = defineEmits(['update:modelValue']);
const selectClass = computed(() => props.filter.class || 'block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500');

const normalizedOptions = computed(() => {
    const opts: any = (props.filter as any).options || {};
    if (Array.isArray(opts)) {
        return opts.map((o) => ({ value: o.key, label: o.label }));
    }
    return Object.entries(opts).map(([value, label]) => ({ value, label }));
});
</script>

<template>
    <select :name="filter.field" :multiple="!!filter.multiple" :class="selectClass" :value="modelValue as any"
        @change="(e: any) => emit('update:modelValue', filter.multiple ? Array.from(e.target.selectedOptions).map((o: any) => o.value) : e.target.value)">
        <option v-if="!filter.multiple" value="">-- {{ filter.label || 'Select' }} --</option>
        <option v-for="opt in normalizedOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
    </select>
</template>
