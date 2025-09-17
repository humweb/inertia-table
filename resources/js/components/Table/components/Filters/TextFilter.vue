<script setup lang="ts">
import { computed } from 'vue';
const props = defineProps<{
    modelValue: string | null | undefined;
    filter: { field: string; label?: string; placeholder?: string; class?: string } & Record<string, any>;
}>();

const emit = defineEmits(['update:modelValue']);
const inputClass = computed(() => props.filter.class || 'block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500');

const onInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    emit('update:modelValue', target.value);
};
</script>

<template>
    <input type="text" :name="filter.field" :placeholder="filter.placeholder || filter.label || ''" :class="inputClass"
        :value="modelValue ?? ''" @input="onInput" />
</template>
