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
  <div class="tui-performGoalsFilter">
    <FilterBarArea
      v-model:value="filters"
      :accessibility-title="$str('a11y_filter_goals', 'perform_goal')"
      @input="
        e => {
          $emit('update:value', e);
          $emit('input', e);
        }
      "
    >
      <template v-slot:bar-filters="{ filters, update }">
        <SearchFilter
          :bar-filter="true"
          drop-label
          :label="$str('a11y_goals_filter_search', 'perform_goal')"
          :placeholder="$str('goals_filter_search', 'perform_goal')"
          :stacked="true"
          :value="filters.search"
          @input="update('search', $event)"
        />
      </template>

      <template v-slot:extra-filters="{ filters, update, stacked }">
        <!-- Status -->
        <MultiSelectCheckbox
          v-if="filterOptions.status"
          :has-columns="!stacked"
          :options="filterOptions.status"
          :title="$str('goals_filter_status', 'perform_goal')"
          :value="filters.status"
          @input="update('status', $event)"
        />
      </template>
    </FilterBarArea>
  </div>
</template>

<script>
// Components
import FilterBarArea from 'tui/components/filters/FilterBarArea';
import MultiSelectCheckbox from 'tui/components/filters/MultiSelectCheckboxFilter';
import SearchFilter from 'tui/components/filters/SearchFilter';

export default {
  components: {
    FilterBarArea,
    MultiSelectCheckbox,
    SearchFilter,
  },

  props: {
    // Available filter options
    filterOptions: { type: Object },
    // Current filter values
    value: { type: Object },
  },

  emits: ['input', 'update:value'],

  data() {
    return {
      filters: this.value,
    };
  },
};
</script>
