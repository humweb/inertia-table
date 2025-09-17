<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{ 
  row: Record<string, any>;
  field: Record<string, any>;
  value: string | Record<string, any> | any[]
}>();

const routeParams = computed(() => {
    return props.field.routeParams.map((param: string) => props.row[param]);
});
</script>

<template>
    <ILink :href="route(props.field.route, routeParams)" :class="props.field.class">{{ props.value }}</ILink>
</template>


