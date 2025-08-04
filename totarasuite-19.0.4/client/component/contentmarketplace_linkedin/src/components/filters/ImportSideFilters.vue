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
  @package contentmarketplace_linkedin
-->

<template>
  <FilterSidePanel
    :value="value"
    :skip-content-id="contentId"
    :title="$str('filters_title', 'contentmarketplace_linkedin')"
    @update:value="
      value => {
        $emit('update:value', value);
        $emit('input', value);
      }
    "
  >
    <!-- Search filter -->
    <SearchFilter
      :value="value.search"
      :label="$str('a11y_search_filter', 'contentmarketplace_linkedin')"
      :placeholder="
        $str('search_filter_placeholder', 'contentmarketplace_linkedin')
      "
      @input="value => setFilter('search', value)"
    />

    <div>
      <!-- Subject tree filter -->
      <Tree
        :value="openNodes.subjects"
        :header-level="3"
        :separator="true"
        :tree-data="filters.subjects"
        @input="value => setOpenNodes('subject', value)"
      >
        <template v-slot:content="{ content, label }">
          <MultiSelectFilter
            :value="value.subjects"
            :hidden-title="true"
            :options="content"
            :title="label"
            :visible-item-limit="5"
            @input="value => setFilter('subjects', value)"
          />
        </template>
      </Tree>

      <!-- Time tree filter -->
      <Tree
        :value="openNodes.time_to_complete"
        :header-level="3"
        :separator="true"
        :tree-data="filters.time_to_complete"
        @input="value => setOpenNodes('time_to_complete', value)"
      >
        <template v-slot:content="{ content, label }">
          <MultiSelectFilter
            :value="value.time_to_complete"
            :hidden-title="true"
            :options="content"
            :title="label"
            :visible-item-limit="5"
            @input="value => setFilter('time_to_complete', value)"
          />
        </template>
      </Tree>

      <!-- In catalog tree filter -->
      <Tree
        :value="openNodes.in_catalog"
        :header-level="3"
        :separator="true"
        :tree-data="filters.in_catalog"
        @input="value => setOpenNodes('in_catalog', value)"
      >
        <template v-slot:content="{ content, label }">
          <MultiSelectFilter
            :value="value.in_catalog"
            :hidden-title="true"
            :options="content"
            :title="label"
            :visible-item-limit="5"
            @input="value => setFilter('in_catalog', value)"
          />
        </template>
      </Tree>
    </div>
  </FilterSidePanel>
</template>

<script>
import FilterSidePanel from 'tui/components/filters/FilterSidePanel';
import MultiSelectFilter from 'tui/components/filters/MultiSelectFilter';
import SearchFilter from 'tui/components/filters/SearchFilter';
import Tree from 'tui/components/tree/Tree';

export default {
  components: {
    FilterSidePanel,
    MultiSelectFilter,
    SearchFilter,
    Tree,
  },

  props: {
    contentId: String,
    filters: {
      type: Object,
      required: true,
    },
    openNodes: Object,
    value: Object,
  },

  emits: ['input', 'update:value', 'update:openNodes'],

  methods: {
    setFilter(name, val) {
      const value = {
        ...this.value,
        [name]: val,
      };
      this.$emit('update:value', value);
      this.$emit('input', value);
    },

    setOpenNodes(name, val) {
      this.$emit('update:openNodes', {
        ...this.openNodes,
        [name]: val,
      });
    },
  },
};
</script>
