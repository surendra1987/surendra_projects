<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module core_my
-->

<template>
  <div class="tui-overviewCount">
    <div class="tui-overviewCount__items">
      <div class="tui-overviewCount__itemsContent">
        <div class="tui-overviewCount__itemsContent-count">
          {{ items }}
        </div>
        <div class="tui-overviewCount__itemsContent-text">
          {{ itemLabel }}
        </div>
      </div>
    </div>
    <div v-if="dueSoon" class="tui-overviewCount__due">
      <div class="tui-overviewCount__dueContent">
        <div class="tui-overviewCount__dueContent-count">
          {{ dueSoon }}
        </div>
        <div class="tui-overviewCount__dueContent-text">
          <OverdueIcon
            :aria-hidden="true"
            class="tui-overviewCount__dueContent-overdueIcon"
          />
          {{ $str('overview_due_soon', 'core_my') }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import OverdueIcon from 'tui/components/icons/Overdue';

export default {
  components: { OverdueIcon },

  props: {
    // Number of due soon
    dueSoon: {
      type: Number,
    },
    // Number of items
    items: {
      type: Number,
      required: true,
    },
    // Text to be displayed below the item count
    itemLabel: {
      type: String,
      required: true,
    },
  },
};
</script>

<style lang="scss">
.tui-overviewCount {
  display: flex;
  width: 100%;
  margin-top: var(--gap-2);
  padding-bottom: var(--gap-4);

  &__items,
  &__due {
    width: 50%;
    text-align: center;
  }

  &__itemsContent,
  &__dueContent {
    display: flex;
    flex-direction: column;
    justify-content: center;

    &-count {
      @include font(h1);
      color: var(--color-neutral-7);
    }

    &-text {
      @include font(body-sm);
      color: var(--color-neutral-6);
    }

    &-overdueIcon {
      color: var(--color-prompt-warning);
    }
  }
}
</style>
