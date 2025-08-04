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

  @author Riana Rossouw <riana.rossouw@totaralearning.com>
  @author Kevin Hottinger <kevin.hottinger@totaralearning.com>
  @module mod_perform
-->

<template>
  <div class="tui-performUserActivitiesFilter">
    <FilterBarArea
      :value="value"
      :accessibility-title="$str('user_activities_filter', 'mod_perform')"
      :reset-values="resetValues"
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
          :label="
            $str(
              aboutOthers
                ? 'user_activities_filter_search_others'
                : 'user_activities_filter_search',
              'mod_perform'
            )
          "
          :placeholder="
            $str(
              aboutOthers
                ? 'user_activities_filter_search_others_placeholder'
                : 'user_activities_filter_search_placeholder',
              'mod_perform'
            )
          "
          :stacked="true"
          :value="filters.search"
          @input="update('search', $event)"
        />
      </template>

      <template v-slot:extra-filters="{ filters, update, stacked }">
        <!-- Type -->
        <SelectFilter
          v-if="filterOptions.activityTypes"
          :label="$str('user_activities_filter_type', 'mod_perform')"
          :options="activityTypeFilterOptions"
          :show-label="true"
          :stacked="true"
          :value="filters.activityType"
          @input="update('activityType', $event)"
        />

        <!-- Your progress -->
        <MultiSelectCheckbox
          v-if="filterOptions.progressOptions"
          :has-columns="!stacked"
          :options="filterOptions.progressOptions"
          :title="$str('user_activities_filter_own_progress', 'mod_perform')"
          :value="filters.ownProgress"
          @input="update('ownProgress', $event)"
        />

        <!-- Activity status -->
        <MultiSelectCheckbox
          v-if="filterOptions.activityProgressOptions"
          :has-columns="!stacked"
          :options="filterOptions.activityProgressOptions"
          :title="$str('user_activities_filter_activity_status', 'mod_perform')"
          :value="filters.activityProgress"
          @input="update('activityProgress', $event)"
        />

        <!-- Activity availability -->
        <MultiSelectCheckbox
          v-if="filterOptions.activityAvailabilityOptions"
          :has-columns="!stacked"
          :options="filterOptions.activityAvailabilityOptions"
          :title="
            $str('user_activities_filter_activity_availability', 'mod_perform')
          "
          :value="filters.activityAvailability"
          @input="update('activityAvailability', $event)"
        />
      </template>
    </FilterBarArea>
  </div>
</template>

<script>
import FilterBarArea from 'tui/components/filters/FilterBarArea';
import MultiSelectCheckbox from 'tui/components/filters/MultiSelectCheckboxFilter';
import SearchFilter from 'tui/components/filters/SearchFilter';
import SelectFilter from 'tui/components/filters/SelectFilter';

export default {
  components: {
    FilterBarArea,
    MultiSelectCheckbox,
    SearchFilter,
    SelectFilter,
  },

  props: {
    aboutOthers: Boolean,
    filterOptions: Object,
    resetValues: Object,
    value: Object,
  },

  emits: ['input', 'update:value'],

  computed: {
    /**
     * Get the available options for activity type filter
     *
     * @return {Array}
     */
    activityTypeFilterOptions() {
      return this.mapFilterOptions(this.filterOptions.activityTypes);
    },
  },

  methods: {
    /**
     * Map filter options to required format
     *
     * @param {Object} source
     * @return {Object}
     */
    mapFilterOptions(source) {
      let filters = source;

      filters = Object.keys(filters).map(id => {
        return {
          id: id,
          label: filters[id],
        };
      });

      filters.unshift({
        id: null,
        label: this.$str('all', 'core'),
      });

      return filters;
    },
  },
};
</script>

<style lang="scss">
.tui-performUserActivitiesFilter {
  & > * + * {
    margin-top: var(--gap-4);
  }
}
</style>
