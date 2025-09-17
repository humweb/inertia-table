<script setup lang="ts">
const props = defineProps<{
    row: Record<string, any>;
    field: { compute?: (row: Record<string, any>) => any; class?: string } & Record<string, any>;
}>();

const value = props.field.compute ? props.field.compute(props.row) : '';
</script>

<template>
    <span :class="props.field.class">{{ value }}</span>
</template>
