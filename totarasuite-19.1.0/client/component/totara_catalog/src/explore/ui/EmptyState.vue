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

  @author Qingyang Liu <qingyang.liue@totara.com>
  @module totara_catalog
-->

<template>
  <div class="tui-totara_catalog-emptyState">
    <div class="tui-totara_catalog-emptyState__icon">
      <slot name="icon" />
    </div>

    <div class="tui-totara_catalog-emptyState__text">
      <h2 class="tui-totara_catalog-emptyState__text-result">
        {{ text }}
      </h2>

      <p class="tui-totara_catalog-emptyState__text-hint">
        {{ hint }}
      </p>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    text: {
      type: String,
      required: true,
    },
    hint: {
      type: String,
      required: true,
    },
  },
};
</script>

<style lang="scss">
.tui-totara_catalog-emptyState {
  &__icon {
    margin-bottom: var(--gap-6);
    color: var(--color-neutral-6);
    text-align: center;
  }
  &__text {
    color: var(--color-neutral-7);
    text-align: center;
    &-result {
      @include font(display-xs);
      margin-bottom: var(--gap-3);
    }
    &-hint {
      @include font(body-lg);
      color: var(--color-neutral-6);
    }
  }
}
</style>
