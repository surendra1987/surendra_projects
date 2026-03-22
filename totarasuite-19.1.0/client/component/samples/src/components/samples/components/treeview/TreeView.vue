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
  @module samples
-->

<script setup>
import { ref, watch } from 'vue';
import Button from 'tui/components/buttons/Button';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import Separator from 'tui/components/decor/Separator';
import TreeView from 'tui/components/treeview/TreeView';

const multiSelect = ref(false);

const items = ref(generateItems());

const expandedItems = ref([]);

const selectedItems = ref([]);

watch(multiSelect, () => {
  selectedItems.value = [];
});

function generateNewItems() {
  items.value = generateItems();
}

/**
 * Generate randomised tree item data
 */
function generateItems(idCounter = { count: 1 }) {
  const maxDepth = 4;
  const maxBreadth = 6;
  const nonSelectableCount = Math.floor(Math.random() * 3) + 1;
  let nonSelectableAssigned = 0;

  function generateItem(level) {
    const itemId = idCounter.count++;
    const isSelectable =
      nonSelectableAssigned >= nonSelectableCount || Math.random() > 0.7;
    if (!isSelectable) {
      nonSelectableAssigned++;
    }

    const labelPrefix =
      level === 1 ? 'Folder' : level === 2 ? 'Sub-folder' : 'File';
    const label = `${labelPrefix} ${itemId}${
      !isSelectable ? ' (not selectable)' : ''
    }`;

    const item = {
      id: itemId,
      label: label,
      selectable: isSelectable,
      children: [],
    };

    item.children =
      level < maxDepth
        ? Array.from({ length: Math.floor(Math.random() * maxBreadth) }, () =>
            generateItem(level + 1)
          )
        : [];

    return item;
  }

  return Array.from({ length: Math.floor(Math.random() * 5) + 2 }, () =>
    generateItem(1)
  );
}
</script>

<template>
  <SamplesExample>
    <p>Selected items: {{ selectedItems }}</p>
    <p>Expanded items: {{ expandedItems }}</p>
    <Separator />
    <TreeView
      v-model:selectedItems="selectedItems"
      v-model:expandedItems="expandedItems"
      :items="items"
      :multi-select="multiSelect"
      class="tui-sample__treeView"
    />
  </SamplesExample>

  <SamplesCtl>
    <FormRow v-slot="{ id }" label="Multiselect">
      <RadioGroup :id="id" v-model:value="multiSelect" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        multiSelect
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Items" required>
      <Button :text="'Generate random tree items'" @click="generateNewItems" />
      <FormRowDetails :id="id">
        items
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>

<style lang="scss">
.tui-sample__treeView {
  max-width: 300px;
}
</style>
