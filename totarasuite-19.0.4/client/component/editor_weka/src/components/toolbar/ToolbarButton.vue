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
  @module editor_weka
-->

<template>
  <button
    class="tui-wekaToolbarButton"
    :class="{
      'tui-wekaToolbarButton--selected': selected,
    }"
    :aria-label="text"
    :aria-pressed="ariaPressed"
    type="button"
    @click="$emit('click', $event)"
  >
    <div>
      <slot name="icon" />
      {{ text }}
    </div>
    <Caret v-if="caret" />
  </button>
</template>

<script>
import Caret from 'tui/components/decor/Caret';

export default {
  components: {
    Caret,
  },

  props: {
    text: {
      type: String,
      required: true,
    },

    // null = not selectable
    selected: {
      type: Boolean,
      default: null,
    },

    caret: Boolean,
  },

  emits: ['click'],

  computed: {
    ariaPressed() {
      if (this.selected == null) {
        return null; // unset - not pressable
      }
      return this.selected ? 'true' : 'false';
    },
  },
};
</script>

<style lang="scss">
.tui-wekaToolbarButton {
  display: inline-flex;
  gap: var(--gap-1);
  align-items: center;
  justify-content: center;
  height: var(--gap-8);
  min-height: var(--gap-6);
  padding: 0 var(--gap-2);
  color: var(--color-text);
  font-size: font-size-px(14);
  line-height: 1;
  background: transparent;
  border: none;

  &:focus-visible {
    @include tui-focus;
  }

  &:hover,
  &:active {
    background: var(--color-neutral-4);
  }

  &:disabled {
    color: var(--color-state-disabled);
    background: transparent;
    opacity: 1;

    &:hover,
    &:active {
      color: var(--color-state-disabled);
      background: transparent;
      box-shadow: none;
    }
  }

  &--selected {
    color: var(--color-neutral-1);
    background: var(--color-state-active);

    &:hover,
    &:active {
      color: var(--color-neutral-1);
      background: var(--color-state-active);
    }
  }
}
</style>
