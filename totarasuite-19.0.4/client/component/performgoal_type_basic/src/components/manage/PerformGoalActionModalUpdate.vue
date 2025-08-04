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
  @package performgoal_type_basic
-->

<template>
  <ModalContent
    :title="$str('goal_update_progress_modal_title', 'perform_goal')"
    :title-id="titleId"
    :title-visible="false"
  >
    <template v-slot:custom-title>
      <GoalModalHeader
        :title="$str('goal_update_progress_modal_title', 'perform_goal')"
        @close="$emit('close')"
      />
    </template>

    <NotificationBanner
      v-if="fromReviewItem"
      :message="$str('goal_update_progress_modal_message', 'perform_goal')"
      type="info"
    />

    <Loader v-if="loading" loading="loading" />

    <UpdateForm
      v-else
      ref="update"
      :goal-response="goalResponse"
      @submit="handleSubmit"
    />

    <template v-slot:buttons>
      <ButtonGroup>
        <Button
          :disabled="submitting"
          :styleclass="{ transparent: true }"
          :text="$str('cancel', 'core')"
          @click="$emit('cancel')"
        />

        <Button
          :loading="submitting"
          :disabled="loading"
          :styleclass="{ primary: true }"
          :text="$str('goal_form_update', 'perform_goal')"
          @click="$refs.update.$refs.form.submit()"
        />
      </ButtonGroup>
    </template>
  </ModalContent>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import GoalModalHeader from 'perform_goal/components/view/PerformGoalActionModalHeader';
import Loader from 'tui/components/loading/Loader';
import ModalContent from 'tui/components/modal/ModalContent';
import NotificationBanner from 'tui/components/notifications/NotificationBanner';
import UpdateForm from 'perform_goal/components/manage/PerformGoalStatusForm';

// Util
import { notify } from 'tui/notifications';

// GraphQL
import PerformGoalChangeStatus from 'perform_goal/graphql/perform_change_status';
import UpdateGoalProgress from 'perform_goal/graphql/update_progress';
import ViewGoalQuery from 'perform_goal/graphql/view_goal';

export default {
  components: {
    Button,
    ButtonGroup,
    GoalModalHeader,
    Loader,
    ModalContent,
    NotificationBanner,
    UpdateForm,
  },

  props: {
    // The ID for this goal
    id: { type: [Number, String], required: true },
    // The ID of the participant instance for when updating via review item element
    participantInstanceId: { type: [Number, String] },
    // The ID of the section for when updating via review item element
    sectionElementId: { type: [Number, String] },
    // Title ID so the modal can reference it
    titleId: { type: String, required: true },
  },

  emits: ['close', 'cancel', 'request-close', 'submitted'],

  data() {
    return {
      // Goal response data for form
      goalResponse: {},
      // Loading current data
      loading: true,
      // Currently submitting changes
      submitting: false,
    };
  },

  computed: {
    /**
     * If we have section element and participant instance ids
     * then we must be within a review item
     *
     * @return {Boolean}
     */
    fromReviewItem() {
      return this.sectionElementId && this.participantInstanceId;
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
        this.closeModal();
        // Error notification
        notify({
          message: this.$str('goal_form_update_modal_error', 'perform_goal'),
          type: 'error',
        });
        return false;
      },
    },
  },

  methods: {
    /**
     * Close this modal
     */
    closeModal() {
      this.$emit('request-close');
    },

    /**
     * Handle the submission of the update status form
     *
     * @param {Object} values Form values.
     */
    async handleSubmit(values) {
      try {
        this.submitting = true;

        // If from a review item then we call a different mutation
        if (this.fromReviewItem) {
          const result = await this.performGoalChangeStatus({
            current_value: parseFloat(values.progress),
            goal_id: parseInt(this.id),
            participant_instance_id: parseInt(this.participantInstanceId),
            section_element_id: parseInt(this.sectionElementId),
            status: values.status,
          });

          this.$emit(
            'submitted',
            result.data.perform_goal_perform_change_status_result
          );
        } else {
          const result = await this.updateGoalProgress(
            { id: parseInt(this.id) },
            {
              current_value: parseFloat(values.progress),
              status: values.status,
            }
          );

          this.$emit(
            'submitted',
            result.data.perform_goal_update_progress_result
          );
        }
      } catch (e) {
        // Error notification
        notify({
          message: this.$str('goal_form_update_modal_error', 'perform_goal'),
          type: 'error',
        });
      } finally {
        this.submitting = false;
      }
    },

    /**
     * Call the perform_change_status mutation for use when updating via a review item
     *
     * @return {Object}
     */
    performGoalChangeStatus(input) {
      return this.$apollo.mutate({
        mutation: PerformGoalChangeStatus,
        variables: {
          input: input,
        },
      });
    },

    /**
     * Call the update_progress mutation for use when updating via the goal details page
     *
     * @return {Object}
     */
    updateGoalProgress(goalReference, input) {
      return this.$apollo.mutate({
        mutation: UpdateGoalProgress,
        variables: {
          goal_reference: goalReference,
          input: input,
        },
      });
    },
  },
};
</script>
