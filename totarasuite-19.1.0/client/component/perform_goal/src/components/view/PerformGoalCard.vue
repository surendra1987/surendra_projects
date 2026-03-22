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
  <div class="tui-performGoalCard" @click="goalClicked">
    <div class="tui-performGoalCard__details">
      <h3 class="tui-performGoalCard__details-name">
        <ButtonAria
          class="tui-performGoalCard__details-nameBtn"
          :aria-label="goal.name"
          @click="goalClicked"
        >
          <div>{{ goal.name }}</div>
        </ButtonAria>
      </h3>
      <div class="tui-performGoalCard__details-bar">
        <div class="tui-performGoalCard__details-due">
          <span>{{ $str('goal_due_date', 'perform_goal') }}</span>
          {{ goal.target_date }}
        </div>

        <!-- Tasks -->
        <div
          v-if="
            goal.goal_tasks_metadata && goal.goal_tasks_metadata.total_count
          "
          class="tui-performGoalCard__details-tasks"
          :title="$str('goal_tasks', 'perform_goal')"
        >
          <TaskIcon
            :aria-hidden="true"
            class="tui-performGoalCard__details-tasksIcon"
          />
          <span class="sr-only">
            {{ $str('a11y_goal_tasks_completed_label', 'perform_goal') }}
          </span>

          <span>
            {{
              $str('goal_tasks_completed_count', 'perform_goal', {
                completed: goal.goal_tasks_metadata.completed_count,
                total: goal.goal_tasks_metadata.total_count,
              })
            }}
          </span>
        </div>

        <!-- Comments -->
        <div
          v-if="goal.comment_count"
          class="tui-performGoalCard__details-comments"
          :title="$str('goal_comments', 'perform_goal')"
        >
          <CommentIcon
            :aria-hidden="true"
            class="tui-performGoalCard__details-commentsIcon"
          />
          <span class="sr-only">
            {{ $str('a11y_goal_comment_label', 'perform_goal') }}
          </span>

          <span>{{ goal.comment_count }}</span>
        </div>
      </div>
    </div>

    <div class="tui-performGoalCard__status">
      <Progress
        class="tui-performGoalCard__status-progress"
        :aria-label="$str('a11y_goal_overall_progress', 'perform_goal')"
        :max="parseFloat(goal.target_value)"
        :value="parseFloat(goal.current_value)"
      />

      <div class="tui-performGoalCard__status-label">
        {{ goal.status.label }}
      </div>
    </div>
  </div>
</template>

<script>
import ButtonAria from 'tui/components/buttons/ButtonAria';
import CommentIcon from 'tui/components/icons/Comment';
import Progress from 'tui/components/progress/Progress';
import TaskIcon from 'tui/components/icons/Task';

export default {
  components: {
    ButtonAria,
    CommentIcon,
    Progress,
    TaskIcon,
  },

  props: {
    // goal details
    goal: { type: Object, required: true },
  },

  emits: ['goalClicked'],

  methods: {
    /**
     * Goal selected
     *
     */
    goalClicked() {
      this.$emit('goalClicked', this.goal.id);
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalCard {
  $block: #{&};

  display: flex;
  flex-direction: column;
  flex-grow: 1;
  gap: var(--gap-4);
  justify-content: space-between;

  &:hover {
    cursor: pointer;
  }

  &__details {
    display: flex;
    flex-direction: column;
    flex-wrap: wrap;
    gap: var(--gap-1);

    &-bar {
      display: flex;
      flex-direction: column;
      gap: var(--gap-2);
      @include font(body-sm);
      color: var(--color-neutral-6);
    }

    &-name {
      @include font(h4);
      margin: 0;
      color: var(--btn-text-color);

      #{$block}:focus & {
        color: var(--color-state-focus);
      }

      #{$block}:hover & {
        color: var(--color-state-hover);
      }

      #{$block}:active &,
      #{$block}:active:hover &,
      #{$block}:active:focus & {
        color: var(--color-state-active);
      }
    }

    &-nameBtn {
      word-break: break-word;

      &:focus-visible {
        @include tui-focus();
      }
    }

    &-comments,
    &-tasks {
      display: flex;
      gap: var(--gap-1);
      align-items: center;
    }

    &-commentsIcon,
    &-tasksIcon {
      font-size: inherit;
    }
  }

  &__status {
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    flex-wrap: wrap;
    gap: var(--gap-1) var(--gap-4);
    align-items: baseline;

    &-label {
      width: rem-px(120);
    }

    &-progress {
      position: relative;
      left: -1px;
      width: rem-px(120);
    }
  }
}

@media screen and (min-width: $tui-screen-sm) {
  .tui-performGoalCard {
    flex-direction: row;

    &__status {
      flex-direction: row;

      &-progress {
        left: 0;
      }
    }

    &__details {
      &-bar {
        flex-direction: row;
        gap: 0;
        @include tui-separator-pipe();

        & > * {
          &:before {
            color: var(--color-neutral-5);
          }
        }
      }
    }
  }
}
</style>
