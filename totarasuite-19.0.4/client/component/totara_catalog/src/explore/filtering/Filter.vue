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
  @module totara_catalog
-->

<script setup>
import SelectFilter from 'tui/components/filters/SelectFilter';
import MultiSelectCheckboxFilter from 'tui/components/filters/MultiSelectCheckboxFilter';
import TreeFilter from 'totara_catalog/explore/filtering/TreeFilter';

defineProps({
  type: String,
  title: String,
  options: Array,
  stacked: Boolean,
  // eslint-disable-next-line vue/require-prop-types
  value: {},
});

const emit = defineEmits(['update:value']);
</script>

<template>
  <MultiSelectCheckboxFilter
    v-if="type == 'multi'"
    :title="title"
    :has-columns="!stacked"
    :options="options"
    :value="value || []"
    @update:value="emit('update:value', $event)"
  />
  <TreeFilter
    v-else-if="type == 'tree'"
    :title="title"
    :options="options"
    :value="value"
    @update:value="emit('update:value', $event)"
  />
  <SelectFilter
    v-else-if="type == 'single'"
    :label="title"
    :show-label="true"
    :options="options"
    :stacked="true"
    :value="value || null"
    @update:value="emit('update:value', $event)"
  />
</template>
