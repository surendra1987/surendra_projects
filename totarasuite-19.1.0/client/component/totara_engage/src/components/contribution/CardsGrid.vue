<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Kian Nguyen <kian.nguyen@totaralearning.com>
  @module totara_engage
-->

<template>
  <VirtualScroll
    data-key="key"
    style="display: grid"
    class="tui-engageCardsGrid"
    :data-list="cards"
    :page-mode="true"
    :aria-label="$str('items_list', 'totara_engage')"
    :is-loading="isLoading"
    @scrollbottom="scrolledToBottom"
  >
    <template v-slot:item="{ item, posInSet, setSize }">
      <div class="tui-engageCardsGrid__item">
        <EngageCard
          :card-attribute="item"
          :aria-labelledby="$id(`item-${item.instanceid}-label`)"
          :label-id="$id(`item-${item.instanceid}-label`)"
          :aria-posinset="posInSet"
          :aria-setsize="setSize"
          :show-footnotes="showFootnotes"
          @refetch="$emit('refetch')"
        />
      </div>
    </template>
    <template v-slot:footer>
      <PageLoader :fullpage="false" :loading="isLoading" />
    </template>
  </VirtualScroll>
</template>

<script>
import EngageCard from 'totara_engage/components/card/compute/EngageCard';
import VirtualScroll from 'tui/components/virtualscroll/VirtualScroll';
import PageLoader from 'tui/components/loading/Loader';

export default {
  components: {
    EngageCard,
    VirtualScroll,
    PageLoader,
  },

  inheritAttrs: false,

  props: {
    cards: {
      required: true,
      type: Array,
    },
    showFootnotes: {
      type: Boolean,
      default: false,
    },
    isLoading: {
      type: Boolean,
      required: true,
    },
  },

  emits: ['refetch', 'scrolledtobottom'],

  methods: {
    scrolledToBottom() {
      this.$emit('scrolledtobottom');
    },
  },
};
</script>

<style lang="scss">
.tui-engageCardsGrid {
  position: relative;
  grid-template-columns: repeat(
    auto-fill,
    minmax(min(var(--tui-card-default-width), 100%), 1fr)
  );
  gap: var(--gap-card-grid);

  &__item {
    display: flex;
    flex-direction: column;
  }
}
</style>
