<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2022 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Rodney Cruden-Powell <rodney.cruden-powell@totaralearning.com>
  @module mod_perform
  @deprecated Since Totara 19.0
-->
<template>
  <ActivitySetting
    class="tui-performAssignmentParticipantSelection"
    :title="
      $str(
        'perform_admin_sync_participant_instance_override_heading',
        'mod_perform'
      )
    "
  >
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
        @input="updateRelationshipParticipation('override')"
      />
    </template>

    <template v-slot:form>
      <Form class="tui-performManageRelationshipParticipation">
        <FormRowStack spacing="large">
          <!-- Auto assign -->
          <FormRow
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
            <ToggleSwitch
              v-model:value="autoAssign"
              :aria-label="
                $str(
                  'perform_admin_sync_participant_instance_role_change_create',
                  'mod_perform'
                )
              "
              :disabled="!override || isSaving.assign"
              :toggle-first="true"
              @input="updateRelationshipParticipation('assign')"
            />
          </FormRow>

          <!-- Auto close -->
          <FormRow
            :label="
              $str(
                'perform_admin_sync_participant_instance_role_change_close',
                'mod_perform'
              )
            "
            :helpmsg="
              $str(
                'perform_admin_sync_participant_instance_role_change_close_description_with_view_only',
                'mod_perform'
              )
            "
          >
            <ToggleSwitch
              v-model:value="autoClose"
              :aria-label="
                $str(
                  'perform_admin_sync_participant_instance_role_change_close',
                  'mod_perform'
                )
              "
              :disabled="!override || isSaving.close"
              :toggle-first="true"
              @input="updateRelationshipParticipation('close')"
            />
          </FormRow>
        </FormRowStack>
      </Form>
    </template>
  </ActivitySetting>
</template>

<script>
// Imports
import ActivitySetting from 'mod_perform/components/manage_activity/ActivitySetting';
import Form from 'tui/components/form/Form';
import FormRow from 'tui/components/form/FormRow';
import FormRowStack from 'tui/components/form/FormRowStack';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';

export default {
  components: {
    ActivitySetting,
    Form,
    FormRow,
    FormRowStack,
    ToggleSwitch,
  },

  props: {
    isSaving: {
      type: Object,
      required: true,
    },

    settings: {
      type: Object,
      required: true,
    },
  },

  emits: ['update'],

  data() {
    return {
      override: this.settings.override_global_participation_settings,
      autoAssign: this.settings.sync_participant_instance_creation,
      autoClose: this.settings.sync_participant_instance_closure,
    };
  },

  watch: {
    settings: {
      deep: true,
      handler(value) {
        this.autoAssign = value.sync_participant_instance_creation;
        this.autoClose = value.sync_participant_instance_closure;
      },
    },
  },

  methods: {
    /**
     * Update the current toggle states and emit them to parent component
     *
     * @param {String} toggle which toggle was clicked
     */
    updateRelationshipParticipation(toggle) {
      const settings = {
        override: this.override,
        autoAssign: this.autoAssign,
        autoClose: this.autoClose,
      };

      const disabled = {
        override: toggle == 'override',
        assign: toggle == 'assign',
        close: toggle == 'close',
      };

      this.$emit('update', { settings: settings, disabled: disabled });
    },
  },
};
</script>
