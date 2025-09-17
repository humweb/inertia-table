<script setup lang="ts">
import { computed } from 'vue';
const props = defineProps<{ 
  row: Record<string, any>;
  field: Record<string, any>;
  value: string | null | undefined
}>();

const src = computed(() => {
  const base = props.field.path || props.field.meta?.path || '';
  return `${base}${props.value ?? ''}`;
});
</script>

<template>
    <img :src="src" :alt="String(props.value || '')" :class="props.field.class" />
</template>


