<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @module totara_perform
-->

<template>
  <!-- Section and activity closure conditions -->
  <div class="tui-performPASettingClosure">
    <h2 class="tui-performPASettingClosure__heading">
      {{ $str('manage_setting_heading_closure', 'mod_perform') }}
    </h2>

    <FormRowStack spacing="large">
      <!-- Section closure -->
      <FormRow :label="$str('manage_closure_section_closure', 'mod_perform')">
        <div class="tui-performPASettingClosure__toggle">
          <ToggleSwitch
            v-model:value="settingValues.close_on_section_submission"
            :disabled="saving"
            :text="$str('manage_closure_section_closure_option', 'mod_perform')"
            :toggle-first="true"
            @input="sectionClosureChange"
          />
        </div>
      </FormRow>

      <!-- Section closure state warning -->
      <FormRow v-if="showVisibilityStatusMismatchWarning">
        <div class="tui-performPASettingClosure__warning">
          <NotificationBanner
            :message="
              $str('automatic_closure_status_mismatch_warning', 'mod_perform')
            "
            type="warning"
          />
        </div>
      </FormRow>

      <!-- Activity closure -->
      <FormRow :label="$str('manage_closure_activity_closure', 'mod_perform')">
        <template v-slot:help-message>
          <div class="tui-performPASettingClosure__helpSetting">
            <div
              v-for="option in data.checkbox_options"
              :key="option.id"
              class="tui-performPASettingClosure__helpSettingDetail"
            >
              <h3
                class="tui-performPASettingClosure__helpSettingDetail-heading"
              >
                {{ option.label }}
              </h3>
              {{ option.desc }}
            </div>
          </div>
        </template>

        <CheckboxGroup>
          <Checkbox
            v-for="option in data.checkbox_options"
            :key="option.id"
            v-model:checked="settingValues[option.id]"
            :disabled="
              (option.id === 'close_on_completion' &&
                !onCompletionChangeable) ||
                (option.id === 'close_on_due_date' && !dueDateChangeable) ||
                saving
            "
            :name="$id('option')"
            :disabled-readable="true"
            @update:checked="activityClosureChange($event, option.id)"
          >
            {{ option.label }}
          </Checkbox>
        </CheckboxGroup>
      </FormRow>
    </FormRowStack>

    <ConfirmationModal
      :open="sectionChangeModalOpen"
      :title="
        $str('workflow_automatic_closure_confirmation_title', 'mod_perform')
      "
      :confirm-button-text="$str('modal_confirm', 'mod_perform')"
      @confirm="sectionChangeModalConfirmed"
      @cancel="sectionChangeModalCancelled"
    >
      {{
        $str(
          settingValues.close_on_section_submission
            ? 'workflow_automatic_closure_enabled_confirmation_text'
            : 'workflow_automatic_closure_disabled_confirmation_text',
          'mod_perform'
        )
      }}
    </ConfirmationModal>

    <ConfirmationModal
      :open="activityChangeModalOpen"
      :title="
        $str('workflow_due_date_closure_confirmation_title', 'mod_perform')
      "
      :confirm-button-text="$str('modal_confirm', 'mod_perform')"
      @confirm="activityChangeModalConfirmed"
      @cancel="activityChangeModalCancelled"
    >
      {{ activityConfirmationMessage }}
    </ConfirmationModal>
  </div>
</template>

<script>
import Checkbox from 'tui/components/form/Checkbox';
import CheckboxGroup from 'tui/components/form/CheckboxGroup';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import FormRow from 'tui/components/form/FormRow';
import FormRowStack from 'tui/components/form/FormRowStack';
import NotificationBanner from 'tui/components/notifications/NotificationBanner';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
// Util
import { notify } from 'tui/notifications';

// Queries
import updateClosureSetting from 'mod_perform/graphql/update_activity_closure_settings';

