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
  @module performelement_perform_goal_creation
-->

<template>
  <!-- Handle the different view switching (read only / print / form),
  populate form content if editable and display others responses -->
  <ElementParticipantFormContent
    class="tui-performGoalCreationParticipantForm"
    v-bind="$attrs"
    :active-section-is-closed="activeSectionIsClosed"
    :element="element"
    :is-draft="isDraft"
    :section-element="sectionElement"
    :show-other-response="showOtherResponse"
    @view="openDetailsModal"
  >
    <template v-slot:content>
      <PerformGoalCreationResponseDisplay
        :active-section-is-closed="activeSectionIsClosed"
        :data="addedGoalsData"
        :element="sectionElement.element"
        :exclude-no-response-message="canManage"
        :permissions="permissions"
        @delete="openDeleteModal"
        @edit="openEditModal"
        @view="openDetailsModal"
      />

      <FormScope :path="path" :process="process">
        <div v-if="canManage">
          <Button
            class="tui-performGoalCreationParticipantForm__createButton"
            :text="$str('create_goal', 'performelement_perform_goal_creation')"
            @click="openActionModal('create')"
          />
        </div>
        <div
          v-else
          class="tui-performGoalCreationParticipantForm__permissionMessage"
        >
          {{
            $str(
              'no_permission_to_create_goal_text',
              'performelement_perform_goal_creation',
              subjectUser.fullname
            )
          }}
        </div>
      </FormScope>

      <!-- Display validation error when appropriate -->
      <FormField
        :name="$id('performGoalCreation')"
        :validations="validations"
      />

      <!-- Delete modal -->
      <GoalDeleteModal
        :id="goalId"
        :open="showDeleteModal"
        @cancel="hideDeleteModal"
        @handle-deleted="handleDeleted"
      />
    </template>

    <template v-slot:modals>
      <!-- Actions modal (available to all views) -->
      <ModalPresenter :open="showActionModal" @request-close="hideActionModal">
        <GoalActionModal
          :id="goalId"
          :action="action"
          :show-actions="showActionsMenu"
          :subject-id="subjectUser.id"
          @created="handleCreated"
          @edited="handleEdited"
          @show-delete="openDeleteModal"
          @request-close="hideActionModal"
        />
      </ModalPresenter>
    </template>
  </ElementParticipantFormContent>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ElementParticipantFormContent from 'mod_perform/components/element/ElementParticipantFormContent';
import { FormField, FormScope } from 'tui/components/uniform';
import GoalActionModal from 'perform_goal/components/view/PerformGoalActionModal';
import GoalDeleteModal from 'perform_goal/components/manage/PerformGoalDeleteModal';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import PerformGoalCreationResponseDisplay from 'performelement_perform_goal_creation/components/PerformGoalCreationResponseDisplay';

// Util
import { notify } from 'tui/notifications';
import { v as validation } from 'tui/validation';

