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

  @author Jack Humphrey <jack.humphrey@totaralearning.com>
  @module tui
-->

<template>
  <ButtonAria>
    <div
      class="tui-modalHeaderButton"
      :aria-label="ariaLabelText"
      :title="titleText"
      @click.prevent="$emit('click', $event)"
    >
      <slot />
    </div>
  </ButtonAria>
</template>

<script>
import ButtonAria from 'tui/components/buttons/ButtonAria';

export default {
  components: {
    ButtonAria,
  },

  props: {
    ariaLabel: {
      type: [Boolean, String],
    },
    title: {
      type: [String, Boolean],
      default: null,
    },
  },

  emits: ['click'],

  computed: {
    ariaLabelText() {
      if (this.ariaLabel) {
        return this.ariaLabel;
      }
      return this.$str('closebuttontitle', 'core');
    },

    titleText() {
      if (this.title) {
        return this.title;
      }
      if (this.title === false) {
        // if title is specifically set to false, it means we don't want a
        // title (tooltip)
        return null;
      }
      return this.ariaLabelText;
    },
  },
};
</script>

<style lang="scss">
.tui-modalHeaderButton {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  width: rem-px(24);
  height: rem-px(24);
  color: var(--color-neutral-6);
  border-radius: var(--border-radius-small);
  cursor: pointer;
  user-select: none;
  &:hover,
  &:focus,
  &:active {
    color: var(--color-neutral-7);
    background: var(--color-neutral-3);
  }
  &:focus-visible {
    @include tui-focus();
  }
}
</style>
