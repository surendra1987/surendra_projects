<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2021 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Kevin Hottinger <kevin.hottinger@totaralearning.com>
  @module mod_perform
-->

<template>
  <SortBar
    v-if="total"
    class="tui-performUserActivitiesCount"
    :sort-by="value"
    :options="sortByFilterOptions"
    @update:sort-by="
      e => {
        $emit('update:value', e);
        $emit('input', e);
      }
    "
  >
    <template v-slot:start>
      <div
        class="tui-performUserActivitiesCount__count"
        :class="{
          'tui-performUserActivitiesCount__count--hidden': loading,
        }"
      >
        {{
          $str('showing_activities', 'mod_perform', {
            shown: displayedCount,
            total: total,
          })
        }}
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
    aboutOthers: Boolean,
    displayedCount: Number,
    loading: Boolean,
    sortByOptions: Array,
    total: Number,
    value: String,
  },

  emits: ['input', 'update:value'],

  computed: {
    /**
     * Get the available options for sort by filter
     *
     * @return {Array}
     */
    sortByFilterOptions() {
      let options = this.sortByOptions;
      if (!this.aboutOthers) {
        options = options.filter(item => 'subject_name' !== item.id);
      }

      return options;
    },
  },
};
</script>

<style lang="scss">
.tui-performUserActivitiesCount {
  margin-top: var(--gap-6);

  &__count {
    flex-grow: 1;

    &--hidden {
      visibility: hidden;
    }
  }
}
</style>
