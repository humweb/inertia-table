<script setup lang="ts">

const props = defineProps<{
    modelValue: [string, string] | string | null | undefined;
    filter: { field: string; label?: string; dateFormat?: string; class?: string } & Record<string, any>;
}>();

const emit = defineEmits(['update:modelValue']);

const onChange = (idx: number, val: string) => {
    let current = Array.isArray(props.modelValue) ? props.modelValue.slice() : ['', ''];
    current[idx] = val;
    emit('update:modelValue', current);
};
</script>

<template>
    <div class="flex items-center gap-2" :class="filter.class">
        <input type="date" :name="filter.field + '[0]'"
            class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            @change="(e: any) => onChange(0, e.target.value)" />
        <span>–</span>
        <input type="date" :name="filter.field + '[1]'"
            class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            @change="(e: any) => onChange(1, e.target.value)" />
    </div>
</template>
