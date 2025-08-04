<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Simon Chester <simon.chester@totaralearning.com>
  @module tui
-->

<template>
  <div
    class="tui-popover__wrap"
    :class="[triggerValign && 'tui-popover__wrap--valign-' + triggerValign]"
  >
    <PopoverTrigger
      v-if="$slots.trigger"
      ref="trigger"
      :triggers="triggers"
      :ui-element="$refs.frame"
      @open-changed="setOpen"
      @trigger-type-changed="setTriggerType"
    >
      <slot name="trigger" :is-open="isOpen" />
    </PopoverTrigger>
    <PopoverPositioner
      v-slot="{ side, arrowDistance }"
      :position="position"
      :open="isOpen"
      :reference-element="getReference()"
      :prefer-slide="true"
      :context-mode="contextMode"
    >
      <PopoverFrame
        ref="frame"
        :title="title"
        :side="side"
        :size="size"
        :slim="isSlim"
        :sr-closeable="activeClickTrigger"
        :arrow-distance="arrowDistance"
        :closeable="showCloseable"
        :has-content-padding="hasContentPadding"
        @close="handleClose"
      >
        <slot :close="handleClose" />
        <template v-if="$slots.buttons" v-slot:buttons>
          <slot name="buttons" :close="handleClose" />
        </template>

        <template v-else-if="$slots['custom-buttons']" v-slot:custom-buttons>
          <slot name="custom-buttons" :close="handleClose" />
        </template>
      </PopoverFrame>
    </PopoverPositioner>
  </div>
</template>

<script>
import PopoverFrame from 'tui/components/popover/PopoverFrame';
import PopoverTrigger from 'tui/components/popover/PopoverTrigger';
import PopoverPositioner from 'tui/components/popover/PopoverPositioner';

const validTriggers = ['click', 'click-toggle', 'hover', 'focus'];
const validPositions = ['top', 'right', 'bottom', 'left'];

export default {
  components: {
    PopoverFrame,
    PopoverTrigger,
    PopoverPositioner,
  },

  props: {
    // note: this cannot be changed after a component is created.
    // only the initial trigger set will be used.
    triggers: {
      type: Array,
      default: () => ['click'],
      validator: value =>
        value === null || value.every(x => validTriggers.includes(x)),
    },
    title: String,
    reference: Object,
    position: {
      type: String,
      default: 'bottom',
      validator: x => x.split('-', 2).every(y => validPositions.includes(y)),
    },
    open: Boolean,
    closeable: {
      type: [Boolean, String],
      default: true,
    },
    size: String,
    // Reduced padding
    slim: Boolean,
    contextMode: {
      type: String,
      default: 'contained',
    },
    /** @type {import('vue').PropType<?('center')>} */
    triggerValign: {
      type: String,
      validator: x => ['center'].includes(x),
    },
    // Setting this to false will remove the extra padding around the content
    hasContentPadding: {
      type: Boolean,
      default: true,
    },
  },

  emits: ['open-changed', 'request-close'],

  data() {
    return {
      isOpen: !!this.open,
      referenceElement: null,
      activeClickTrigger: false,
    };
  },

  computed: {
    // Combination of props allow for 'slim' modifier
    isSlim() {
      return this.slim && !this.showCloseable && !this.title;
    },

    showCloseable() {
      if (this.closeable === 'always') {
        return true;
      }
      if (!this.closeable || !this.activeClickTrigger) {
        return false;
      }
      return true;
    },
  },

  watch: {
    open() {
      this.isOpen = !!this.open;
    },
  },

  mounted() {
    document.addEventListener('keydown', this.handleKeyDown);
  },

  beforeUnmount() {
    document.removeEventListener('keydown', this.handleKeyDown);
  },

  methods: {
    /**
     * Set whether the popover is open
     *
     * @param {boolean} visible
     */
    setOpen(visible) {
      this.isOpen = visible;
      this.$emit('open-changed', visible);
    },

    /**
     * Track which triggers are currently active on an open popover
     *
     * @param {string} triggerType
     */
    setTriggerType(triggerType) {
      this.activeClickTrigger = triggerType.click;
    },

    /**
     * Get the element to position the popover relative to.
     *
     * @returns {Element}
     */
    getReference() {
      let reference = this.reference || this.$refs.trigger;
      if (reference && reference.$el) {
        reference = reference.$el;
      }
      if (reference instanceof Element) {
        return reference;
      }
      return null;
    },

    /**
     * Closes the popover if the 'Escape' key is pressed
     *
     * @param {object} event
     */
    handleKeyDown(event) {
      if (event.key === 'Escape') {
        this.handleClose();
      }
    },

    handleClose() {
      if (!this.$refs.trigger) {
        this.$emit('request-close');
      } else {
        this.$refs.trigger.close();
      }
    },
  },
};
</script>

<style lang="scss">
.tui-popover {
  &__wrap {
    &--valign {
      &-center {
        display: flex;
        align-items: center;
      }
    }
  }
}
</style>
