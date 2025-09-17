<script setup lang="ts">
const props = defineProps<{
    value: string | number | null | undefined;
    field: { map?: Record<string, { label: string; class: string }> } & Record<string, any>;
}>();

const map = props.field.map || {};
const meta = map[String(props.value ?? '')] || { label: String(props.value ?? ''), class: props.field.class || '' };
</script>

<template>
    <span :class="meta.class">{{ meta.label }}</span>
</template>
