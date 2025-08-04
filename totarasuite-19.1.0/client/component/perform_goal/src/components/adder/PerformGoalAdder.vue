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

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @package perform_goal
-->

<template>
  <Adder
    :existing-items="existingItems"
    :loading="$apollo.loading"
    :open="open"
    :show-load-more="nextPage"
    :show-loading-btn="showLoadingBtn"
    :title="$str('goal_adder_title', 'perform_goal')"
    @added="closeWithData"
    @cancel="cancelAdder"
    @load-more="loadMoreItems"
    @selected-tab-active="updateSelectedItems"
  >
    <!-- Filters -->
    <template v-slot:browse-filters>
      <FilterBar
        :has-top-bar="false"
        :title="$str('goal_adder_filter_title', 'perform_goal')"
      >
        <template v-slot:filters-left="{ stacked }">
          <SearchFilter
            v-model:value="searchDebounce"
            :label="$str('goal_adder_filter_name', 'perform_goal')"
            :placeholder="$str('goal_adder_filter_name', 'perform_goal')"
            :show-label="false"
            :stacked="stacked"
          />
        </template>
      </FilterBar>
    </template>

    <!-- Browse all list -->
    <template v-slot:browse-list="{ disabledItems, selectedItems, update }">
      <SelectTable
        :border-bottom-hidden="true"
        checkbox-v-align="center"
        :data="goals && goals.items ? goals.items : []"
        :disabled-ids="disabledItems"
        :large-check-box="true"
        :no-items-text="$str('goal_adder_no_items', 'perform_goal')"
        :no-label-offset="true"
        :select-all-enabled="true"
        :value="selectedItems"
        @input="update"
      >
        <template v-slot:header-row>
          <HeaderCell size="8" valign="center">
            {{ $str('goal_adder_label_name', 'perform_goal') }}
          </HeaderCell>
          <HeaderCell size="4" valign="center">
            {{ $str('goal_form_label_due_date', 'perform_goal') }}
          </HeaderCell>
        </template>

        <template v-slot:row="{ row }">
          <Cell
            :column-header="$str('goal_adder_label_name', 'perform_goal')"
            size="8"
            valign="center"
          >
            {{ row.name }}
          </Cell>

          <Cell
            :column-header="$str('goal_form_label_due_date', 'perform_goal')"
            size="4"
            valign="center"
          >
            {{ row.target_date }}
          </Cell>
        </template>
      </SelectTable>
    </template>

    <!-- Selected list -->
    <template v-slot:basket-list="{ disabledItems, selectedItems, update }">
      <SelectTable
        :border-bottom-hidden="true"
        checkbox-v-align="center"
        :data="goalSelectedItems"
        :disabled-ids="disabledItems"
        :large-check-box="true"
        :no-label-offset="true"
        :select-all-enabled="true"
        :value="selectedItems"
        @input="update"
      >
        <template v-slot:header-row>
          <HeaderCell size="8" valign="center">
            {{ $str('goal_adder_label_name', 'perform_goal') }}
          </HeaderCell>
          <HeaderCell size="4" valign="center">
            {{ $str('goal_form_label_due_date', 'perform_goal') }}
          </HeaderCell>
        </template>

        <template v-slot:row="{ row }">
          <Cell
            :column-header="$str('goal_adder_label_name', 'perform_goal')"
            size="8"
            valign="center"
          >
            {{ row.name }}
          </Cell>

          <Cell
            :column-header="$str('goal_form_label_due_date', 'perform_goal')"
            size="4"
            valign="center"
          >
            {{ row.target_date }}
          </Cell>
        </template>
      </SelectTable>
    </template>
  </Adder>
</template>

<script>
import Adder from 'tui/components/adder/Adder';
import Cell from 'tui/components/datatable/Cell';
import FilterBar from 'tui/components/filters/FilterBar';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import SearchFilter from 'tui/components/filters/SearchFilter';
import SelectTable from 'tui/components/datatable/SelectTable';

// Util
import { debounce } from 'tui/util';

