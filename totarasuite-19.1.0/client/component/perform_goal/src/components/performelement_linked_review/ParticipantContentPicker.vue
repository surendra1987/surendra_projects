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
  @package perform_goal
-->

<template>
  <div>
    <SelectContent
      :adder="getAdder()"
      :add-btn-text="$str('review_add_goals', 'perform_goal')"
      :can-show-adder="canShowAdder"
      :cant-add-text="
        $str(
          'review_awaiting_goal_selection',
          'perform_goal',
          coreRelationship[0].name
        )
      "
      :existing-item-ids="existingItemIds"
      :is-draft="isDraft"
      :participant-instance-id="participantInstanceId"
      :remove-text="$str('review_remove_goal', 'perform_goal')"
      :required="required"
      :section-element-id="sectionElementId"
      :user-id="userId"
      @unsaved-plugin-change="$emit('unsaved-plugin-change', $event)"
      @update="$emit('update', $event)"
    >
      <template v-slot:content-preview="{ content }">
        <component
          :is="previewComponent"
          :content="content"
          :subject-user="subjectUser"
        />
      </template>
    </SelectContent>
  </div>
</template>

<script>
import GoalAdder from 'perform_goal/components/adder/PerformGoalAdder';
import SelectContent from 'performelement_linked_review/components/SelectContent';

export default {
  components: {
    GoalAdder,
    SelectContent,
  },

  props: {
    // Can display adder to user
    canShowAdder: { type: Boolean, required: true },
    // Users role relationship
    coreRelationship: { type: Array },
    // The ids for the items that have already been saved to this review element
    existingItemIds: { type: Array },
    // Is in a draft state
    isDraft: { type: Boolean },
    // Participant instance ID
    participantInstanceId: { type: [String, Number], required: true },
    // Displaying a preview version of the selected goals
    previewComponent: { type: [Function, Object] },
    // Is the response required
    required: { type: Boolean },
    // Section element ID
    sectionElementId: { type: String },
    // Subject user
    subjectUser: { type: Object },
    // User ID
    userId: { type: Number },
  },

  emits: ['unsaved-plugin-change', 'update'],

  methods: {
    /**
     * Get adder component
     *
     * @return {Object}
     */
    getAdder() {
      return GoalAdder;
    },
  },
};
</script>
