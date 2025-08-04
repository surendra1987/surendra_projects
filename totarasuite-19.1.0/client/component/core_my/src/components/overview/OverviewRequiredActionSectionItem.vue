<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @package core_my
-->

<template>
  <div
    class="tui-myPerformOverviewRequiredActionSectionItem"
    :class="{
      'tui-myPerformOverviewRequiredActionSectionItem--stacked': stacked,
    }"
  >
    <div class="tui-myPerformOverviewRequiredActionSectionItem__text">
      <slot name="text" />
    </div>
    <div
      class="tui-myPerformOverviewRequiredActionSectionItem__action"
      :class="{
        'tui-myPerformOverviewRequiredActionSectionItem__action--stacked': stacked,
      }"
    >
      <span v-if="hasAction">
        <slot name="action" />
      </span>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    // Display action
    hasAction: {
      type: Boolean,
    },
    // Should the content be stacked
    stacked: {
      type: Boolean,
    },
  },
};
</script>

<style lang="scss">
.tui-myPerformOverviewRequiredActionSectionItem {
  display: flex;

  & > * + * {
    margin-left: var(--gap-4);
  }

  &--stacked {
    flex-direction: column;
  }

  &__action {
    display: flex;
    flex-grow: 1;
    flex-shrink: 0;
    justify-content: flex-end;
    min-width: 20%;

    &--stacked {
      justify-content: flex-start;
      margin: var(--gap-2) 0 0;
    }
  }
}
</style>