export default {
  components: {
    Checkbox,
    CheckboxGroup,
    ConfirmationModal,
    FormRow,
    FormRowStack,
    NotificationBanner,
    ToggleSwitch,
  },

  props: {
    activityId: { required: true, type: [Number, String] },
    data: { required: true, type: Object },
  },

  emits: ['updated'],

  data() {
    return {
      // Show activity change confirmation modal
      activityChangeModalOpen: false,
      // Message displayed in activity change confirmation modal
      activityConfirmationMessage: null,
      // ID of field changed
      changedField: null,
      // Currently saving the setting
      saving: false,
      // Show section change confirmation modal
      sectionChangeModalOpen: false,
      // Setting values
      settingValues: this.data.value,
    };
  },

  computed: {
    /**
     * Due date activity value can be changed
     *
     */
    dueDateChangeable() {
      return this.data.mutable.close_on_due_date;
    },

    /**
     * Close on completion value can be changed
     */
    onCompletionChangeable() {
      return !this.data.value.close_on_section_submission;
    },

    /**
     * Closure values aren't compatible with visibility setting
     *
     */
    showVisibilityStatusMismatchWarning() {
      return !this.data.valid_closure_state;
    },
  },

  watch: {
    data: {
      deep: true,
      handler(update) {
        this.settingValues = Object.assign({}, update.value);
      },
    },
  },

  methods: {
    /**
     * Handle the cancellation of activity closure change
     *
     */
    activityChangeModalCancelled() {
      this.activityChangeModalOpen = false;
      this.activityConfirmationMessage = null;

      this.settingValues[this.changedField] = !this.settingValues[
        this.changedField
      ];
    },

    /**
     * Handle the confirmation of activity closure change
     *
     */
    activityChangeModalConfirmed() {
      this.activityChangeModalOpen = false;
      this.activityConfirmationMessage = null;
      this.updateSetting();
    },

    /**
     * Handle the update of activity closure values
     *
     * @param {Boolean} value checkbox checked value
     * @param {String} id field identifier
     */
    activityClosureChange(value, id) {
      // Show confirmation if activity active
      if (!this.data.draft) {
        this.changedField = id;

        // show due data confirmation
        if (id === 'close_on_completion') {
          this.activityConfirmationMessage = this.$str(
            value
              ? 'workflow_automatic_closure_enabled_confirmation_text'
              : 'workflow_automatic_closure_disabled_confirmation_text',
            'mod_perform'
          );

          this.activityChangeModalOpen = true;
        } else if (id === 'close_on_due_date') {
          this.activityConfirmationMessage = this.$str(
            value
              ? 'workflow_due_date_closure_enabled_confirmation_text'
              : 'workflow_due_date_closure_disabled_confirmation_text',
            'mod_perform'
          );

          this.activityChangeModalOpen = true;
        } else if (id === 'manual_close') {
          this.activityConfirmationMessage = this.$str(
            value
              ? 'workflow_manual_closure_enabled_confirmation_text'
              : 'workflow_manual_closure_disabled_confirmation_text',
            'mod_perform'
          );

          this.activityChangeModalOpen = true;
        } else {
          // No confirmation message to show so update setting
          this.updateSetting();
        }
      } else {
        this.updateSetting();
      }
    },

    /**
     * Handle the cancellation of section closure change
     *
     */
    sectionChangeModalCancelled() {
      this.sectionChangeModalOpen = false;
      this.settingValues.close_on_section_submission = !this.settingValues
        .close_on_section_submission;
    },

    /**
     * Handle the confirmation of section closure change
     *
     */
    sectionChangeModalConfirmed() {
      this.sectionChangeModalOpen = false;
      this.updateSetting();
    },

    /**
     * Handle the update of the section closure value
     *
     */
    sectionClosureChange() {
      // Show confirmation if activity active
      if (!this.data.draft) {
        this.sectionChangeModalOpen = true;
      } else {
        // Turn close on completion on whenever close on section submission is on
        if (
          Object.keys(this.settingValues).includes('close_on_completion') &&
          this.settingValues.close_on_section_submission
        ) {
          this.settingValues.close_on_completion = true;
        }

        this.updateSetting();
      }
    },

    /**
     * Handle the submission of setting value changes
     *
     */
    async updateSetting() {
      try {
        this.saving = true;
        const result = await this.$apollo.mutate({
          mutation: updateClosureSetting,
          variables: {
            input: {
              activity_id: this.activityId,
              close_on_section_submission: this.settingValues
                .close_on_section_submission,
              close_on_completion: this.settingValues.close_on_completion,
              close_on_due_date: this.settingValues.close_on_due_date,
              manual_close: this.settingValues.manual_close,
            },
          },
        });

        this.saving = false;

        if (result) {
          this.$emit('updated');
        }
      } catch (e) {
        this.saving = false;
        // Error notification
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
.tui-performPASettingClosure {
  display: flex;
  flex-direction: column;
  gap: var(--gap-4);

  &__heading {
    @include font(h4);
    margin: 0;
  }
  &__helpSetting {
    display: flex;
    flex-direction: column;
    gap: var(--gap-4);
  }

  &__helpSettingDetail {
    @include tui-font-body-x-small();
    display: flex;
    flex-direction: column;
    gap: var(--gap-1);
    color: var(--color-neutral-6);

    &-heading {
      @include tui-font-body();
      margin: 0;
      color: var(--color-neutral-7);
    }
  }

  &__toggle {
    padding-top: tui-input-toggle-v-padding();
  }

  &__warning {
    max-width: 712px;
  }
}
</style>
