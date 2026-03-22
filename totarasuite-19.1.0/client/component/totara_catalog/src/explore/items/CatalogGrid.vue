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
  @module tui
-->

<script setup>
defineProps({
  items: Array,
});
</script>

<template>
  <TransitionGroup
    name="tui-totara_catalog-catalogGrid__item-transition"
    tag="div"
    class="tui-totara_catalog-catalogGrid"
  >
    <slot />
  </TransitionGroup>
</template>

<style lang="scss">
.tui-totara_catalog-catalogGrid {
  position: relative;
  display: grid;
  grid-template-columns: repeat(
    auto-fill,
    minmax(min(var(--tui-card-default-width), 100%), 1fr)
  );
  gap: calc(var(--gap-card-grid) * 1.5) var(--gap-card-grid);

  &__item-transition {
    &-move,
    &-enter-active,
    &-leave-active {
      transition: all 0.2s ease;
      @media (prefers-reduced-motion: reduce) {
        transition-duration: 0s;
      }
    }

    &-enter-from,
    &-leave-to {
      transform: scale(0.8);
      opacity: 0;
      @media (prefers-reduced-motion: reduce) {
        transform: unset;
        opacity: unset;
      }
    }

    &-leave-active {
      display: none;
    }
  }
}
</style>
