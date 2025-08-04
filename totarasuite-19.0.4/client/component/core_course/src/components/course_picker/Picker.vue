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
  <div class="tui-core_course-picker">
    <FilterBar
      v-if="$slots['filters-left'] || $slots['filters-right']"
      :has-top-bar="false"
      :title="$str('filter_courses', 'core')"
    >
      <template v-slot:filters-left="{ stacked }">
        <slot :stacked="stacked" name="filters-left" />
      </template>
      <template v-slot:filters-right="{ stacked }">
        <slot :stacked="stacked" name="filters-right" />
      </template>
    </FilterBar>
    <div class="tui-core_course-picker__listScroll">
      <Table
        class="tui-core_course-picker__table"
        :data="items"
        :header-has-loaded="true"
        :loading-overlay-active="true"
        :loading-preview="loading"
        :loading-preview-rows="loadingPreviewRows"
        :no-items-text="$str('noitems', 'totara_core')"
        :selection="[value]"
        :stack-at="!stackedCellLabels ? 0 : undefined"
      >
        <template v-slot:header-row>
          <HeaderCell
            v-for="column in columns"
            :key="column.id"
            :size="column.size && String(column.size)"
            valign="center"
          >
            {{ column.label }}
          </HeaderCell>
        </template>

        <template v-slot:row="{ row }">
          <Cell
            v-for="(column, index) in columns"
            :key="column.id"
            :column-header="column.label"
            :size="column.size && String(column.size)"
            valign="center"
          >
            <Radio
              v-if="index === 0"
              :checked="isChecked(row)"
              :name="$id('radio')"
              :value="row"
              @select="
                () => {
                  $emit('update:value', row);
                  $emit('input', row);
                }
              "
            >
              <slot :id="column.id" name="column" :row="row">
                {{ row[column.id] }}
              </slot>
            </Radio>
            <slot v-else :id="column.id" name="column" :row="row">
              {{ row[column.id] }}
            </slot>
          </Cell>
        </template>
      </Table>
      <div v-if="showLoadMore" class="tui-core_course-picker__loadMore">
        <Button
          :text="$str('loadmore', 'totara_core')"
          @click="$emit('load-more')"
        />
      </div>
      <Loader v-if="loadingMore" :loading="true" />
    </div>
    <div class="tui-core_course-picker__selectionInfo">
      {{
        value
          ? $str('itemselected', 'totara_core', getItemName(value))
          : $str('noitemselected', 'totara_core')
      }}
    </div>
  </div>
</template>

<script>
// Components
import Button from 'tui/components/buttons/Button';
import Cell from 'tui/components/datatable/Cell';
import FilterBar from 'tui/components/filters/FilterBar';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import Loader from 'tui/components/loading/Loader';
import Radio from 'tui/components/form/Radio';
import Table from 'tui/components/datatable/Table';

export default {
  components: {
    Button,
    Cell,
    FilterBar,
    HeaderCell,
    Loader,
    Radio,
    Table,
  },

  props: {
    // Column definitions
    columns: {
      type: Array,
      required: true,
    },
    // Item data to display in the table
    items: Array,
    // Should we display the loading state
    loading: Boolean,
    // Should we display the loading more loader
    loadingMore: Boolean,
    // Should we show the load more button
    showLoadMore: Boolean,
    // When stacked should the table cells contain the column labels
    stackedCellLabels: Boolean,
    // v-model value for selected item
    value: Object,
  },

  emits: ['input', 'load-more', 'update:value'],

  computed: {
    /**
     * The number of preview rows when loading
     *
     * @return {Number}
     */
    loadingPreviewRows() {
      const count = this.items.length;
      return count === 0 ? 5 : count;
    },
  },

  methods: {
    /**
     * Get the name of this item based on the id defined in the columns
     *
     * @param {Object} row
     * @return {String}
     */
    getItemName(row) {
      const key = this.columns && this.columns[0] && this.columns[0].id;
      return row[key];
    },

    /**
     * Is this row the selected row
     *
     * @param {Object} row
     * @return {Boolean}
     */
    isChecked(row) {
      if (this.value && row) {
        return this.value.id == row.id;
      }
    },
  },
};
</script>

<style lang="scss">
.tui-core_course-picker {
  display: flex;
  flex-direction: column;

  &__table {
    margin: var(--gap-4) 0;
  }

  &__selectionInfo {
    padding-top: var(--gap-3);
    border-top: var(--border-width-normal) solid var(--color-neutral-5);
    @include font(body-sm);
  }

  &__listScroll {
    height: 100%;
    overflow-y: auto;
  }

  &__loadMore {
    display: flex;
    justify-content: center;
    margin: var(--gap-4) 0;
  }
}
</style>
