<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2021 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Arshad Anwer <arshad.anwer@totaralearning.com>
  @module hierarchy_goal
-->

<template>
  <SelectContent
    :adder="getAdder()"
    :add-btn-text="getAddBtnText()"
    :can-show-adder="canShowAdder"
    :cant-add-text="getCantAddText()"
    :existing-item-ids="existingItemIds"
    :is-draft="isDraft"
    :participant-instance-id="participantInstanceId"
    :remove-text="getRemoveText()"
    :required="required"
    :section-element-id="sectionElementId"
    :user-id="userId"
    @unsaved-plugin-change="$emit('unsaved-plugin-change', $event)"
    @update="$emit('update', $event)"
  >
    <template v-slot:content-preview="{ content }">
      <component
        :is="previewComponent"
        :content="getItemData(content)"
        :settings="settings"
        :subject-user="subjectUser"
      />
    </template>
  </SelectContent>
</template>

<script>
import AssignedCompanyGoalAdder from 'totara_hierarchy/components/adder/AssignedCompanyGoalAdder';
import PersonalGoalAdder from 'totara_hierarchy/components/adder/PersonalGoalAdder';
import SelectContent from 'performelement_linked_review/components/SelectContent';
import { COMPANY_GOAL } from '../../js/constants';

export default {
  components: {
    AssignedCompanyGoalAdder,
    PersonalGoalAdder,
    SelectContent,
  },

  props: {
    canShowAdder: {
      type: Boolean,
      required: true,
    },
    contentType: String,
    coreRelationship: Array,
    // The ids for the items that have already been saved to this review element
    existingItemIds: { type: Array },
    isDraft: Boolean,
    participantInstanceId: {
      type: [String, Number],
      required: true,
    },
    previewComponent: [Function, Object],
    required: Boolean,
    sectionElementId: String,
    subjectUser: Object,
    settings: { type: Object },
    userId: Number,
  },

  emits: ['unsaved-plugin-change', 'update'],

  methods: {
    getAddBtnText() {
      if (this.contentType === COMPANY_GOAL) {
        return this.$str('add_company_goals', 'hierarchy_goal');
      } else {
        return this.settings.perform_goals_transition_mode
          ? this.$str('add_legacy_personal_goals', 'hierarchy_goal')
          : this.$str('add_personal_goals', 'hierarchy_goal');
      }
    },

    getCantAddText() {
      if (this.contentType === COMPANY_GOAL) {
        return this.$str(
          'awaiting_company_selection_text',
          'hierarchy_goal',
          this.coreRelationship[0].name
        );
      } else {
        return this.$str(
          'awaiting_personal_selection_text',
          'hierarchy_goal',
          this.coreRelationship[0].name
        );
      }
    },

    getRemoveText() {
      if (this.contentType === COMPANY_GOAL) {
        return this.$str('remove_company_goal', 'hierarchy_goal');
      } else {
        return this.$str('remove_personal_goal', 'hierarchy_goal');
      }
    },

    /**
     * Get adder component
     *
     * @return {Object}
     */
    getAdder() {
      if (this.contentType === COMPANY_GOAL) {
        return AssignedCompanyGoalAdder;
      } else {
        return PersonalGoalAdder;
      }
    },

    /**
     * Get data for competency preview component
     *
     * @param {Object} values
     * @return {Object}
     */
    getItemData(values) {
      if (this.contentType === COMPANY_GOAL) {
        return {
          goal: {
            display_name: values.goal.full_name,
            description: values.goal.description,
            id: values.goal.id,
          },
          target_date: values.goal.target_date,
          status: values.scale_value,
          content_type: this.contentType,
        };
      } else {
        return {
          goal: {
            display_name: values.name,
            description: values.description,
            id: values.id,
          },
          target_date: values.target_date,
          status: values.scale_value,
          content_type: this.contentType,
        };
      }
    },
  },
};
</script>
