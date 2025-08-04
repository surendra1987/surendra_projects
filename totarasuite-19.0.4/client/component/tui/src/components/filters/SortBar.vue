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
  @module tui
-->

<script setup>
import SelectFilter from 'tui/components/filters/SelectFilter';

defineProps({
  options: Array,
  sortBy: String,
});

defineEmits(['update:sortBy']);
</script>

<template>
  <div class="tui-sortBar">
    <div class="tui-sortBar__start">
      <slot name="start" />
    </div>

    <div class="tui-sortBar__end">
      <SelectFilter
        v-if="options?.length > 0"
        :value="sortBy"
        :label="$str('sortby', 'core')"
        :options="options"
        show-label
        @update:value="$emit('update:sortBy', $event)"
      />
    </div>
  </div>
</template>

<style lang="scss">
.tui-sortBar {
  display: flex;
  flex-flow: row wrap;
  gap: var(--gap-3) var(--gap-6);
  align-items: center;
  justify-content: space-between;

  &__start {
    display: flex;
    gap: var(--gap-2);
    align-items: center;
  }
}
</style>
