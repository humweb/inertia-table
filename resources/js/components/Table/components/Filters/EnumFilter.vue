<script setup lang="ts">
import { computed } from 'vue';
const props = defineProps<{
    modelValue: string | number | null | undefined;
    filter: { field: string; label?: string; options: Record<string, string>; class?: string } & Record<string, any>;
}>();

const emit = defineEmits(['update:modelValue']);
const selectClass = computed(() => props.filter.class || 'block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500');
</script>

<template>
    <select :name="filter.field" :class="selectClass" :value="(modelValue as any)"
        @change="(e: any) => emit('update:modelValue', e.target.value)">
        <option value="">-- {{ filter.label || 'Select' }} --</option>
        <option v-for="(label, key) in filter.options" :key="key" :value="key">{{ label }}</option>
    </select>
</template>
