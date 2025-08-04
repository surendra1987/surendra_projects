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

  @author Simon Chester <simon.chester@totara.com>
  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module tui
-->

<script setup>
import { computed, ref } from 'vue';
import FilterFieldset from 'tui/components/form/FilterFieldset';
import TreeView from 'tui/components/treeview/TreeView';
import { listToTree } from './tree';

const props = defineProps({
  title: { type: String, required: true },
  options: Array,
  value: [Number, String],
  hideLegend: Boolean,
});

const emit = defineEmits(['update:value']);

function convertNode(node) {
  return {
    id: node.id,
    label: node.label,
    selectable: true,
    children: node.children ? node.children.map(convertNode) : [],
  };
}

const items = computed(() => listToTree(props.options).map(convertNode));
const expandedItems = ref([]);
const selectedItems = computed({
  get: () => [props.value],
  set(value) {
    emit('update:value', value[0]);
  },
});
</script>

<template>
  <FilterFieldset
    class="tui-totara_catalog-treeFilter"
    :hidden="hideLegend"
    :legend="title"
  >
    <TreeView
      v-model:selected-items="selectedItems"
      v-model:expanded-items="expandedItems"
      :items="items"
    />
  </FilterFieldset>
</template>

<style lang="scss">
.tui-totara_catalog-treeFilter {
  padding: gap(1);
}
</style>
