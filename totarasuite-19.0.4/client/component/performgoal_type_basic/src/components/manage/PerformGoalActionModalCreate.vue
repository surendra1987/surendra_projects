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
    :title="$str('goal_create_modal_title', 'perform_goal')"
    :title-id="titleId"
    :title-visible="false"
  >
    <template v-slot:custom-title>
      <GoalModalHeader
        :title="$str('goal_create_modal_title', 'perform_goal')"
        @close="$emit('close')"
      />
    </template>

    <!-- Goal creation -->
    <GoalForm ref="create" @submit="handleCreation" />

    <template v-slot:buttons>
      <ButtonGroup>
        <Button
          :disabled="submitting && submitShow"
          :loading="submitting && !submitShow"
          variant="stealth"
          :text="$str('goal_create_modal_create_and_close', 'perform_goal')"
          @click="$refs.create.$refs.form.submit()"
        />

        <Button
          :disabled="submitting && !submitShow"
          :loading="submitting && submitShow"
          variant="primary"
          :text="$str('goal_create_modal_create_and_view', 'perform_goal')"
          @click="createThenShow"
        />
      </ButtonGroup>
    </template>
  </ModalContent>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import GoalForm from 'perform_goal/components/manage/PerformGoalForm';
import GoalModalHeader from 'perform_goal/components/view/PerformGoalActionModalHeader';
import ModalContent from 'tui/components/modal/ModalContent';

// Util
import { notify } from 'tui/notifications';

// GraphQL
import CreateGoalQuery from 'perform_goal/graphql/create_goal';

export default {
  components: {
    Button,
    ButtonGroup,
    GoalForm,
    GoalModalHeader,
    ModalContent,
  },

  props: {
    // Subject ID we are creating a personal goal for
    subjectId: { type: [String, Number] },
    // Title ID so the modal can reference it
    titleId: { type: String, required: true },
  },

  emits: ['close', 'request-close', 'submitShow', 'submitted'],

  data() {
    return {
      submitShow: false,
      submitting: false,
    };
  },

  mounted() {
    window.document.title = this.$str(
      'goal_create_modal_title',
      'perform_goal'
    );
  },

  methods: {
    /**
     * Close this modal
     */
    closeModal() {
      this.$emit('request-close');
    },

    /**
     * Create then show goal
     */
    createThenShow() {
      this.submitShow = true;
      this.$refs.create.$refs.form.submit();
    },

    /**
     * Handle the submission of the edit form
     *
     * @param {Object} values Form values.
     */
    async handleCreation(values) {
      try {
        this.submitting = true;
        const result = await this.$apollo.mutate({
          mutation: CreateGoalQuery,
          variables: {
            input: {
              description: values.description
                ? values.description.getContent()
                : null,
              name: values.name,
              start_date: values.dates.start_date.iso,
              target_date: values.dates.target_date.iso,
              target_value: parseFloat(values.target_value),
              user: {
                id: this.subjectId,
              },
            },
          },
        });

        if (this.submitShow) {
          this.$emit('submitShow', result.data.perform_goal_create_result);
        } else {
          this.$emit('submitted', result.data.perform_goal_create_result);
        }
      } catch (e) {
        // Error notification
        notify({
          message: this.$str('goal_create_modal_error', 'perform_goal'),
          type: 'error',
        });
      } finally {
        this.submitShow = false;
        this.submitting = false;
      }
    },
  },
};
</script>
