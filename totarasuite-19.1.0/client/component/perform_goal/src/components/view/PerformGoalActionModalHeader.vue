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
  <div class="tui-performGoalActionModalHeader">
    <div class="tui-performGoalActionModalHeader__left">
      <div class="tui-performGoalActionModalHeader__title">
        <span class="tui-performGoalActionModalHeader__title-icon">
          <GoalIcon aria-hidden="true" :size="400" />
        </span>
        <span>
          {{ title }}
        </span>
      </div>

      <div class="tui-performGoalActionModalHeader__status">
        <Lozenge v-if="status" :text="status" :type="'neutral'" />
      </div>
    </div>
    <div class="tui-performGoalActionModalHeader__side">
      <Actions
        v-if="hasActions && canManage"
        class="tui-performGoalActionModalHeader__side-actions"
        :can-update-status="canUpdateStatus"
        :goal-id="goalId"
        @goal-delete="$emit('goalDelete', $event)"
        @goal-edit="$emit('goalEdit', $event)"
        @goal-update="$emit('goalUpdate', $event)"
      />

      <div class="tui-performGoalActionModalHeader__side-close">
        <CloseButton :size="300" :small-btn="true" @click="$emit('close')" />
      </div>
    </div>
  </div>
</template>

<script>
import Actions from 'perform_goal/components/view/GoalActionsMenu';
import CloseButton from 'tui/components/buttons/CloseIcon';
import GoalIcon from 'tui/components/icons/Goal';
import Lozenge from 'tui/components/lozenge/Lozenge';

export default {
  components: {
    Actions,
    CloseButton,
    GoalIcon,
    Lozenge,
  },

  props: {
    // Can manage goal
    canManage: { type: Boolean },
    // Can update goal status
    canUpdateStatus: { type: Boolean },
    // Goal ID for actions
    goalId: { type: [String, Number] },
    // Show Actions
    showActions: { type: Boolean },
    // Current status of goal
    status: { type: String },
    // Title of modal
    title: { type: String, required: true },
  },

  emits: ['goalDelete', 'goalEdit', 'goalUpdate', 'close'],

  computed: {
    /**
     * Does the user currently have any goals
     */
    hasActions() {
      return this.canManage && this.goalId && this.showActions;
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalActionModalHeader {
  display: flex;
  flex-grow: 1;
  gap: var(--gap-4);
  justify-content: space-between;

  &__title {
    @include font(h4);
    display: flex;
    gap: var(--gap-2);

    &-icon {
      position: relative;
      top: -1px;
      display: flex;
      align-items: center;
      align-self: flex-start;
      color: var(--color-primary);
    }
  }

  &__status {
    display: inline-flex;
  }

  &__left {
    display: flex;
    flex-grow: 1;
    flex-wrap: wrap;
    gap: var(--gap-4);
    align-items: center;
    justify-content: space-between;
  }

  &__side {
    position: relative;
    right: calc(var(--gap-2) * -1);
    display: flex;
    gap: var(--gap-4);
    align-items: center;
    align-self: flex-start;
  }
}
</style>
