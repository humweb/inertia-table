<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    row: Record<string, any>;
    field: { relation?: string; relationKey?: string; route?: string; routeParams?: string[]; class?: string } & Record<string, any>;
}>();

const label = (() => {
    const rel = props.field.relation || '';
    const key = props.field.relationKey || 'name';
    const obj = rel ? props.row[rel] : undefined;
    return obj ? (obj[key] ?? '') : '';
})();

const params = (() => {
    const arr = props.field.routeParams || [];
    return arr.map((p) => props.row[p]);
})();
</script>

<template>
    <Link :href="route(props.field.route as string, params)" :class="props.field.class">{{ label }}</Link>
</template>
