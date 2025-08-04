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
defineProps({
  items: { type: Array, required: true },
});
</script>

<template>
  <div
    class="tui-totara_catalog-textPlaceholders"
    data-testid="text-placeholders"
  >
    <div v-for="(item, i) in items" :key="i">
      {{ (item.show_label ? item.label + ': ' : '') + item.data }}
    </div>
  </div>
</template>

<style lang="scss">
.tui-totara_catalog-textPlaceholders {
  display: flex;
  flex-flow: column;
  gap: gap(2);
}
</style>
