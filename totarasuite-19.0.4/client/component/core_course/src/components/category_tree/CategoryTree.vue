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
  <Dropdown
    ref="categorySelector"
    class="tui-core_course-categorySelector"
    :close-on-click="false"
  >
    <template v-slot:trigger="{ toggle, isOpen }">
      <Button
        :caret="true"
        :styleclass="{
          stealth: true,
          small: size == 'small',
        }"
        :text="truncateLabel ? truncateString(categoryLabel) : categoryLabel"
        :title="categoryLabel"
        :aria-expanded="isOpen"
        @click="toggle"
      />
    </template>
    <Tree
      v-model:value="selection"
      class="tui-core_course-categorySelector__categorySelector"
      :tree-data="categoryTree"
      :depth-limit="5"
      label-type="button"
      :value="selectedCategory"
      @label-click="updateTree"
    />
  </Dropdown>
</template>
<script>
// components
import Tree from 'tui/components/tree/Tree';
import Dropdown from 'tui/components/dropdown/Dropdown';
import Button from 'tui/components/buttons/Button';

// Queries
import categories from 'core/graphql/categories';

export default {
  components: {
    Tree,
    Dropdown,
    Button,
  },

  props: {
    size: {
      type: String,
      validator: size => ['small', 'medium'].includes(size),
    },
    selectedCategory: Object,
    truncateLabel: Boolean,
  },

  emits: ['category-selected'],

  apollo: {
    categoryList: {
      query: categories,
      update({ core_categories }) {
        return core_categories;
      },
    },
  },

  data() {
    return {
      selection: [],
      categoryList: [],
    };
  },

  computed: {
    /**
     * The category tree for the filter
     *
     * @returns {Object} the category tree
     */
    categoryTree() {
      let top = [
        {
          children: this.buildCategoryTree(this.categoryList, null),
          content: {},
          label: this.$str('all', 'core'),
          name: this.$str('all', 'core'),
          id: '0',
        },
      ];
      return top;
    },

    /**
     * What to display on the button for the category tree
     */
    categoryLabel() {
      return this.selectedCategory
        ? this.selectedCategory.name
        : this.$str('all', 'core');
    },
  },

  methods: {
    /**
     * Builds the course category tree
     *
     * @param {Array} data a flat list of categories
     * @param {Object} node the parent node to populate
     * @return {Object} the category tree for the given node
     */
    buildCategoryTree(data, node) {
      return data
        .filter(category => {
          if (node === null) {
            return category.parent === null;
          } else {
            return category.parent && category.parent.id === node.id;
          }
        })
        .map(category => {
          let categoryNode = Object.assign({}, category, {
            children: this.buildCategoryTree(data, category),
            content: {},
            label: category.name,
            id: category.id,
          });
          return categoryNode;
        });
    },

    /**
     * Truncate the text to 30 characters
     * and replace the last three characters with an ellipsis
     *
     * @param {String} str string to be truncated
     */
    truncateString(str) {
      const ellipsis = '...';
      const characters = 30;

      return str.length > characters
        ? str.substr(0, characters - ellipsis.length) + ellipsis
        : str;
    },

    updateTree(event) {
      let sc = this.categoryList.find(category => category.id == event);
      this.$emit('category-selected', sc);
      this.$refs.categorySelector.dismiss();
    },
  },
};
</script>
<style lang="scss">
.tui-core_course-categorySelector {
  &__categorySelector {
    margin: var(--gap-2);
  }
}
</style>
