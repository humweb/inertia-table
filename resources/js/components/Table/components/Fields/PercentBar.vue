<script setup lang="ts">
const props = defineProps<{
    value: number | string;
    field: { class?: string; barClass?: string; trackClass?: string } & Record<string, any>;
}>();

const pct = Math.max(0, Math.min(100, Number(props.value ?? 0)));
const trackClass = props.field.trackClass || 'h-2 w-24 rounded bg-gray-200';
const barClass = props.field.barClass || 'h-2 rounded bg-blue-500';
</script>

<template>
    <div class="flex items-center gap-2">
        <div :class="trackClass">
            <div :class="barClass" :style="{ width: pct + '%' }"></div>
        </div>
        <span :class="props.field.class">{{ pct }}%</span>
    </div>
</template>
