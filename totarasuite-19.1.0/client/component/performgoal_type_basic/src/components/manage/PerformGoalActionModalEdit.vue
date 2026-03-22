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
    :title="$str('goal_edit_modal_title', 'perform_goal')"
    :title-id="titleId"
    :title-visible="false"
  >
    <template v-slot:custom-title>
      <GoalModalHeader
        :title="$str('goal_edit_modal_title', 'perform_goal')"
        @close="$emit('close')"
      />
    </template>

    <Loader v-if="loading" loading="loading" />

    <GoalForm
      v-else
      ref="edit"
      :goal-data="goalResponse.goal"
      :raw-goal-data="goalResponse.raw"
      @submit="handleSubmit"
    />

    <template v-slot:buttons>
      <Button
        :disabled="submitting"
        :styleclass="{ transparent: true }"
        :text="$str('cancel', 'core')"
        @click="$emit('cancel')"
      />

      <Button
        :disabled="loading"
        :loading="submitting"
        :styleclass="{ primary: true }"
        :text="$str('save', 'totara_core')"
        @click="$refs.edit.$refs.form.submit()"
      />
    </template>
  </ModalContent>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import GoalForm from 'perform_goal/components/manage/PerformGoalForm';
import GoalModalHeader from 'perform_goal/components/view/PerformGoalActionModalHeader';
import Loader from 'tui/components/loading/Loader';
import ModalContent from 'tui/components/modal/ModalContent';

// GraphQL
import UpdateGoalQuery from 'perform_goal/graphql/update_goal';
import ViewGoalQuery from 'perform_goal/graphql/view_goal';

// Util
import { notify } from 'tui/notifications';

export default {
  components: {
    Button,
    GoalForm,
    GoalModalHeader,
    Loader,
    ModalContent,
  },

  props: {
    // The ID for this goal
    id: { type: [Number, String], required: true },
    // Title ID so the modal can reference it
    titleId: { type: String, required: true },
  },

  emits: ['close', 'cancel', 'submitted'],

  data() {
    return {
      // Goal response data for form
      goalResponse: {},
      // Loading current data
      loading: true,
      // Submitting changes
      submitting: false,
    };
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
    },
  },

  methods: {
    /**
     * Handle the submission of the edit form
     *
     * @param {Object} values Form values.
     */
    async handleSubmit(values) {
      try {
        this.submitting = true;
        const result = await this.$apollo.mutate({
          mutation: UpdateGoalQuery,
          variables: {
            goal_reference: {
              id: parseInt(this.id),
            },
            input: {
              description: values.description
                ? values.description.getContent()
                : null,
              name: values.name,
              start_date: values.dates.start_date.iso,
              target_date: values.dates.target_date.iso,
              target_value: parseFloat(values.target_value),
            },
          },
        });

        this.$emit('submitted', result.data.perform_goal_update_result);
      } catch (e) {
        // Error notification
        notify({
          message: this.$str('goal_edit_modal_error', 'perform_goal'),
          type: 'error',
        });
      } finally {
        this.submitting = false;
      }
    },
  },
};
</script>
