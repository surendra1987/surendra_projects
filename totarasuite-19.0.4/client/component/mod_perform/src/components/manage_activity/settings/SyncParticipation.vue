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

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @module totara_perform
-->

<template>
  <ActivitySetting
    class="tui-performPASettingSyncParticipation"
    :title="
      $str(
        'perform_admin_sync_participant_instance_override_heading',
        'mod_perform'
      )
    "
  >
    <template v-slot:description>
      {{
        $str(
          'perform_sync_participant_instance_closure_admin_description',
          'mod_perform'
        )
      }}
    </template>

    <template v-slot:heading-side>
      <ToggleSwitch
        v-model:value="override"
        :aria-label="
          $str(
            'perform_admin_sync_participant_instance_override_heading',
            'mod_perform'
          )
        "
        :disabled="isSaving.override"
        :toggle-first="true"
        @input="updateSetting('override')"
      />
    </template>

    <template v-slot:form>
      <FormRowStack spacing="large">
        <!-- Auto assign -->
        <FormRow
          content-type="other"
          :label="
            $str(
              'perform_admin_sync_participant_instance_role_change_create',
              'mod_perform'
            )
          "
          :helpmsg="
            $str(
              'perform_admin_sync_participant_instance_role_change_create_description',
              'mod_perform'
            )
          "
        >
          <template v-slot:default="{ id }">
            <Select
              :id="id"
              v-model:value="autoAssign"
              :aria-label="
                $str(
                  'perform_admin_sync_participant_instance_role_change_create',
                  'mod_perform'
                )
              "
              :disabled="!override || isSaving.assign"
              :options="data.sync_participant_instance_creation_options"
              @input="updateSetting('assign')"
            />
          </template>
        </FormRow>

        <!-- Auto close -->
        <FormRow
          content-type="other"
          :label="
            $str(
              'perform_admin_sync_participant_instance_role_change_close',
              'mod_perform'
            )
          "
        >
          <template v-slot:help-message>
            <div
              class="tui-performPASettingSyncParticipation__removedHelpSetting"
            >
              <div
                v-for="option in data.sync_participant_instance_closure_options"
                :key="option.id"
                class="tui-performPASettingSyncParticipation__removedHelpSettingDetail"
              >
                <h4
                  class="tui-performPASettingSyncParticipation__removedHelpSettingDetail-heading"
                >
                  {{ option.label }}
                </h4>
                {{ option.desc }}
              </div>
            </div>
          </template>

          <template v-slot:default="{ id }">
            <Select
              :id="id"
              v-model:value="autoClose"
              :aria-label="
                $str(
                  'perform_admin_sync_participant_instance_role_change_close',
                  'mod_perform'
                )
              "
              :disabled="!override || isSaving.close"
              :options="data.sync_participant_instance_closure_options"
              @input="updateSetting('close')"
            />
          </template>
        </FormRow>
      </FormRowStack>
    </template>
  </ActivitySetting>
</template>

<script>
// Imports
import ActivitySetting from 'mod_perform/components/manage_activity/ActivitySetting';
import FormRow from 'tui/components/form/FormRow';
import FormRowStack from 'tui/components/form/FormRowStack';
import { notify } from 'tui/notifications';
import Select from 'tui/components/form/Select';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';

//GraphQL
import OverrideGlobalParticipationSettings from 'mod_perform/graphql/override_global_participation_settings';

export default {
  components: {
    ActivitySetting,
    FormRow,
    FormRowStack,
    Select,
    ToggleSwitch,
  },

  props: {
    activityId: { required: true, type: [Number, String] },
    data: { required: true, type: Object },
  },

  emits: ['updated'],

  data() {
    return {
      // Auto assign toggle value
      autoAssign: this.data.sync_participant_instance_creation_type,
      // Auto close toggle value
      autoClose: this.data.sync_participant_instance_closure_type,
      // Toggle being saved
      isSaving: {
        assign: false,
        close: false,
        override: false,
      },
      // Override toggle value
      override: this.data.override_global_participation_settings,
    };
  },

  watch: {
    data: {
      deep: true,
      handler(value) {
        this.autoAssign = value.sync_participant_instance_creation_type;
        this.autoClose = value.sync_participant_instance_closure_type;
        this.override = value.override_global_participation_settings;
      },
    },
  },

  methods: {
    /**
     * Clear the saving values
     *
     */
    clearSaving() {
      this.isSaving = {
        assign: false,
        close: false,
        override: false,
      };
    },

    /**
     * Saves the sync participation settings to the backend
     *
     * @param {String} toggle which toggle was clicked
     */
    async updateSetting(toggle) {
      this.isSaving = {
        assign: toggle == 'assign',
        close: toggle == 'close',
        override: toggle == 'override',
      };

      try {
        const { data: data } = await this.$apollo.mutate({
          mutation: OverrideGlobalParticipationSettings,
          variables: {
            input: {
              activity_id: this.activityId,
              override_global_participation_settings: this.override,
              sync_participant_instance_creation_type: +this.autoAssign,
              sync_participant_instance_closure_type: this.autoClose,
            },
          },
          refetchAll: false,
        });
        const result = data.mod_perform_override_global_participation_settings;

        if (result) {
          this.$emit('updated');
        }

        this.clearSaving();
      } catch (e) {
        this.clearSaving();
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
.tui-performPASettingSyncParticipation {
  &__removedHelpSetting {
    display: flex;
    flex-direction: column;
    gap: var(--gap-4);
  }

  &__removedHelpSettingDetail {
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
}
</style>
