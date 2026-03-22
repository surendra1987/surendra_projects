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

  @author Alvin Smith <alvin.smith@totaralearning.com>
  @module tui
-->

<template>
  <div
    class="tui-tag"
    :class="{
      'tui-tag--large': large,
      'tui-tag--noBorder': noBorder,
      'tui-tag--primary': primary,
      'tui-tag--bold': bold,
      'tui-tag--withButton': !!$slots.button,
    }"
  >
    <component :is="href ? 'a' : 'span'" :href="href" class="tui-tag__content">
      <span v-if="label" class="tui-tag__contentLabel">
        {{ label }}
      </span>

      {{ text }}
    </component>
    <slot name="button" />
  </div>
</template>

<script>
export default {
  props: {
    bold: Boolean,
    href: String,
    label: String,
    large: Boolean,
    noBorder: Boolean,
    primary: Boolean,
    text: {
      type: String,
      required: true,
    },
  },
};
</script>

<style lang="scss">
.tui-tag {
  display: inline-flex;
  flex-shrink: 0;
  align-items: center;
  height: var(--tag-height);
  white-space: nowrap;
  background-color: var(--tag-bg-color);
  border: var(--border-width-thin) solid var(--tag-border-color);
  border-radius: var(--tag-border-radius);

  &--noBorder {
    border: none;
  }

  &--large {
    height: auto;
    border-radius: 100px; // suitably large to make it rounded
  }

  &--bold {
    font-weight: bold;
  }

  &__content {
    display: flex;
    padding: 0 var(--gap-2);
    font-size: font-size-px(13);
  }

  &--large &__content {
    padding: var(--gap-2) var(--gap-3);
    font-size: font-size-px(15);
  }

  &--withButton &__content {
    padding-right: 0;
    color: var(--tag-button-color);
  }

  &__contentLabel {
    padding-right: var(--gap-1);
    font-weight: normal;
  }

  &--primary {
    text-transform: uppercase;
    background-color: var(--color-state);
  }

  &--primary &__content {
    color: var(--color-neutral-1);
  }
}
</style>
