<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Jack Humphrey <jack.humphrey@totaralearning.com>
  @module totara_engage
-->

<template>
  <button
    class="tui-totara_engage-navigationPill"
    :class="{
      'tui-totara_engage-navigationPill--selected': selected,
    }"
    :disabled="disabled || null"
    :aria-current="selected ? 'page' : null"
  >
    <div class="tui-totara_engage-navigationPill__content">
      {{ text }}
    </div>
  </button>
</template>

<script>
export default {
  props: {
    selected: Boolean,
    disabled: Boolean,
    text: {
      type: String,
      required: true,
    },
  },
};
</script>

<style lang="scss">
.tui-totara_engage-navigationPill {
  &:hover,
  &:focus {
    text-decoration: none;
  }
  display: inline-flex;
  align-items: center;
  height: var(--engage-nav-pill-height);
  padding: 0 var(--gap-4);
  color: var(--color-neutral-7);
  font-size: font-size-px(14);
  white-space: nowrap;
  background-color: var(--color-neutral-1);
  border: 0;
  border-radius: var(--engage-nav-pill-border-radius);
  cursor: pointer;

  &:focus-visible {
    @include tui-focus();
  }
  &:hover {
    color: var(--color-state);
  }
  &:active {
    color: var(--color-state-active);
  }

  &--selected {
    color: var(--color-neutral-1);
    background-color: var(--color-state);
    &:hover {
      color: var(--color-neutral-1);
      background-color: var(--color-state-hover);
    }
    &:active {
      color: var(--color-neutral-1);
      background-color: var(--color-state-active);
    }
  }

  &[disabled] {
    color: var(--color-neutral-6);
    cursor: default;
  }
}
</style>
