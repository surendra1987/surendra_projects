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
  <div class="tui-performGoalLinkedReviewChangeStatus">
    <div class="tui-performGoalLinkedReviewChangeStatus__currentStatus">
      <div
        class="tui-performGoalLinkedReviewChangeStatus__currentStatus-message"
      >
        <div v-if="!content.can_change_status">
          {{ $str('goal_progress_update_no_permission', 'perform_goal') }}
        </div>
        <div v-else>
          {{ $str('goal_awaiting_progress_update', 'perform_goal') }}
        </div>
      </div>
      <div
        class="tui-performGoalLinkedReviewChangeStatus__currentStatus-progress"
      >
        {{
          $str('goal_progress_current', 'perform_goal', {
            current: parseFloat(goal.current_value),
            target: parseFloat(goal.target_value),
            status: goal.status.label,
          })
        }}
      </div>
    </div>

    <div
      v-if="!fromPrint && content.can_change_status"
      class="tui-performGoalLinkedReviewChangeStatus__updateStatus"
    >
      <Button
        :disabled="!canUpdate"
        :styleclass="{
          small: true,
        }"
        :text="$str('goal_update_progress', 'perform_goal')"
        @click="openStatusModal"
      />
    </div>

    <ModalPresenter :open="showStatusModal" @request-close="hideStatusModal">
      <GoalActionModal
        :id="goal.id"
        action="update"
        :participant-instance-id="participantInstanceId"
        :section-element-id="sectionElementId"
        :subject-id="participantInstanceId"
        @update="handleStatusSubmitted"
        @request-close="hideStatusModal"
      />
    </ModalPresenter>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import GoalActionModal from 'perform_goal/components/view/PerformGoalActionModal';
import ModalPresenter from 'tui/components/modal/ModalPresenter';

export default {
  components: {
    Button,
    GoalActionModal,
    ModalPresenter,
  },

  props: {
    canUpdate: { type: Boolean, default: true },
    content: { type: Object, required: true },
    fromPrint: { type: Boolean },
    participantInstanceId: { type: [String, Number] },
    sectionElementId: { type: [String, Number] },
  },

  emits: ['updated'],

  data() {
    return {
      // Goal data
      goal: this.content.goal,
      // Should we be showing the update status modal?
      showStatusModal: false,
    };
  },

  methods: {
    /**
     * Handle update from goal status/progress change
     *
     * @param {Object} result the goal response data returned from the update query
     */
    handleStatusSubmitted(result) {
      this.$emit('updated', result.data);
      this.hideStatusModal();
    },

    /**
     * Hide the modal for updating this goal status.
     */
    hideStatusModal() {
      this.showStatusModal = false;
    },

    /**
     * Open the modal for updating this goal status.
     */
    openStatusModal() {
      this.showStatusModal = true;
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalLinkedReviewChangeStatus {
  display: flex;
  flex-direction: column;
  gap: var(--gap-4);

  &__currentStatus {
    display: flex;
    flex-direction: column;
    gap: var(--gap-1);

    &-message {
      font-style: italic;
    }

    &-progress {
      @include font(body-sm);
      color: var(--color-neutral-6);
    }
  }
}

@media (min-width: $tui-screen-sm) {
  .tui-performGoalLinkedReviewChangeStatus {
    flex-direction: row;
    gap: var(--gap-8);
  }
}
</style>