// Queries
import perform_goals from 'perform_goal/graphql/select_goals_for_add';

export default {
  components: {
    Adder,
    Cell,
    FilterBar,
    HeaderCell,
    SearchFilter,
    SelectTable,
  },

  props: {
    existingItems: { type: Array, default: () => [] },
    open: { type: Boolean },
    showLoadingBtn: { type: Boolean },
    userId: { type: Number },
  },

  emits: ['cancel', 'add-button-clicked', 'added'],

  data() {
    return {
      goals: null,
      goalSelectedItems: [],
      nextPage: false,
      searchDebounce: '',
      searchFilter: '',
      skipQueries: true,
      stacked: false,
    };
  },

  watch: {
    open() {
      if (this.open) {
        this.searchDebounce = '';
        this.skipQueries = false;
      } else {
        this.skipQueries = true;
      }
    },

    searchDebounce(newValue) {
      this.updateSearchDebounced(newValue);
    },
  },

  created() {
    this.$apollo.addSmartQuery('goals', {
      query: perform_goals,
      skip() {
        return this.skipQueries;
      },
      variables() {
        return {
          input: {
            filters: {
              name: this.searchFilter,
              user_id: this.userId,
            },
          },
        };
      },
      update({ ['perform_goal_select_goals_for_add']: goals }) {
        this.nextPage = goals.next_cursor ? goals.next_cursor : false;
        return goals;
      },
    });

    this.$apollo.addSmartQuery('selectedGoals', {
      query: perform_goals,
      skip() {
        return this.skipQueries;
      },
      variables() {
        return {
          input: {
            filters: {
              goals: [],
              user_id: this.userId,
            },
          },
        };
      },
      update({ ['perform_goal_select_goals_for_add']: selectedGoals }) {
        this.goalSelectedItems = selectedGoals.items;
        return selectedGoals;
      },
    });
  },

  methods: {
    /**
     * Close the adder
     */
    cancelAdder() {
      this.$emit('cancel');
    },

    /**
     * Emit an event with the added goals
     */
    async closeWithData(selection) {
      let data;

      this.$emit('add-button-clicked');

      try {
        data = await this.updateSelectedItems(selection);
      } catch (error) {
        console.error(error);
        return;
      }
      this.$emit('added', { ids: selection, data: data });
    },

    /**
     * Fetch more from the goals query
     */
    async loadMoreItems() {
      if (!this.nextPage) {
        return;
      }
      try {
        this.$apollo.queries.goals.fetchMore({
          variables: {
            input: {
              cursor: this.nextPage,
              filters: {
                name: this.searchFilter,
                user_id: this.userId,
              },
            },
          },

          updateQuery: (previousResult, { fetchMoreResult }) => {
            const oldData = previousResult.perform_goal_select_goals_for_add;
            const newData = fetchMoreResult.perform_goal_select_goals_for_add;
            const newList = oldData.items.concat(newData.items);

            return {
              ['perform_goal_select_goals_for_add']: {
                items: newList,
                next_cursor: newData.next_cursor,
                total: newList.length,
              },
            };
          },
        });
      } catch (error) {
        console.error(error);
      }
    },

    /**
     * Update the search filter (which re-triggers the query) if the user stopped typing >500 milliseconds ago.
     *
     * @param {String} input Value from search filter input
     */
    updateSearchDebounced: debounce(function(input) {
      this.searchFilter = input;
    }, 500),

    /**
     * Refetch the selected goals
     *
     * @param {Array} selection array of goal id's
     * @return {Array} goal objects that are currently selected
     */
    async updateSelectedItems(selection) {
      try {
        await this.$apollo.queries.selectedGoals.refetch({
          input: {
            filters: {
              goals: selection,
              user_id: this.userId,
            },
          },
        });
      } catch (error) {
        console.error(error);
      }

      return this.goalSelectedItems;
    },
  },
};
</script>

<style lang="scss">
.tui-filterBar {
  &__filters {
    &-icon {
      margin-right: var(--gap-4);
    }
  }
}
</style>
