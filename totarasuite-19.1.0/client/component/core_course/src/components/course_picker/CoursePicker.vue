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

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module core_course
-->

<template>
  <Picker
    v-model:value="selectedCourse"
    :columns="columns"
    :items="courses"
    :loading="loading"
    :loading-more="loadingMore"
    :show-load-more="showLoadMore"
    @input="handleInput"
    @load-more="loadMoreItems()"
  >
    <template v-slot:filters-left>
      <CategoryTree
        size="small"
        :selected-category="filters.category"
        :truncate-label="true"
        @category-selected="selectCategory"
      />
    </template>
    <template v-slot:filters-right="{ stacked }">
      <SearchFilter
        v-model:value="filters.search"
        :label="$str('coursename', 'core')"
        :placeholder="$str('search', 'totara_core')"
        :show-label="false"
        :stacked="stacked"
        debounce-input
      />
    </template>
  </Picker>
</template>

<script>
// Components
import CategoryTree from 'core_course/components/category_tree/CategoryTree';
import Picker from 'core_course/components/course_picker/Picker';
import SearchFilter from 'tui/components/filters/SearchFilter';

// Queries
import courseSelectorQuery from 'core/graphql/course_selector';

const PAGE_SIZE = 20;

export default {
  components: {
    CategoryTree,
    Picker,
    SearchFilter,
  },

  emits: ['input', 'update:value'],

  data() {
    return {
      // Column definitions for the picker component
      columns: [
        {
          id: 'fullname',
          label: this.$str('courses', 'core'),
        },
      ],
      // Course selector query results
      courses: [],
      // Filters for the course selector query
      filters: {
        search: null,
        category: null,
      },
      // Are we loading more courses
      loadingMore: false,
      // Data for the next page to load
      nextPage: false,
      // Current selected course as v-model
      selectedCourse: null,
    };
  },

  computed: {
    /**
     * Is the query loading. Excludes fetching more
     *
     * @return {Boolean}
     */
    loading() {
      return this.$apollo.loading && !this.loadingMore;
    },

    /**
     * Should we show the load more button
     *
     * @return {Boolean}
     */
    showLoadMore() {
      return this.nextPage && !this.loadingMore;
    },
  },

  methods: {
    /**
     * Load additional courses and append to list
     */
    async loadMoreItems() {
      if (!this.nextPage) {
        return;
      }

      this.loadingMore = true;
      await this.$apollo.queries.courses.fetchMore({
        variables: {
          query: {
            filters: {
              search: this.filters.search,
              category_id: this.filters.category ? this.filters.category.id : 0,
            },
            pagination: this.nextPage,
          },
        },

        updateQuery: (previousResult, { fetchMoreResult }) => {
          const oldData = previousResult['core_course_selector'].courses;
          const newData = fetchMoreResult['core_course_selector'];
          const newList = oldData.concat(newData.courses);

          return {
            core_course_selector: {
              courses: newList,
              next_cursor: newData.next_cursor,
              total: newData.total,
              page: newData.page,
            },
          };
        },
      });
      this.loadingMore = false;
    },

    /**
     * Handle the category filter selection
     *
     * @param category
     */
    selectCategory(category) {
      this.filters.category = category;
    },

    handleInput(value) {
      this.$emit('update:value', value);
      this.$emit('input', value);
    },
  },

  apollo: {
    courses: {
      query: courseSelectorQuery,
      variables() {
        return {
          query: {
            filters: {
              search: this.filters.search,
              category_id: this.filters.category ? this.filters.category.id : 0,
            },
            pagination: { limit: PAGE_SIZE },
          },
        };
      },
      update({ core_course_selector: result }) {
        if (result.total > result.courses.length) {
          this.nextPage = {
            limit: PAGE_SIZE,
            cursor: result.next_cursor,
            page: result.page,
          };
        } else {
          this.nextPage = false;
        }

        return result.courses;
      },
    },
  },
};
</script>
