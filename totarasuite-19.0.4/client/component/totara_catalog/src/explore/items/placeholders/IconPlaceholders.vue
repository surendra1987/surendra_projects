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
defineProps({
  items: { type: Array, required: true },
});
</script>

<template>
  <div
    class="tui-totara_catalog-iconPlaceholders"
    data-testid="icon-placeholders"
  >
    <template v-for="(item, i) in items" :key="i">
      <div
        v-if="item.icon"
        class="tui-totara_catalog-iconPlaceholders__item"
        v-html="item.icon"
      />
      <div
        v-else-if="item.url"
        class="tui-totara_catalog-iconPlaceholders__item"
      >
        <img :src="item.url" :alt="item.alt" :title="item.alt" />
      </div>
    </template>
  </div>
</template>

<style lang="scss">
.tui-totara_catalog-iconPlaceholders {
  display: flex;
  flex-flow: row wrap;
  gap: gap(2);

  &__item > img {
    width: 1.4rem;
    height: 1.4rem;
    object-fit: contain;
  }
}
</style>
