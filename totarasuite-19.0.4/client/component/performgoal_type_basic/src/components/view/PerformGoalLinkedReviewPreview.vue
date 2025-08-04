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
  @module performgoal_type_basic
-->

<!-- Used in the performance activity review element -->
<template>
  <div class="tui-performGoalLinkedReviewPreview">
    <!-- Goal status -->
    <div class="tui-performGoalLinkedReviewPreview__status">
      <Progress
        class="tui-performGoalLinkedReviewPreview__status-progress"
        :max="parseFloat(goal.target_value)"
        :small="true"
        :value="parseFloat(goal.current_value)"
      />

      <div class="tui-performGoalLinkedReviewPreview__status-value">
        <span class="sr-only">
          {{ $str('a11y_goal_current_status', 'perform_goal') }}
        </span>
        {{ goal.status.label }}
      </div>
    </div>

    <!-- Goal target date -->
    <div class="tui-performGoalLinkedReviewPreview__group">
      <div class="tui-performGoalLinkedReviewPreview__due">
        <span class="tui-performGoalLinkedReviewPreview__due-label">
          {{ $str('goal_form_label_due_date', 'perform_goal') }}
        </span>
        <span class="tui-performGoalLinkedReviewPreview__due-value">
          {{ goal.target_date }}
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import Progress from 'tui/components/progress/Progress';

export default {
  components: {
    Progress,
  },

  props: {
    // The goal data
    goal: { type: Object, required: true },
  },
};
</script>

<style lang="scss">
.tui-performGoalLinkedReviewPreview {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  gap: var(--gap-4);

  &__status {
    display: flex;
    gap: var(--gap-3);
    align-items: center;
    min-width: 28%;

    &-progress {
      width: rem-px(120);
    }

    &-value {
      padding-left: var(--gap-2);
      font-weight: bold;
    }
  }

  &__due {
    display: flex;
    gap: var(--gap-2);

    &-value {
      font-weight: bold;
    }
  }
}

@media (min-width: $tui-screen-sm) {
  .tui-performGoalLinkedReviewPreview {
    flex-direction: row;

    &__group {
      padding-left: var(--gap-3);
      border-color: var(--color-neutral-5);
      border-style: solid;
      border-width: 0 0 0 var(--border-width-thin);

      .dir-rtl & {
        padding-right: var(--gap-3);
        border-width: 0 var(--border-width-thin) 0 0;
      }
    }
  }
}
</style>
