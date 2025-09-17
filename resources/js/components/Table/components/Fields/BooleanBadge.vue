<script setup lang="ts">
const props = defineProps<{
    value: boolean | number | string | null | undefined;
    field: Record<string, any>;
}>();

const isTrue = !!(props.value === true || props.value === 1 || props.value === '1' || props.value === 'true');
const trueLabel = props.field.trueLabel || 'Yes';
const falseLabel = props.field.falseLabel || 'No';
const trueClass = props.field.trueClass || 'inline-flex items-center rounded px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800';
const falseClass = props.field.falseClass || 'inline-flex items-center rounded px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-800';
</script>

<template>
    <span :class="isTrue ? trueClass : falseClass">{{ isTrue ? trueLabel : falseLabel }}</span>
</template>
