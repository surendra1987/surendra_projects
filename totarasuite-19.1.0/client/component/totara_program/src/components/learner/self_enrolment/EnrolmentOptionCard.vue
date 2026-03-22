<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Rami Habib <rami.habib@totara.com>
  @module totara_program
-->

<template>
  <Card
    :no-border="true"
    :has-hover-shadow="clickable"
    :clickable="clickable"
    :aria-label="
      $str('enrolmentconfirmationaction', 'totara_program', option.name)
    "
    @click="$emit('click')"
  >
    <div class="tui-totara_program-enrolment-option-card_item">
      <div class="tui-totara_program-enrolment-option-card_item-container">
        <span class="tui-totara_program-enrolment-option-card_item-label">
          <span
            class="tui-totara_program-enrolment-option-card_item-label__name"
          >
            {{ option.name }}
          </span>
          <span
            class="tui-totara_program-enrolment-option-card_item-label__assignment_type"
          >
            {{ option.type }}
          </span>
        </span>
        <span>
          {{ option.description }}
        </span>
        <span v-if="option.due_date && option.can_due_date_change">
          {{ $str('duedate_a', 'totara_program', option.due_date) }}
        </span>
      </div>
      <slot />
    </div>
  </Card>
</template>

<script>
import Card from 'tui/components/card/Card';

export default {
  components: { Card },

  props: {
    clickable: {
      type: Boolean,
      default: () => false,
    },
    option: {
      type: Object,
      default: () => {},
    },
  },

  emits: ['click'],
};
</script>

<style scoped lang="scss">
.tui-totara_program-enrolment-option-card {
  &_item {
    display: grid;
    grid-template-columns: 5fr auto;
    gap: var(--gap-4);
    align-items: baseline;
    width: 100%;
    margin: var(--gap-1);
    padding: var(--gap-2);
    background-color: #f9f9f9;
    border-radius: var(--border-radius-normal);

    &-container {
      display: flex;
      flex-direction: column;
      gap: var(--gap-2);
    }

    &-label {
      display: flex;
      gap: var(--gap-2);
      align-items: baseline;

      &__name {
        font-weight: 501;
        font-size: font-size-px(16);
        line-height: line-height-px(24);
      }

      &__assignment_type {
        @include font(body-md);
      }
    }

    button {
      max-height: var(--gap-4);

      &:hover {
        box-shadow: unset;
      }
    }
  }
}
</style>
