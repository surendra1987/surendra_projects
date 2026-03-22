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

  @author Kevin Hottinger <kevin.hottinger@totaralearning.com>
  @module totara_core
-->

<template>
  <OverflowDetector
    ref="toggleSet"
    v-slot="{ measuring }"
    @change="overflowChanged"
  >
    <div
      class="tui-toggleSet"
      :class="{
        'tui-toggleSet--disabled': disabled,
        'tui-toggleSet--large': large,
        'tui-toggleSet--select': overflowing,
      }"
      :role="!overflowing || measuring ? 'radiogroup' : null"
      :aria-label="ariaLabel"
    >
      <PropsProvider v-if="!overflowing || measuring" :provide="provide">
        <slot />
      </PropsProvider>

      <Select
        v-else
        :value="value"
        :disabled="disabled"
        :options="selectOptions"
        @input="$_handleSelect"
      />
    </div>
  </OverflowDetector>
</template>

<script>
import { eachChild } from 'tui/vue/vnode';
import OverflowDetector from 'tui/components/util/OverflowDetector';
import PropsProvider from 'tui/components/util/PropsProvider';
import Select from 'tui/components/form/Select';

export default {
  components: {
    OverflowDetector,
    PropsProvider,
    Select,
  },

  props: {
    ariaLabel: {
      type: String,
      required: true,
    },
    disabled: Boolean,
    large: Boolean,
    value: [Boolean, String],
  },

  emits: ['input', 'update:value'],

  data() {
    return {
      overflowing: false,
    };
  },

  computed: {
    selectOptions() {
      const options = [];
      eachChild(
        this.$slots.default(),
        vnode => {
          options.push({
            id: vnode.props.value,
            label: vnode.props.text || vnode.props.ariaLabel,
          });
        },
        { component: true }
      );
      return options;
    },
  },

  methods: {
    /**
     * Provide disabled, large & selected props to inner toggle buttons
     *
     * @param {string} selected
     */
    provide({ props }) {
      return {
        props: {
          disabled: this.disabled,
          large: this.large,
          selected: props.value == this.value,
        },
        listeners: {
          clicked: this.$_handleSelect,
          keydown: this.$_handleKeyDown,
        },
      };
    },

    overflowChanged({ overflowing }) {
      this.overflowing = overflowing;
    },

    /**
     * Key pressed while focused on toggle button
     *
     * @param {object} e
     */
    $_handleKeyDown(e) {
      const currentEvents = {
        prev: ['Left', 'ArrowLeft'],
        next: ['Right', 'ArrowRight'],
      };

      if (currentEvents.prev.includes(e.key)) {
        this.navigateByArrow(-1);
      }
      if (currentEvents.next.includes(e.key)) {
        this.navigateByArrow(1);
      }
    },

    /**
     * Toggle selected button
     *
     * @param {string} selected
     */
    $_handleSelect(selected) {
      this.$emit('update:value', selected);
      this.$emit('input', selected);
    },

    /**
     * Allow selected item to be changed with left / right arrows
     *
     * @param {Integer} direction
     */
    navigateByArrow(direction) {
      direction = direction < 0 ? -1 : 1;
      const buttons = [...this.$el.querySelectorAll('[role=radio]')];
      let index = buttons.findIndex(
        x => x.getAttribute('aria-checked') == 'true'
      );

      if (index == -1) {
        // ensure index is within buttons otherwise we will get an infinite loop
        index = direction < 0 ? 0 : buttons.length - 1;
      }

      let newIndex = (index += direction);
      if (newIndex < 0) {
        newIndex = buttons.length - 1;
      }
      if (newIndex >= buttons.length) {
        newIndex = 0;
      }
      const newSelection = buttons[newIndex];

      // Set focus
      newSelection.focus();

      // Update selection
      this.$_handleSelect(newSelection.value);
    },
  },
};
</script>

<style lang="scss">
.tui-toggleSet {
  display: inline-flex;
  max-width: 100%;
  padding: 1px;
  background: var(--toggle-bg-color);
  border-radius: var(--btn-radius);

  &--disabled {
    opacity: 0.4;
  }

  &--select {
    width: 100%;
    background: transparent;
  }

  &--large {
    padding: 2px;
  }
}
</style>
