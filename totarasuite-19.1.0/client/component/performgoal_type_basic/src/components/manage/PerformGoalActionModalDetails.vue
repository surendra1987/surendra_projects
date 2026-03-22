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
  @package performgoal_type_basic
-->

<template>
  <ModalContent
    class="tui-performGoalActionModalDetails"
    :title="$str('goal_details_modal_title', 'perform_goal')"
    :title-id="titleId"
    :title-visible="false"
  >
    <template v-slot:custom-title>
      <GoalModalHeader
        :can-manage="canManage"
        :can-update-status="canUpdateStatus"
        :goal-id="id"
        :show-actions="showActions && hasGoal"
        :status="hasGoal ? goalResponse.goal.status.label : null"
        :title="$str('goal_details_modal_title', 'perform_goal')"
        @close="$emit('close')"
        @goal-delete="$emit('showDelete', $event)"
        @goal-edit="$emit('showEdit', $event)"
        @goal-update="$emit('showUpdate', $event)"
      />
    </template>

    <GoalDetails
      v-if="hasGoal"
      :goal-response="goalResponse"
      @content-update="$emit('content-update')"
    />

    <div v-else-if="!loading">
      <NotificationBanner
        :message="$str('goal_cannot_be_displayed', 'perform_goal')"
        type="warning"
      />
    </div>

    <template v-if="hasGoal" v-slot:footer-content>
      <div class="tui-performGoalActionModalDetails__updated">
        {{
          $str(
            'goal_last_updated',
            'perform_goal',
            goalResponse.goal.updated_at
          )
        }}
      </div>
    </template>
  </ModalContent>
</template>

<script>
import GoalDetails from 'perform_goal/components/view/PerformGoalDetails';
import GoalModalHeader from 'perform_goal/components/view/PerformGoalActionModalHeader';
import ModalContent from 'tui/components/modal/ModalContent';
import NotificationBanner from 'tui/components/notifications/NotificationBanner';

// GraphQL
import ViewGoalQuery from 'perform_goal/graphql/view_goal';

export default {
  components: {
    GoalDetails,
    GoalModalHeader,
    ModalContent,
    NotificationBanner,
  },

  props: {
    // The ID for this goal
    id: { type: [Number, String], required: true },
    // Title ID so the modal can reference it
    titleId: { type: String, required: true },
    // Show actions menu
    showActions: { type: Boolean, default: true },
  },

  emits: ['close', 'showDelete', 'showEdit', 'showUpdate', 'content-update'],

  data() {
    return {
      // Goal response data for form
      goalResponse: {},
      loading: true,
      submitShow: false,
      submitting: false,
    };
  },

  computed: {
    /**
     * Does this user have permission to manage this goal
     */
    canManage() {
      return this.hasGoal ? this.goalResponse.permissions.can_manage : false;
    },

    /**
     * Does this user have permission to update the status of this goal
     */
    canUpdateStatus() {
      return this.hasGoal
        ? this.goalResponse.permissions.can_update_status
        : false;
    },

    /**
     * Has the goal been found
     */
    hasGoal() {
      return this.goalResponse.goal != null;
    },
  },

  /**
   * Fetch the latest goal data
   *
   */
  apollo: {
    goalResponse: {
      query: ViewGoalQuery,
      fetchPolicy: 'no-cache',
      variables() {
        return {
          goal_reference: {
            id: parseInt(this.id),
          },
        };
      },
      update({ perform_goal_view_goal: goal }) {
        this.loading = false;
        return goal;
      },
      error() {
        this.loading = false;
      },
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalActionModalDetails {
  &__updated {
    @include font(body-sm);
    color: var(--color-neutral-6);
  }
}
</style>
