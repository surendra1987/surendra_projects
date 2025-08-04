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

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @package perform_goal
-->

<template>
  <div v-if="!total" class="tui-performGoalsSortBar__empty">
    {{ $str('filtered_goals_empty', 'perform_goal') }}
  </div>
  <SortBar
    v-else
    :options="sortOptions"
    :sort-by="value"
    @update:sort-by="
      e => {
        $emit('update:value', e);
        $emit('input', e);
      }
    "
  >
    <template v-slot:start>
      <div
        class="tui-performGoalsSortBar__count"
        :class="{
          'tui-performGoalsSortBar__count--hidden': loading,
        }"
      >
        {{ countString }}
      </div>
    </template>
  </SortBar>
</template>

<script>
import SortBar from 'tui/components/filters/SortBar';

export default {
  components: {
    SortBar,
  },

  props: {
    // Goals query is currently loading
    loading: { type: Boolean },
    // Available options goal results can be sorted by
    sortOptions: { type: Array },
    // Total number of goals based on filters
    total: { type: [String, Number], required: true },
    // Current sort option value
    value: { type: String, required: true },
  },

  emits: ['input', 'update:value'],

  computed: {
    /**
     * String displayed to show count of goals
     *
     * @return {String}
     */
    countString() {
      return this.$str(
        parseInt(this.total) > 1
          ? 'filtered_goals_count_plural'
          : 'filtered_goals_count',
        'perform_goal',
        {
          total: this.total,
        }
      );
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalsSortBar {
  &__count {
    &--hidden {
      visibility: hidden;
    }
  }
}
</style>
