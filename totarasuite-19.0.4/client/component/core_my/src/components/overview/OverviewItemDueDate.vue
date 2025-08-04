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
  <!-- Represents the due date state across the different plugins -->
  <div class="tui-myPerformOverviewItemDueDate">
    <template v-if="!hideState && (overdue || dueSoon)">
      <Tooltip :position="'bottom'" :content="dueState">
        <template v-slot:trigger>
          <ButtonIcon
            :aria-label="
              $str('a11y_overview_due_date_status_button', 'core_my')
            "
            class="tui-myPerformOverviewItemDueDate__icon"
            :class="{
              'tui-myPerformOverviewItemDueDate__icon--dueSoon': dueSoon,
              'tui-myPerformOverviewItemDueDate__icon--overdue': overdue,
            }"
            :styleclass="{
              small: true,
              transparentNoPadding: true,
            }"
            :title="false"
          >
            <OverdueIcon :size="100" />
          </ButtonIcon>
        </template>
      </Tooltip>
    </template>

    <span
      class="tui-myPerformOverviewItemDueDate__date"
      :class="{
        'tui-myPerformOverviewItemDueDate__date--overdue': overdue,
      }"
    >
      {{ $str('overview_due_date', 'core_my', date) }}
    </span>
  </div>
</template>

<script>
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import OverdueIcon from 'tui/components/icons/Overdue';
import Tooltip from 'tui/components/popover/Tooltip';

export default {
  components: {
    ButtonIcon,
    OverdueIcon,
    Tooltip,
  },

  props: {
    // Due date
    date: {
      required: true,
      type: String,
    },
    // Due soon (next 7 days)
    dueSoon: {
      type: Boolean,
    },
    // Hide the overdue or due soon state
    hideState: {
      type: Boolean,
    },
    // Overdue
    overdue: {
      type: Boolean,
    },
  },

  computed: {
    /**
     * Return the current due state string
     *
     * @return {String} Overdue || Due soon || null
     */
    dueState() {
      return this.dueSoon
        ? this.$str('overview_due_soon', 'core_my')
        : this.overdue
        ? this.$str('overview_overdue', 'core_my')
        : '';
    },
  },
};
</script>

<style lang="scss">
.tui-myPerformOverviewItemDueDate {
  display: flex;
  flex-wrap: nowrap;
  gap: gap(0.5);

  & > * + * {
    margin-left: calc(var(--gap-1) / 2);
  }

  &__button {
    @include font(body-sm);
  }

  &__icon {
    top: 1px;

    &--dueSoon {
      color: var(--color-prompt-warning);

      &:active,
      &:focus,
      &:active:focus,
      &:active:hover,
      &:hover {
        color: var(--color-prompt-warning);
      }
    }

    &--overdue {
      color: var(--color-prompt-alert);

      &:active,
      &:focus,
      &:active:focus,
      &:active:hover,
      &:hover {
        color: var(--color-prompt-alert);
      }
    }
  }

  &__iconText {
    @include font(body-sm);
  }

  &__date {
    &--overdue {
      color: var(--color-prompt-alert);
    }
  }
}
</style>
