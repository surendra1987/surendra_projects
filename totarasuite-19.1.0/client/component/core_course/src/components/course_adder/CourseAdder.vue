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

  @author Brian Barnes <brian.barnes@totara.com>
  @module core_course
-->

<template>
  <Adder
    class="tui-core_course-courseAdder"
    :existing-items="existingItems"
    :has-loading-preview="true"
    :loading="$apollo.loading && !showLoadingBtn"
    :open="open"
    :show-load-more="!!nextPage"
    :show-loading-btn="showLoadingBtn"
    :title="$str('selectcourses', 'core')"
    @added="closeWithData($event)"
    @cancel="$emit('cancel')"
    @load-more="loadMoreItems()"
    @selected-tab-active="updateSelectedItems($event)"
  >
    <template v-if="$slots.notices" v-slot:notices>
      <slot name="notices" />
    </template>

    <!-- Filters -->
    <template v-slot:browse-filters>
      <FilterBar
        :has-top-bar="false"
        :title="$str('filter_courses', 'core')"
        :show-reset="hasFilters"
        @reset="resetFilters"
      >
        <template v-slot:filters-left>
          <CategoryTree
            :selected-category="selectedCategory"
            size="small"
            @category-selected="selectCategory"
          />
        </template>
        <template v-slot:filters-right="{ stacked }">
          <SearchFilter
            v-model:value="filters.search"
            :debounce-input="true"
            :label="$str('coursename', 'core')"
            :placeholder="$str('search', 'totara_core')"
            :show-label="false"
            :stacked="stacked"
          />
        </template>
      </FilterBar>
    </template>

    <!-- View Browse courses datatable -->
    <template v-slot:browse-list="{ disabledItems, selectedItems, update }">
      <SelectTable
        :border-bottom-hidden="true"
        checkbox-v-align="center"
        :data="listItems"
        :disabled-ids="disabledItems"
        :large-check-box="true"
        :loading-overlay-active="true"
        :loading-preview="
          !closingWithData && listLoading && listItems.length === 0
        "
        :loading-preview-rows="Math.max(listItems.length, 5)"
        :no-label-offset="true"
        :select-all-enabled="true"
        :stack-at="0"
        :value="selectedItems"
        @input="update($event)"
      >
        <template v-slot:header-row>
          <HeaderCell ref="headerCell" size="12" valign="center">
            {{ $str('courses', 'core') }}
          </HeaderCell>
        </template>

        <template v-slot:row="{ row }">
          <Cell size="12" valign="center">
            <AdderRow
              :image-url="row.image"
              :full-name="row.fullname"
              :category-string="row.category ? row.category.full_path : ''"
            />
          </Cell>
        </template>
      </SelectTable>
    </template>

    <!-- View selected courses datatable -->
    <template v-slot:basket-list="{ disabledItems, selectedItems, update }">
      <SelectTable
        :border-bottom-hidden="true"
        checkbox-v-align="center"
        :data="selectedCourses"
        :disabled-ids="disabledItems"
        :large-check-box="true"
        :loading-overlay-active="true"
        :loading-preview="!closingWithData && selectedLoading"
        :no-label-offset="true"
        :select-all-enabled="true"
        :stack-at="0"
        :value="selectedItems"
        @input="update($event)"
      >
        <template v-slot:header-row>
          <HeaderCell size="12" valign="center">
            {{ $str('courses', 'core') }}
          </HeaderCell>
        </template>

        <template v-slot:row="{ row }">
          <Cell size="12" valign="center">
            <AdderRow
              :image-url="row.image"
              :full-name="row.fullname"
              :category-string="row.category ? row.category.full_path : ''"
            />
          </Cell>
        </template>
      </SelectTable>
    </template>
  </Adder>
</template>

<script>
// Components
import Adder from 'tui/components/adder/Adder';
import Cell from 'tui/components/datatable/Cell';
import FilterBar from 'tui/components/filters/FilterBar';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import SearchFilter from 'tui/components/filters/SearchFilter';
import SelectTable from 'tui/components/datatable/SelectTable';
import AdderRow from 'core_course/components/course_adder/Row';
import CategoryTree from 'core_course/components/category_tree/CategoryTree';

// Queries
import filterResults from 'core/graphql/course_selector';
import courses from 'core/graphql/course_list';

const PAGE_SIZE = 20;

