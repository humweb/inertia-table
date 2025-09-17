<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue';
import get from 'lodash-es/get';

import Actions from './Actions.vue';
import ImageField from './ImageField.vue';
import LinkField from './LinkField.vue';
import BooleanBadge from './BooleanBadge.vue';
import CurrencyCell from './CurrencyCell.vue';
import PercentBar from './PercentBar.vue';
import StatusBadge from './StatusBadge.vue';
import AvatarCell from './AvatarCell.vue';
import RelationLinkCell from './RelationLinkCell.vue';
import ComputedCell from './ComputedCell.vue';

const props = defineProps<{
    record: Record<string, any>;
    field: Record<string, any>;
}>();

const value = computed(() => props.field.value ?? get(props.record, props.field.attribute));

const resolved = computed(() => {
    switch (props.field.component) {
        case 'action-field':
            return Actions;
        case 'image-field':
            return ImageField;
        case 'link-field':
            return LinkField;
        case 'boolean-field':
            return BooleanBadge;
        case 'currency-field':
            return CurrencyCell;
        case 'percent-field':
            return PercentBar;
        case 'badge-field':
            return StatusBadge;
        case 'avatar-field':
            return AvatarCell;
        case 'relation-field':
            return RelationLinkCell;
        case 'computed-field':
            return ComputedCell;
        default:
            return null;
    }
});
</script>

<template>
    <component v-if="resolved" :is="resolved" :record="record" :row="record" :field="field" :value="value"
        :actions="field.actions" />
    <span v-else>{{ value }}</span>
</template>
