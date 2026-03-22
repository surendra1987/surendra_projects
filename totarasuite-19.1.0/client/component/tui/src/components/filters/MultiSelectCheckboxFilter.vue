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

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @module tui
-->

<template>
  <FilterFieldset
    class="tui-multiSelectCheckboxFilter"
    :hidden="hiddenTitle"
    :legend="title"
  >
    <div
      class="tui-multiSelectCheckboxFilter__items"
      :class="{
        'tui-multiSelectCheckboxFilter__items--hasColumns': hasColumns,
      }"
    >
      <div
        v-for="{ label, id } in options"
        :key="id"
        class="tui-multiSelectCheckboxFilter__items-item"
        :class="{
          'tui-multiSelectCheckboxFilter__items-item--twoColumn': hasColumns,
        }"
      >
        <Checkbox
          :checked="isItemSelected(id)"
          @change="itemStateChange(id, $event)"
        >
          {{ label }}
        </Checkbox>
      </div>
    </div>
  </FilterFieldset>
</template>

<script>
import Checkbox from 'tui/components/form/Checkbox';
import FilterFieldset from 'tui/components/form/FilterFieldset';

export default {
  components: {
    Checkbox,
    FilterFieldset,
  },

  props: {
    // Checkboxes are displayed in columns
    hasColumns: { type: Boolean },
    // checkbox group title is hidden
    hiddenTitle: { type: Boolean },
    // checkbox options
    options: { type: Array },
    // Checkbox group title
    title: { type: String, required: true },
    // Active checkboxes
    value: { type: Array },
  },

  emits: ['input', 'update:value'],

  methods: {
    /**
     * remove selected option ID from selection and emit the update
     *
     * @param {Int} id
     * @param {Array} selection
     */
    deselectItem(id, selection) {
      if (selection.indexOf(id) !== -1) {
        selection = [...selection];
        selection.splice(selection.indexOf(id), 1);
        this.$emit('update:value', selection);
        this.$emit('input', selection);
      }
    },

    /**
     * Check if item is selected
     *
     * @param {Int} id
     * @return {Boolean}
     */
    isItemSelected(id) {
      return this.value.indexOf(id) !== -1;
    },

    /**
     * item selection state has changed
     *
     * @param {Int} id
     * @param {Boolean} checked
     */
    itemStateChange(id, checked) {
      let selection = [].concat(this.value);
      if (!checked) {
        this.deselectItem(id, selection);
      } else {
        this.selectItem(id, selection);
      }
    },

    /**
     * Add selected option ID to selection and emit the update
     *
     * @param {Int} id
     * @param {Array} selection
     */
    selectItem(id, selection) {
      // If not already selected
      if (!this.value.includes(id)) {
        selection = selection.concat([id]);
        this.$emit('update:value', selection);
        this.$emit('input', selection);
      }
    },
  },
};
</script>

<style lang="scss">
.tui-multiSelectCheckboxFilter {
  display: flex;
  flex-direction: column;
  gap: var(--gap-2);

  &__items {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    gap: var(--gap-4) 0;
    white-space: normal;
  }
}

@media (min-width: $tui-screen-xs) {
  .tui-multiSelectCheckboxFilter {
    &__items {
      &--hasColumns {
        flex-direction: row;
        flex-wrap: wrap;
        gap: var(--gap-4);
      }

      &-item {
        &--twoColumn {
          width: 47%;
          overflow-wrap: break-word;
        }
      }
    }
  }
}
</style>
