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

  @author Qingyang Liu <qingyang.liue@totara.com>
  @module block_totara_catalog
-->

<template>
  <CardScroller
    v-if="learningItems"
    class="tui-block_totara_catalog-learningItemsScroller"
    :items="learningItems.items"
    :title="title"
    :title-tooltip="$str('tooltip', 'block_totara_catalog')"
    :title-href="titleUrl"
  >
    <template v-slot="{ item }">
      <CatalogItemCard
        :key="item.itemid"
        :item="item"
        :class="[
          'tui-block_totara_catalog-learningItemsScroller__item',
          `tui-block_totara_catalog-learningItemsScroller__item--minWidth-${cardMinWidth}`,
        ]"
      />
    </template>
  </CardScroller>
</template>

<script>
import CardScroller from 'tui/components/card/CardScroller';
import CatalogItemCard from 'totara_catalog/explore/items/CatalogItemCard';

export default {
  components: {
    CardScroller,
    CatalogItemCard,
  },

  props: {
    cardMinWidth: {
      type: String,
      default: 'default',
      validator: x => ['default'].includes(x),
    },
    catalogUrl: String,
    title: { type: String, required: true },
    learningItems: Object,
  },

  computed: {
    titleUrl() {
      return this.catalogUrl ?? this.$url('/totara/catalog/explore.php');
    },
  },
};
</script>

<style lang="scss">
.tui-block_totara_catalog-learningItemsScroller__item {
  &--minWidth-default {
    min-width: min(var(--tui-card-default-width), 100%);
  }
}
</style>
