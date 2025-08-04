<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Mark Metcalfe <mark.metcalfe@totaralearning.com>
  @module mod_perform
  @deprecated since Totara 19.0
-->

<template>
  <div class="tui-performActivityWorkflowSettings">
    <h2 class="tui-performActivityWorkflowSettings__heading">
      {{ $str('workflow_settings', 'mod_perform') }}
    </h2>
    <Form>
      <FormRow
        :label="$str('workflow_automatic_closure_label', 'mod_perform')"
        :helpmsg="$str('workflow_automatic_closure_label_help', 'mod_perform')"
      >
        <ToggleSwitch
          v-model:value="closeOnCompletion"
          :aria-describedby="$id('on-completion')"
          :disabled="completionIsSaving"
          :toggle-first="true"
          :text="
            $str('workflow_automatic_closure_on_completion', 'mod_perform')
          "
          @input="closeOnCompletionChanged"
        />

        <FormRowDetails :id="$id('on-completion')">
          {{
            $str('workflow_automatic_closure_on_completion_help', 'mod_perform')
          }}
        </FormRowDetails>
      </FormRow>

      <FormRow>
        <div class="tui-performActivityWorkflowSettings__warning">
          <NotificationBanner
            v-if="showVisibilityStatusMismatchWarning"
            type="warning"
            :message="
              $str('automatic_closure_status_mismatch_warning', 'mod_perform')
            "
          />
        </div>
      </FormRow>

      <FormRow>
        <ToggleSwitch
          v-model:value="closeOnDueDate"
          :aria-describedby="$id('on-due-date')"
          :disabled="dueDateIsSaving || !dueDateIsEnabled"
          :toggle-first="true"
          :text="
            dueDateIsEnabled
              ? $str('workflow_automatic_closure_on_due_date', 'mod_perform')
              : $str(
                  'workflow_automatic_closure_on_due_date_no_due_date',
                  'mod_perform'
                )
          "
          @input="closeOnDueDateChanged"
        />

        <FormRowDetails :id="$id('on-completion')">
          <p>
            {{
              $str('workflow_automatic_closure_on_due_date_help', 'mod_perform')
            }}
          </p>
          <template v-if="!dueDateIsEnabled">
            <p>
              {{
                $str(
                  'workflow_automatic_closure_on_due_date_help_no_due_date',
                  'mod_perform'
                )
              }}
            </p>
          </template>
        </FormRowDetails>
      </FormRow>
    </Form>

    <ConfirmationModal
      :open="closeOnCompletionModalOpen"
      :title="
        $str('workflow_automatic_closure_confirmation_title', 'mod_perform')
      "
      :confirm-button-text="$str('modal_confirm', 'mod_perform')"
      @confirm="closeOnCompletionModalConfirmed"
      @cancel="closeOnCompletionModalCancelled"
    >
      {{
        $str(
          closeOnCompletion
            ? 'workflow_automatic_closure_enabled_confirmation_text'
            : 'workflow_automatic_closure_disabled_confirmation_text',
          'mod_perform'
        )
      }}
    </ConfirmationModal>

    <ConfirmationModal
      :open="closeOnDueDateModalOpen"
      :title="
        $str('workflow_due_date_closure_confirmation_title', 'mod_perform')
      "
      :confirm-button-text="$str('modal_confirm', 'mod_perform')"
      @confirm="closeOnDueDateModalConfirmed"
      @cancel="closeOnDueDateModalCancelled"
    >
      {{
        $str(
          closeOnDueDate
            ? 'workflow_due_date_closure_enabled_confirmation_text'
            : 'workflow_due_date_closure_disabled_confirmation_text',
          'mod_perform'
        )
      }}
    </ConfirmationModal>
  </div>
</template>

<script>
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import Form from 'tui/components/form/Form';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import NotificationBanner from 'tui/components/notifications/NotificationBanner';

