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
  @module performgoal_type_basic
-->

<template>
  <div class="tui-performGoalLinkedReviewStatusResponse">
    <div class="tui-performGoalLinkedReviewStatusResponse__progress">
      <div class="tui-performGoalLinkedReviewStatusResponse__progress-label">
        {{ $str('goal_progress_label', 'perform_goal') }}
      </div>
      <div class="tui-performGoalLinkedReviewStatusResponse__progress-value">
        <Progress
          class="tui-performGoalLinkedReviewStatusResponse__progress-valueProgress"
          :hide-value="true"
          :max="parseFloat(goal.target_value)"
          :small="true"
          :value="currentValue"
        />
        <div
          class="tui-performGoalLinkedReviewStatusResponse__progress-valueLabel"
        >
          {{
            $str('goal_progress', 'perform_goal', {
              current: currentValue,
              target: parseFloat(goal.target_value),
            })
          }}
        </div>
      </div>
    </div>
    <div class="tui-performGoalLinkedReviewStatusResponse__status">
      <div class="tui-performGoalLinkedReviewStatusResponse__status-label">
        {{ $str('goal_status_label', 'perform_goal') }}
      </div>
      <div class="tui-performGoalLinkedReviewStatusResponse__status-value">
        {{ statusChange.status.label }}
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
    goal: { type: Object, required: true },
    statusChange: { type: Object, required: true },
  },

  computed: {
    /**
     * Get the current progress value. Either from the goal data, or from a recent status change.
     *
     * @return {Number}
     */
    currentValue() {
      return this.statusChange.current_value
        ? parseFloat(this.statusChange.current_value)
        : parseFloat(this.goal.current_value);
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalLinkedReviewStatusResponse {
  display: flex;
  flex-direction: column;
  gap: var(--gap-4);

  &__progress {
    display: flex;
    flex-direction: column;
    flex-shrink: 1;
    gap: var(--gap-1);
    width: 25%;

    &-label {
      font-weight: var(--label-weight);
    }

    &-value {
      display: flex;
      flex-direction: column;
      gap: var(--gap-1);
    }

    &-valueProgress {
      width: rem-px(120);
    }

    &-valueLabel {
      @include font(body-sm);
    }
  }

  &__status {
    display: flex;
    flex-direction: column;

    &-label {
      font-weight: var(--label-weight);
    }
  }
}

@media (min-width: $tui-screen-sm) {
  .tui-performGoalLinkedReviewStatusResponse {
    flex-direction: row;
  }
}
</style>
