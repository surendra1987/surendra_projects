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

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @package core_my
-->

<template>
  <!-- Represents the updated state across the different plugins -->
  <Popover
    class="tui-myPerformOverviewItemUpdated"
    position="bottom-left"
    size="lg"
    :triggers="['click']"
  >
    <template v-slot:trigger>
      <Button
        class="tui-myPerformOverviewItemUpdated__button"
        :styleclass="{ small: true, transparent: true }"
        :text="$str('overview_updated_date', 'core_my', date)"
      />
    </template>
    <div class="tui-myPerformOverviewItemUpdated__content">
      <span>{{ description }}</span>
    </div>
  </Popover>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import Popover from 'tui/components/popover/Popover';

export default {
  components: {
    Button,
    Popover,
  },

  props: {
    // Update date
    date: {
      required: true,
      type: String,
    },
    // Update description
    description: {
      required: true,
      type: String,
    },
  },
};
</script>

<style lang="scss">
.tui-myPerformOverviewItemUpdated {
  hyphens: none;

  &__button {
    @include font(body-sm);
  }

  &__content {
    display: flex;

    & > * + * {
      margin-left: var(--gap-2);
    }
  }
}
</style>
