<script setup lang="ts">
import { computed } from 'vue';
const props = defineProps<{
    modelValue: string | number | boolean | null | undefined;
    filter: { field: string; label?: string; trueLabel?: string; falseLabel?: string; class?: string } & Record<string, any>;
}>();

const emit = defineEmits(['update:modelValue']);
const selectClass = computed(() => props.filter.class || 'block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500');
</script>

<template>
    <select :name="filter.field" :class="selectClass" :value="(modelValue as any)"
        @change="(e: any) => emit('update:modelValue', e.target.value)">
        <option value="">-- {{ filter.label || 'Any' }} --</option>
        <option value="1">{{ filter.trueLabel || 'Yes' }}</option>
        <option value="0">{{ filter.falseLabel || 'No' }}</option>
    </select>
</template>