export default {
  components: {
    Button,
    ElementParticipantFormContent,
    FormField,
    FormScope,
    GoalActionModal,
    GoalDeleteModal,
    ModalPresenter,
    PerformGoalCreationResponseDisplay,
  },

  props: {
    activeSectionIsClosed: { type: Boolean },
    disabled: { type: Boolean },
    element: { type: Object },
    isDraft: { type: Boolean },
    path: { type: [String, Array], default: '' },
    permissions: { type: Object },
    sectionElement: { type: Object },
    showOtherResponse: { type: Boolean },
    subjectUser: { type: Object, required: true },
  },

  emits: ['unsaved-plugin-change'],

  data() {
    return {
      // Action modal view
      action: null,

      // addedGoals should be initialised with any goals previously added via this element
      // response_data_formatted_lines data is json encoded
      addedGoalsData: this.sectionElement.response_data_formatted_lines.map(
        goal => JSON.parse(goal)
      ),

      // Currently hard coded to our default goal type
      createGoalType: 'basic',

      // The id of the selected goal
      goalId: null,

      // Existing goals for determining unsaved changes
      initialAddedGoalIds: this.sectionElement.response_data || [],

      // Should we open the goal action modal
      showActionModal: false,

      // Should we show the actions menu on the goal action modal
      showActionsMenu: true,

      // Should we open the goal delete modal
      showDeleteModal: false,
    };
  },

  computed: {
    /**
     * Get just the goal ids from the addedGoalsData array
     *
     * @return {Array}
     */
    addedGoalIds() {
      return this.addedGoalsData.map(data => data.goal.id);
    },

    /**
     * Can this user manage the response
     *
     * @return {Boolean}
     */
    canManage() {
      return this.permissions && this.permissions.can_manage;
    },

    /**
     * Gets the component that allows for a new goal to be created
     *
     * @return {function}
     */
    goalCreationComponent() {
      let componentPluginPath =
        'performgoal_type_' + this.createGoalType + '/components/';

      return tui.asyncComponent(
        componentPluginPath + 'manage/PerformGoalCreateModal'
      );
    },

    /**
     * Is this element required
     *
     * @return {Boolean}
     */
    required() {
      return this.sectionElement.element.is_required;
    },
  },

  watch: {
    addedGoalIds: {
      deep: true,
      handler(value) {
        this.$emit('unsaved-plugin-change', {
          key: this.id,
          hasChanges:
            JSON.stringify(value) != JSON.stringify(this.initialAddedGoalIds),
        });
      },
    },
  },

  methods: {
    /**
     * Add the created goal to the added goals array and show success notification
     *
     * @param {Object} result the response returned from the create query
     */
    handleCreated(result) {
      if (!this.addedGoalIds.includes(result.data.goal.id)) {
        this.addedGoalsData.push(result.data);
        this.goalId = result.data.goal.id;

        // Success notification
        notify({
          message: this.$str(
            'perform_goal_create_success',
            'performelement_perform_goal_creation'
          ),
          type: 'success',
        });
      }
    },

    /**
     * Remove the deleted goal from the added goals array and show success notification
     *
     * @param {Object} result the response returned from the delete query
     */
    handleDeleted() {
      this.hideDeleteModal();
      this.hideActionModal();
      this.addedGoalsData = this.addedGoalsData.filter(
        item => item.goal.id != this.goalId
      );
      // Success notification
      notify({
        message: this.$str(
          'perform_goal_delete_modal_success',
          'performelement_perform_goal_creation'
        ),
        type: 'success',
      });
    },

    /**
     * Update this goal to reflect the changes made during the edit
     *
     * @param {Object} result the response returned from the edit query
     */
    handleEdited(result) {
      // Find existing local goal data
      let goalIndex = this.addedGoalsData.findIndex(
        item => item.goal.id == result.data.goal.id
      );

      // If existing goal data found, replace it
      if (goalIndex > -1) {
        this.addedGoalsData[goalIndex] = result.data;
      }
    },

    /**
     * Hide the goal action modal
     */
    hideActionModal() {
      this.showActionModal = false;
    },

    /**
     * Hide the delete goal modal
     */
    hideDeleteModal() {
      this.showDeleteModal = false;
    },

    /**
     * Open the goal action modal based on provided action
     *
     * @param {String} action
     * @param {Number} goalId
     */
    openActionModal(action, goalId) {
      this.action = action;
      this.goalId = goalId;
      this.showActionModal = true;
    },

    /**
     * Open the delete goal modal
     *
     * @param {Number} goalId
     */
    openDeleteModal(goalId) {
      if (goalId) {
        this.goalId = goalId;
      }
      this.showDeleteModal = true;
    },

    /**
     * Open the details action modal
     *
     * @param {Number} id
     */
    openDetailsModal({ id, showActionsMenu }) {
      this.showActionsMenu = showActionsMenu;
      this.openActionModal('details', id);
    },

    /**
     * Open the edit action modal
     *
     * @param {Number} id
     */
    openEditModal(id) {
      this.openActionModal('edit', id);
    },

    /**
     * Process the form values.
     *
     * Will always be the added goal ids
     *
     * @return {Array}
     */
    process() {
      return this.addedGoalIds;
    },

    /**
     * An array of validation rules for the element.
     * The rules returned depend on this users permissions, if we are saving as draft, or if a response is required or not.
     *
     * @return {(function|object)[]}
     */
    validations() {
      if (this.isDraft) {
        return [];
      }

      // If required but user doesn't have permission, then we allow an empty response
      if (this.required && !this.canManage) {
        return [];
      }

      // If required but no goals added then enforce required
      if (this.required && !this.addedGoalsData.length) {
        return [validation.required()];
      }

      return [];
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalCreationParticipantForm {
  &__createButton {
    margin-top: var(--gap-2);
  }

  &__permissionMessage {
    @include font(body-sm);
    margin-top: var(--gap-2);
    font-style: italic;
  }
}
</style>
