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
  <ActivitySetting
    class="tui-performPASettingParticipantSelection"
    :title="$str('manual_participant_selector_role_heading', 'mod_perform')"
  >
    <template v-slot:description>
      {{ $str('manual_participant_selector_role_description', 'mod_perform') }}
    </template>

    <template v-slot:form>
      <FormRowStack spacing="large">
        <FormRow
          v-for="role in manualRelationships"
          :key="role.id"
          v-slot="{ id }"
          :content-type="!role.mutable ? 'other' : null"
          :label="role.name"
        >
          <div>
            <span v-if="!role.mutable">
              {{ role.selector.name }}
            </span>
            <Select
              v-else
              :id="id"
              v-model:value="selectedValues[role.id]"
              :aria-label="role.name"
              :options="data.options"
              @input="updateSetting"
            />
          </div>
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
import Select from 'tui/components/form/Select';

// Util
import { notify } from 'tui/notifications';

// graphQL
import SetManualParticipantSelectorRoles from 'mod_perform/graphql/set_manual_relationship_selector_roles';

export default {
  components: {
    ActivitySetting,
    FormRow,
    FormRowStack,
    Select,
  },

  props: {
    activityId: { required: true, type: [Number, String] },
    data: { required: true, type: Object },
  },

  emits: ['updated'],

  data() {
    return {
      // Relationships data
      manualRelationships: this.data.relationships,
      // Saving changes
      saving: false,
      // Current selection of roles for each relationship
      selectedValues: this.getCurrentValues(),
    };
  },

  methods: {
    /**
     * Gets selected values for each option
     *
     * @returns {Object}
     */
    getCurrentValues() {
      let currentValues = {};
      this.data.relationships.forEach(role => {
        currentValues[role.id] = role.selector.id;
      });

      return currentValues;
    },

    /**
     * Handle the submission of setting value changes
     *
     */
    async updateSetting() {
      // Structure relationship data for mutation
      const relationships = Object.keys(this.selectedValues).map(id => {
        return {
          manual_relationship_id: id,
          selector_relationship_id: this.selectedValues[id],
        };
      });

      try {
        this.saving = true;
        const result = await this.$apollo.mutate({
          mutation: SetManualParticipantSelectorRoles,
          variables: {
            input: {
              activity_id: this.activityId,
              roles: relationships,
            },
          },
        });

        let response =
          result.data.mod_perform_set_manual_relationship_selector_roles;

        this.saving = false;

        if (response && response.success) {
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
