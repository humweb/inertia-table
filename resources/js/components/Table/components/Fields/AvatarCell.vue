<script setup lang="ts">
const props = defineProps<{
    value: string | null | undefined;
    field: { size?: number; class?: string; fallback?: string; path?: string } & Record<string, any>;
    row?: Record<string, any>;
}>();

const size = props.field.size || 32;
const src = (() => {
    const base = props.field.path || props.field.meta?.path || '';
    const v = props.value || props.field.fallback || '';
    return `${base}${v}`;
})();
</script>

<template>
    <img :src="src" :alt="String(props.value || '')" :width="size" :height="size" :class="props.field.class"
        class="rounded-full object-cover" />
</template>
