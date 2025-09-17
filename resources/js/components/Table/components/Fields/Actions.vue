<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    record: Record<string, any>;
    actions: Array<{ label: string; route: string; params?: string[]; method?: string; class?: string }>;
}>();

const resolveParams = (params?: string[]) => {
    if (!params || params.length === 0) return [];
    return params.map((p) => props.record[p]);
};
</script>

<template>
    <div class="inline-flex gap-2">
        <template v-for="(action, idx) in actions" :key="idx">
            <Link :href="route(action.route, resolveParams(action.params))" :method="action.method || 'get'"
                :class="action.class">
            {{ action.label }}
            </Link>
        </template>
    </div>

</template>
