<template>
  <OnClickOutside :do="hide">
    <div class="relative">
      <button
        @click.prevent="toggle"
        type="button"
        :disabled="disabled"
        class="btn btn-outline-gray "
        :class="{'border-indigo-300': active, 'border-gray-300': !active, 'cursor-not-allowed': disabled }"
        aria-haspopup="true"
        ref="button"
      >
        <slot name="button" />
      </button>

      <div ref="tooltip" class="absolute z-10" v-show="opened">
        <div class="mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
          <slot />
        </div>
      </div>
    </div>
  </OnClickOutside>
</template>

<script>
import { createPopper } from "@popperjs/core/lib/popper-lite";
import preventOverflow from "@popperjs/core/lib/modifiers/preventOverflow";
import flip from "@popperjs/core/lib/modifiers/flip";
import OnClickOutside from "./OnClickOutside.vue";

export default {
  components: {
    OnClickOutside,
  },
    props: {
        placement: {
            type: String,
            default: "bottom-start",
            required: false,
        },

        active: {
            type: Boolean,
            default: false,
            required: false,
        },

        disabled: {
            type: Boolean,
            default: false,
            required: false,
        },
    },

    data() {
        return {
            opened: false,
            popper: null,
        };
    },

    watch: {
        opened() {
            this.popper.update();
        },
    },

    methods: {
        toggle() {
            this.opened = !this.opened;
        },

        hide() {
            this.opened = false;
        },
    },

    mounted() {
        this.popper = createPopper(this.$refs.button, this.$refs.tooltip, {
            placement: this.placement,
            modifiers: [flip, preventOverflow],
        });
    },
};
</script>
