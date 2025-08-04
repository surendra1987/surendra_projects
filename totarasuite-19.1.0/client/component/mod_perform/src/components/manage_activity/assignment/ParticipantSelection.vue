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

  @author Murali Nair <murali.nair@totaralearning.com>
  @module mod_perform
  @deprecated Since Totara 19.0
-->
<template>
  <ActivitySetting
    class="tui-performAssignmentParticipantSelection"
    :title="$str('manual_participant_selector_role_heading', 'mod_perform')"
  >
    <template v-slot:description>
      {{ $str('manual_participant_selector_role_description', 'mod_perform') }}
    </template>

    <template v-slot:form>
      <Form class="tui-performAssignmentParticipantSelection__form">
        <FormRowStack spacing="large">
          <FormRow
            v-for="relationship in manualRelationships"
            :key="relationship.id"
            v-slot="{ id }"
            :label="relationship.name"
          >
            <div>
              <span v-if="isActive">
                {{ relationship.selector_relationship_name }}
              </span>
              <Select
                v-else
                :id="id"
                v-model:value="manualRelationshipSelections[relationship.id]"
                :aria-labelledby="id"
                :aria-label="relationship.name"
                :aria-describedby="$id('aria-describedby')"
                :options="manualRelationshipOptions"
                @input="updateManualRelationships"
              />
            </div>
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
import Select from 'tui/components/form/Select';

import { ACTIVITY_STATUS_ACTIVE } from 'mod_perform/constants';

export default {
  components: {
    ActivitySetting,
    Form,
    FormRow,
    FormRowStack,
    Select,
  },

  props: {
    activity: {
      type: Object,
      required: true,
    },
    manualRelationshipOptions: {
      type: Array,
    },
  },

  emits: ['update'],

  data() {
    return {
      manualRelationshipSelections: this.getManualRelationshipSelections(),
    };
  },

  computed: {
    /**
     * Indicates whether the activity is active
     *
     * @return {boolean}
     */
    isActive() {
      return this.activity.state_details.name === ACTIVITY_STATUS_ACTIVE;
    },

    manualRelationships() {
      return this.activity.manual_relationships.map(relationship => {
        return {
          name: relationship.manual_relationship.name,
          id: relationship.manual_relationship.id,
          selector_relationship_id: relationship.selector_relationship.id,
          selector_relationship_name: relationship.selector_relationship.name,
        };
      });
    },
  },

  methods: {
    /**
     * Gets manual relationship selections as an object map.
     * {manual_relationship_id: selected_relationship_id}
     *
     * @returns {Object}
     */
    getManualRelationshipSelections() {
      let relationshipSelections = {};
      this.activity.manual_relationships.forEach(relationship => {
        relationshipSelections[relationship.manual_relationship.id] =
          relationship.selector_relationship.id;
      });

      return relationshipSelections;
    },

    /**
     * Saves the manual relationship selections to the backend.
     */
    updateManualRelationships() {
      const relationships = Object.keys(this.manualRelationshipSelections).map(
        manual_relationship_id => {
          return {
            manual_relationship_id: manual_relationship_id,
            selector_relationship_id: this.manualRelationshipSelections[
              manual_relationship_id
            ],
          };
        }
      );
      this.$emit('update', relationships);
    },
  },
};
</script>
