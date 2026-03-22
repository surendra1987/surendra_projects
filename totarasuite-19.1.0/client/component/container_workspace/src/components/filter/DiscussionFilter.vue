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

  @author Kian Nguyen <kian.nguyen@totaralearning.com>
  @module container_workspace
-->
<template>
  <div class="tui-workspaceDiscussionFilter">
    <div class="tui-workspaceDiscussionFilter__search">
      <SearchBox
        v-model:value="innerSearchTerm"
        :aria-label="$str('search_discussions', 'container_workspace')"
        :placeholder="$str('search_discussions', 'container_workspace')"
        class="tui-workspaceDiscussionFilter__search-box"
        @submit="submitSearch"
        @clear="clearSearch"
      />

      <a
        v-if="showSort"
        :href="getFileUrl()"
        class="tui-workspaceDiscussionFilter__search-filesLink"
      >
        {{ $str('browse_files', 'container_workspace') }}
      </a>
    </div>

    <slot name="pinned-discussions" />

    <div v-if="showSort" class="tui-workspaceDiscussionFilter__sortBox">
      <SelectFilter
        v-model:value="innerSort"
        :label="$str('sortby', 'core')"
        :show-label="true"
        :options="sortOptions"
      />
    </div>
  </div>
</template>

<script>
import SearchBox from 'tui/components/form/SearchBox';
import SelectFilter from 'tui/components/filters/SelectFilter';

// GraphQL queries
import getDiscussionOptions from 'container_workspace/graphql/discussion_filter_options';

export default {
  components: {
    SearchBox,
    SelectFilter,
  },

  props: {
    searchTerm: String,
    sort: String,

    workspaceId: {
      type: [Number, String],
      required: true,
    },

    urlParam: String,
  },

  emits: ['update-sort', 'update-search-term'],

  apollo: {
    sortOptions: {
      query: getDiscussionOptions,
      skip() {
        return !this.showSort;
      },
      context: { batch: true },
      update({ sorts }) {
        return Array.prototype.map.call(sorts, ({ value, label }) => {
          return {
            label: label,
            id: value,
          };
        });
      },
    },
  },

  data() {
    return {
      innerSearchTerm: this.searchTerm,
      innerSort: this.sort,
      sortOptions: [],
    };
  },

  computed: {
    showSort() {
      return !this.innerSearchTerm;
    },
  },

  watch: {
    /**
     *
     * @param {String} value
     */
    sort(value) {
      if (value !== this.innerSort) {
        this.innerSort = value;
      }
    },

    /**
     *
     * @param {String} value
     */
    innerSort(value) {
      if (value !== this.sort) {
        this.$emit('update-sort', value);
      }
    },
  },

  methods: {
    submitSearch() {
      this.$emit('update-search-term', this.innerSearchTerm);
    },

    clearSearch() {
      this.innerSearchTerm = '';
      this.submitSearch();
    },

    getFileUrl() {
      let params = {
        id: this.workspaceId,
      };

      if (this.urlParam) {
        params.from = this.urlParam;
      }
      return this.$url('/container/type/workspace/workspace_files.php', params);
    },
  },
};
</script>

<style lang="scss">
.tui-workspaceDiscussionFilter {
  display: flex;
  flex-wrap: wrap;
  margin-top: calc(var(--gap-4) * -1);
  margin-bottom: var(--gap-8);

  & > * {
    display: flex;
    margin-top: var(--gap-4);
  }

  &__search {
    align-items: center;

    &-filesLink {
      margin: 0 var(--gap-8);
    }
  }

  &__sortBox {
    justify-content: flex-end;
    margin-left: auto;
  }
}
</style>
