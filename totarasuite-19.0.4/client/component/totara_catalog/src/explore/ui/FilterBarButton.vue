<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Simon Chester <simon.chester@totara.com>
  @module totara_catalog
-->

<script setup>
import Caret from 'tui/components/decor/Caret';

defineProps({
  caret: Boolean,
});
</script>

<template>
  <button
    type="button"
    :class="[
      'tui-totara_catalog-filterBarButton',
      $slots.default && 'tui-totara_catalog-filterBarButton--hasContent',
    ]"
  >
    <div
      v-if="$slots.icon"
      class="tui-totara_catalog-filterBarButton__iconBefore"
    >
      <slot name="icon" />
    </div>
    <div
      v-if="$slots.default"
      class="tui-totara_catalog-filterBarButton__content"
    >
      <slot />
    </div>
    <div v-if="caret" class="tui-totara_catalog-filterBarButton__iconAfter">
      <Caret v-if="caret" />
    </div>
  </button>
</template>

<style lang="scss">
.tui-totara_catalog-filterBarButton {
  $min-height: calc(var(--btn-line-height) + (var(--btn-padding-v)) * 2);

  display: inline-flex;
  gap: gap(2);
  align-items: center;
  justify-content: center;
  min-width: $min-height;
  max-width: 100%;
  min-height: $min-height;
  padding: var(--btn-padding-v) var(--btn-padding-h);
  color: var(--color-text);
  font-size: var(--btn-font-size);
  line-height: var(--btn-line-height);
  overflow-wrap: break-word;
  background-color: transparent;
  border: none;
  border-radius: var(--btn-radius);
  cursor: pointer;
  transition: tui-transitions('button', background-color border-color);

  &__content {
    @include flex-center;
  }

  &:focus-visible {
    @include tui-focus();
  }

  &:hover {
    background-color: rgba(0, 0, 0, 0.05);
  }

  &:active {
    background-color: rgba(0, 0, 0, 0.07);
  }

  &__iconBefore {
    @include flex-center;
  }

  &__iconAfter {
    @include flex-center;
  }
}
</style>
