<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author ben fesili <ben.fesili@totara.com>
  @module totara_webhook
-->

<script setup>
import {} from 'totara_webhook/constants';
import FilterBarArea from 'tui/components/filters/FilterBarArea';
import SearchFilter from 'tui/components/filters/SearchFilter';

defineProps({
  value: Object,
  defaultValue: Object,
});

const emit = defineEmits(['update:value']);
</script>

<template>
  <FilterBarArea
    :value="value"
    :reset-values="defaultValue"
    accessibility-title="Webhook filter bar area"
    @update:value="emit('update:value', $event)"
  >
    <template v-slot:extra-filters="{ filters, update }">
      <SearchFilter
        :label="$str('name', 'totara_webhook')"
        :bar-filter="true"
        :stacked="true"
        :placeholder="$str('filter', 'core')"
        :show-label="true"
        :value="filters.name"
        @input="update('name', $event)"
      />
      <SearchFilter
        :label="$str('endpoint', 'totara_webhook')"
        :bar-filter="true"
        :stacked="true"
        :placeholder="$str('filter', 'core')"
        :show-label="true"
        :value="filters.endpoint"
        @input="update('endpoint', $event)"
      />
    </template>
  </FilterBarArea>
</template>