import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
// Util
import { notify } from 'tui/notifications';
import {
  ACTIVITY_STATUS_DRAFT,
  VISIBILITY_CONDITION_NONE,
} from 'mod_perform/constants';
// Queries
import updateActivityWorkflowSettingsMutation from 'mod_perform/graphql/update_activity_workflow_settings';

export default {
  components: {
    ConfirmationModal,
    Form,
    FormRow,
    FormRowDetails,
    NotificationBanner,
    ToggleSwitch,
  },

  props: {
    activity: {
      type: Object,
      required: true,
    },
  },

  emits: ['change'],

  data() {
    return {
      closeOnCompletion: this.activity.settings.close_on_completion,
      closeOnDueDate: this.activity.settings.close_on_due_date,
      closeOnCompletionModalOpen: false,
      closeOnDueDateModalOpen: false,
      completionIsSaving: false,
      dueDateIsSaving: false,
    };
  },

  computed: {
    dueDateIsEnabled() {
      return this.activity.default_track.due_date_is_enabled;
    },
    isDraft() {
      return this.activity.state_details.name === ACTIVITY_STATUS_DRAFT;
    },
    showVisibilityStatusMismatchWarning() {
      return (
        this.activity.settings.visibility_condition != null &&
        this.activity.settings.visibility_condition.value !==
          VISIBILITY_CONDITION_NONE &&
        !this.closeOnCompletion
      );
    },
  },

  watch: {
    dueDateIsEnabled(value) {
      if (!value) {
        this.closeOnDueDate = false;
        this.closeOnDueDateChanged();
      }
    },
  },

  methods: {
    closeOnCompletionChanged() {
      if (this.isDraft) {
        this.completionIsSaving = true;
        this.save().then(() => (this.completionIsSaving = false));
      } else {
        this.closeOnCompletionModalOpen = true;
      }
    },
    closeOnCompletionModalCancelled() {
      this.closeOnCompletionModalOpen = false;
      this.closeOnCompletion = !this.closeOnCompletion;
    },
    closeOnCompletionModalConfirmed() {
      this.closeOnCompletionModalOpen = false;
      this.completionIsSaving = true;
      this.save().then(() => (this.completionIsSaving = false));
    },
    closeOnDueDateChanged() {
      if (this.isDraft || !this.dueDateIsEnabled) {
        this.dueDateIsSaving = true;
        this.save().then(() => (this.dueDateIsSaving = false));
      } else {
        this.closeOnDueDateModalOpen = true;
      }
    },
    closeOnDueDateModalCancelled() {
      this.closeOnDueDateModalOpen = false;
      this.closeOnDueDate = !this.closeOnDueDate;
    },
    closeOnDueDateModalConfirmed() {
      this.closeOnDueDateModalOpen = false;
      this.dueDateIsSaving = true;
      this.save().then(() => (this.dueDateIsSaving = false));
    },
    async save() {
      try {
        await this.$apollo.mutate({
          mutation: updateActivityWorkflowSettingsMutation,
          variables: {
            input: {
              activity_id: this.activity.id,
              close_on_completion: this.closeOnCompletion,
              close_on_due_date: this.closeOnDueDate,
            },
          },
          // Prevents 4 additional queries from executing unnecessarily, instead we patch in the relevant data.
          refetchAll: false,
        });

        notify({
          message: this.$str('toast_success_activity_update', 'mod_perform'),
          type: 'success',
        });

        const updatedActivity = Object.assign({}, this.activity);
        updatedActivity.settings = Object.assign({}, this.activity.settings);

        updatedActivity.settings.close_on_due_date = this.closeOnDueDate;
        updatedActivity.settings.close_on_completion = this.closeOnCompletion;

        this.$emit('change', updatedActivity);
      } catch (e) {
        notify({
          message: this.$str('toast_error_generic_update', 'mod_perform'),
          type: 'error',
        });
      }
    },
  },
};
</script>

<style lang="scss">
.tui-performActivityWorkflowSettings {
  &__heading {
    @include font(h3);
    margin: 0;
  }
  & > * + * {
    margin: var(--gap-8) 0 0;
  }

  &__warning {
    max-width: 712px;
  }
}
</style>
