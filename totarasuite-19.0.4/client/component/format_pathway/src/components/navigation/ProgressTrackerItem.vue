<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Jack Humphrey <jack.humphrey@totara.com>
  @module format_pathway
-->

<template>
  <component
    :is="available ? 'a' : 'span'"
    class="tui-format_pathway-progressTrackerItem"
    :class="{
      'tui-format_pathway-progressTrackerItem--selected': selected,
      'tui-format_pathway-progressTrackerItem--hidden': hidden,
      'tui-format_pathway-progressTrackerItem--link': available,
    }"
    :aria-current="selected ? 'page' : null"
    :aria-label="ariaLabel"
    :href="disabled ? false : href"
    @click="handleClick"
  >
    {{ text }}
    <slot name="text-extra" />
  </component>
</template>

<script>
export default {
  props: {
    selected: Boolean,
    hidden: Boolean,
    ariaLabel: String,
    disabled: Boolean,
    available: Boolean,
    href: {
      required: true,
      type: String,
    },
    text: {
      required: true,
      type: String,
    },
  },

  emits: ['click'],

  methods: {
    handleClick(e) {
      if (this.disabled) {
        e.preventDefault();
        e.stopPropagation();
        return;
      }
      this.$emit('click', e);
    },
  },
};
</script>

<style lang="scss">
.tui-format_pathway-progressTrackerItem {
  display: block;
  width: 100%;
  padding: var(--gap-2);
  color: var(--progresstracker-color-locked);
  line-height: var(--font-body-line-height);
  text-align: left;
  overflow-wrap: break-word;

  &--link {
    color: var(--color-state);

    &:hover,
    &:focus {
      color: var(--color-state);
      text-decoration: none;
      background: var(--color-state-highlight-neutral);
    }
  }

  &--selected {
    color: var(--color-neutral-1);
    background: var(--color-state-active);

    &:hover,
    &:focus,
    &:focus:hover {
      color: var(--color-neutral-1);
      background: var(--color-state-active);
    }
  }

  &--hidden {
    color: var(--progresstracker-color-hidden);

    &:hover,
    &:focus {
      color: var(--progresstracker-color-hidden);
    }
  }

  &--selected&--hidden {
    color: var(--progresstracker-color-hidden--inverse);
    background: var(--progresstracker-color-hidden);

    &:hover,
    &:focus,
    &:focus:hover {
      color: var(--progresstracker-color-hidden--inverse);
      background: var(--progresstracker-color-hidden);
    }
  }
}
</style>
