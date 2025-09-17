<script setup lang="ts">
import { computed } from 'vue';
const props = defineProps<{
    modelValue: string | null | undefined;
    filter: { field: string; label?: string; options?: Record<string, string>; class?: string } & Record<string, any>;
}>();

const emit = defineEmits(['update:modelValue']);
const selectClass = computed(() => props.filter.class || 'block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500');
const options = props.filter.options || { with: 'With Trashed', only: 'Only Trashed' };
</script>

<template>
    <select :name="filter.field" :class="selectClass" :value="(modelValue as any)"
        @change="(e: any) => emit('update:modelValue', e.target.value)">
        <option value="">-- {{ filter.label || 'Trashed' }} --</option>
        <option v-for="(label, key) in options" :key="key" :value="key">{{ label }}</option>
    </select>
</template>