export default {
  components: {
    Adder,
    Cell,
    FilterBar,
    HeaderCell,
    SearchFilter,
    SelectTable,
    AdderRow,
    CategoryTree,
  },

  props: {
    // Provide a custom query when the selection is from a subset of courses
    customQuery: Object,
    // customQueryFilters needs to be passed when a custom query requires additional filters
    customQueryFilters: Object,
    // customQueryKey needs to be passed if customQuery is provided
    customQueryKey: String,
    // Array of previously selected courses IDs
    existingItems: {
      type: Array,
      default: () => [],
    },
    // If the adder modal is currently open
    open: Boolean,
    // Display loading spinner on Add button
    showLoadingBtn: Boolean,
  },

  emits: ['cancel', 'add-button-clicked', 'added'],

  data() {
    return {
      closingWithData: false,
      filters: {
        search: '',
        category: '',
      },
      filterResults: { items: [] },
      selectedCourses: [],
      selectedCourseIds: [],
      nextCursor: false,
      nextPage: false,
      skipQueries: true,
      selection: [],
      selectedCategory: null,
    };
  },

  computed: {
    /**
     * @returns {Boolean} whether there is a filter set
     */
    hasFilters() {
      return this.selectedCategory != null || this.filters.search.length > 0;
    },

    /**
     * Return the query indicating which graphql call to invoke.
     *
     * @return {Object}
     */
    searchQuery() {
      return this.customQuery ? this.customQuery : filterResults;
    },

    /**
     * Return the query key to use when extracting data via graphql calls.
     *
     * @return {String}
     */
    queryKey() {
      return this.customQueryKey ? this.customQueryKey : 'core_course_selector';
    },

    /**
     * Is the filterResults query loading?
     *
     * @returns true if the filtered results are loading
     */
    listLoading() {
      return (
        this.$apollo.queries.filterResults &&
        this.$apollo.queries.filterResults.loading
      );
    },

    /**
     * Items to show in the select table
     *
     * @returns {Array} Course objects to display
     */
    listItems() {
      return (this.filterResults && this.filterResults.items) || [];
    },

    /**
     * Is the selected list loading
     *
     * @returns true if the selected course list is loading
     */
    selectedLoading() {
      return (
        this.$apollo.queries.selectedCourses &&
        this.$apollo.queries.selectedCourses.loading
      );
    },
  },

  watch: {
    /**
     * On opening of adder, unblock query
     */
    open() {
      if (this.open) {
        this.filters.search = '';
        this.selectedCategory = null;
        this.skipQueries = false;
      } else {
        this.skipQueries = true;
      }
    },
  },

  /**
   * Apollo queries have been registered here to provide support for custom queries
   */
  created() {
    /**
     * Request all courses query, by default the core query is used but a custom query
     * can be provided
     *
     * @return {Object} Returns an object containing an array of courses
     */
    this.$apollo.addSmartQuery('filterResults', {
      query: this.searchQuery,
      fetchPolicy: 'network-only',
      skip() {
        return this.skipQueries;
      },
      variables() {
        return {
          query: this.coreFilters(),
        };
      },

      update({ [this.queryKey]: courses }) {
        if (courses.total > courses.courses.length) {
          this.nextPage = {
            limit: PAGE_SIZE,
            cursor: courses.next_cursor,
            page: courses.page,
          };
        } else {
          this.nextPage = false;
        }

        let ret = {
          items: courses.courses,
        };
        return ret;
      },
    });

    this.$apollo.addSmartQuery('selectedCourses', {
      query: courses,
      skip() {
        return this.skipQueries;
      },
      variables() {
        return {
          query: [],
        };
      },

      update({ core_course_list: courses }) {
        return courses;
      },
    });
  },

  methods: {
    /**
     * Formulate the final filters to use for a search query.
     */
    coreFilters() {
      let pagination = this.nextPage;
      if (!pagination) {
        pagination = { limit: PAGE_SIZE, page: 0, cursor: '0' };
      }
      return {
        filters: this.courseFilters({
          search: this.filters.search,
          category_id: this.selectedCategory ? this.selectedCategory.id : '0',
        }),
        pagination: { limit: PAGE_SIZE },
      };
    },

    /**
     * Formulate the final individual filters to use for a query.
     *
     * @param {Object} coreFilters the core user filters to use.
     */
    courseFilters(coreFilters) {
      if (this.customQueryFilters) {
        let filters = Object.assign({}, this.customQueryFilters, coreFilters);
        return filters;
      } else {
        return coreFilters;
      }
    },

    /**
     * Load addition items and append to list
     */
    async loadMoreItems() {
      if (!this.nextPage) {
        return;
      }

      await this.$apollo.queries.filterResults.fetchMore({
        variables: {
          query: Object.assign(this.coreFilters(), {
            pagination: this.nextPage,
          }),
        },

        updateQuery: (previousResult, { fetchMoreResult }) => {
          const oldData = previousResult[this.queryKey].courses;
          const newData = fetchMoreResult[this.queryKey];
          const newList = oldData.concat(newData.courses);

          return {
            [this.queryKey]: {
              courses: newList,
              next_cursor: newData.next_cursor,
              total: newData.total,
              page: newData.page,
            },
          };
        },
      });
    },

    /**
     * Close the adder, returning the selected items data
     *
     * @param {Array} selection
     */
    async closeWithData(selection) {
      this.closingWithData = true;

      this.$emit('add-button-clicked');
      await this.updateSelectedItems(selection);

      this.$emit('added', { ids: selection, data: this.selectedCourses });
      this.closingWithData = false;
    },

    /**
     * Update the selected items data
     *
     * @param {Array} selection array of course id's
     * @return {Array} Course objects that are currently selected
     */
    async updateSelectedItems(selection) {
      await this.$apollo.queries.selectedCourses.refetch({
        query: selection,
      });
      return this.selectedCourses;
    },

    /**
     * Resets the filters from the filter bar
     */
    resetFilters() {
      this.filters.search = '';
      this.selectedCategory = null;
    },

    /**
     * Sets the selected category for the filter
     *
     * @param {Object} category the category to set as the selected category
     */
    selectCategory(category) {
      this.selectedCategory = category;
    },
  },
};
</script>
