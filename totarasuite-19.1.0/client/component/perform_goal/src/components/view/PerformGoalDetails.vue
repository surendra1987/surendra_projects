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
  @package perform_goal
-->

<template>
  <div class="tui-performGoalDetailsView">
    <!-- Name -->
    <h2 class="tui-performGoalDetailsView__name">
      {{ goalResponse.goal.name }}
    </h2>

    <!-- Description -->
    <div
      class="tui-performGoalDetailsView__description"
      v-html="goalResponse.goal.description"
    />

    <!-- Progress -->
    <Card class="tui-performGoalDetailsView__progress" :no-border="true">
      <div
        class="tui-performGoalDetailsView__progress-current"
        v-html="
          $str('goal_progress_of_target', 'perform_goal', {
            current: goalResponse.goal.current_value,
            target: goalResponse.goal.target_value,
          })
        "
      />

      <Progress
        class="tui-performGoalDetailsView__progress-bar"
        :aria-label="$str('a11y_goal_overall_progress', 'perform_goal')"
        :max="parseFloat(goalResponse.goal.target_value)"
        :value="parseFloat(goalResponse.goal.current_value)"
      />

      <div class="tui-performGoalDetailsView__progress-date">
        <DueIcon
          :aria-hidden="true"
          class="tui-performGoalDetailsView__progress-dateIcon"
        />

        <span class="sr-only">
          {{ $str('a11y_goal_date_range', 'perform_goal') }}
        </span>

        <span>
          {{
            $str('goal_date_range', 'perform_goal', {
              start: goalResponse.goal.start_date,
              target: goalResponse.goal.target_date,
            })
          }}
        </span>
      </div>
    </Card>
    <ExtraContent
      :goal="goalResponse.goal"
      :permissions="goalResponse.permissions"
      @content-update="$emit('content-update')"
    />
  </div>
</template>

<script>
import Card from 'tui/components/card/Card';
import DueIcon from 'tui/components/icons/Date';
import ExtraContent from 'perform_goal/components/view/PerformGoalDetailsExtra';
import Progress from 'tui/components/progress/Progress';

export default {
  components: {
    Card,
    DueIcon,
    ExtraContent,
    Progress,
  },

  props: {
    // Goal data
    goalResponse: { type: Object, required: true },
  },

  emits: ['content-update'],
};
</script>

<style lang="scss">
.tui-performGoalDetailsView {
  display: flex;
  flex-direction: column;
  gap: var(--gap-6);
  height: calc(100% - var(--gap-4));
  margin-top: var(--gap-4);

  &__name {
    @include font(h3);
    margin: 0;
    overflow-wrap: break-word;
  }

  &__progress {
    display: flex;
    flex-direction: column;
    gap: var(--gap-2);
    padding: var(--gap-4);
    background-color: var(--color-neutral-3);

    &-current {
      position: relative;
      left: 1px;
    }

    &-bar {
      position: relative;
      left: -1px;
    }

    &-date {
      position: relative;
      left: 1px;
      display: flex;
      gap: var(--gap-1);
      @include font(body-sm);
    }

    &-dateIcon {
      color: var(--color-neutral-6);
    }
  }
}
</style>
