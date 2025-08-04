<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Qingyang Liu <gary.liu@totara.com>
  @module container_workspace
-->
<template>
  <div class="tui-yourWorkspaceFilter">
    <template v-if="!$apollo.loading">
      <FilterBar
        v-model:value="selection"
        :title="$str('find_spaces', 'container_workspace')"
        class="tui-yourWorkspaceFilter__filter"
      >
        <template v-slot:filters-left="{ stacked }">
          <SearchFilter
            v-model:value="selection.searchTerm"
            :label="$str('search_spaces', 'container_workspace')"
            drop-label
            :placeholder="$str('search_spaces', 'container_workspace')"
            :stacked="stacked"
          />

          <SelectFilter
            v-model:value="selection.access"
            :label="$str('workspace_visibility', 'container_workspace')"
            :show-label="true"
            :options="options.accesses"
            :stacked="stacked"
          />
        </template>
      </FilterBar>

      <div class="tui-yourWorkspaceFilter__sortFilter">
        <SortBar v-model:sort-by="selection.sort" :options="options.sorts">
          <template v-slot:start>
            <div v-if="!spacesIsLoading" class="tui-yourWorkspaceFilter__total">
              {{
                $str(
                  spacesCursor.total === 0 || spacesCursor.total > 1
                    ? 'total_space_x'
                    : 'single_space',
                  'container_workspace',
                  spacesCursor.total
                )
              }}
            </div>
          </template>
        </SortBar>
      </div>
    </template>
  </div>
</template>

<script>
import FilterBar from 'tui/components/filters/FilterBar';
import SelectFilter from 'tui/components/filters/SelectFilter';
import SearchFilter from 'tui/components/filters/SearchFilter';
import SortBar from 'tui/components/filters/SortBar';

// GraphQL queries
import getFilterOptions from 'container_workspace/graphql/workspace_filter_options';

export default {
  components: {
    FilterBar,
    SelectFilter,
    SearchFilter,
    SortBar,
  },

  props: {
    selectedSort: {
      type: String,
      default: null,
    },

    searchTerm: {
      type: String,
      default: '',
    },

    selectedAccess: {
      type: String,
      default: null,
    },

    spacesCursor: {
      type: Object,
      default() {
        return { total: 0, next: '' };
      },
    },

    spacesIsLoading: Boolean,
  },

  emits: ['filter', 'submit-search'],

  apollo: {
    options: {
      query: getFilterOptions,

      /**
       * @param {Array} sorts
       * @param {Array} accesses
       */
      update({ sorts, accesses }) {
        accesses = [
          {
            label: this.$str('all', 'core'),
            value: null,
          },
        ].concat(accesses);

        return {
          sorts: Array.prototype.map.call(sorts, ({ value, label }) => {
            return {
              id: value,
              label: label,
            };
          }),

          accesses: Array.prototype.map.call(accesses, ({ value, label }) => {
            return {
              id: value,
              label: label,
            };
          }),
        };
      },
    },
  },

  data() {
    return {
      options: {},

      selection: {
        sort: this.selectedSort,
        access: this.selectedAccess,
        searchTerm: this.searchTerm,
      },
    };
  },

  watch: {
    selection: {
      deep: true,
      handler() {
        this.$emit('filter', this.selection);
      },
    },
  },

  methods: {
    submitSearch() {
      this.$emit('submit-search', this.selection);
    },
  },
};
</script>

<style lang="scss">
.tui-yourWorkspaceFilter {
  &__sortFilter {
    margin-top: var(--gap-8);
  }

  &__total {
    @include font(h4);
  }
}
</style>
