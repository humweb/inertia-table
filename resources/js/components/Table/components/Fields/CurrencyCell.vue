<script setup lang="ts">
const props = defineProps<{
    value: number | string | null | undefined;
    field: { currency?: string; locale?: string; class?: string } & Record<string, any>;
}>();

const locale = props.field.locale || 'en-US';
const currency = props.field.currency || 'USD';
const formatted = new Intl.NumberFormat(locale, { style: 'currency', currency }).format(Number(props.value ?? 0));
</script>

<template>
    <span :class="props.field.class">{{ formatted }}</span>

</template>
