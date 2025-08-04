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
  <Modal
    ref="goalActionModal"
    :aria-labelledby="$id('title')"
    :dismissable="{ backdropClick: false }"
    :shade="true"
    size="large"
    type="drawer"
  >
    <!-- Create modal -->
    <component
      :is="getGoalCreationComponent()"
      v-if="view === 'create'"
      :subject-id="subjectId"
      :title-id="$id('title')"
      @close="closeModal"
      @submitted="handleCreated(true, $event)"
      @submit-show="handleCreated(false, $event)"
    />

    <!-- Edit modal -->
    <component
      :is="getGoalEditComponent()"
      v-if="view === 'edit'"
      :id="goalId"
      :subject-id="subjectId"
      :title-id="$id('title')"
      @cancel="handleExit"
      @close="closeModal"
      @submitted="handleEdited"
    />

    <!-- Status update modal -->
    <component
      :is="getGoalUpdateComponent()"
      v-if="view === 'update'"
      :id="goalId"
      :participant-instance-id="participantInstanceId"
      :section-element-id="sectionElementId"
      :subject-id="subjectId"
      :title-id="$id('title')"
      @cancel="handleExit"
      @close="closeModal"
      @submitted="handleUpdate"
    />

    <!-- View modal -->
    <component
      :is="getGoalDetailsComponent()"
      v-else-if="view === 'details'"
      :id="goalId"
      :show-actions="showActions"
      :subject-id="subjectId"
      :title-id="$id('title')"
      @close="closeModal"
      @content-update="handleContentUpdate"
      @show-delete="showDelete"
      @show-edit="switchView('edit')"
      @show-update="switchView('update')"
    />
  </Modal>
</template>

<script>
import Modal from 'tui/components/modal/Modal';

export default {
  components: {
    Modal,
  },

  props: {
    // Current action modal view
    action: { type: String, required: true },
    // The ID for this goal
    id: { type: [Number, String] },
    // The ID of the participant instance for when updating via review item element
    participantInstanceId: { type: [Number, String] },
    // The ID of the section for when updating via review item element
    sectionElementId: { type: [Number, String] },
    // Show actions menu on the view details modal
    showActions: { type: Boolean, default: true },
    // Subject ID we are creating a personal goal for
    subjectId: { type: [String, Number] },
  },

  emits: [
    'request-close',
    'created',
    'edited',
    'update',
    'change',
    'show-delete',
    'content-update',
  ],

  data() {
    return {
      // Currently hard coded to our default goal type
      goalPluginType: 'basic',
      // Have we navigated to the current modal from the details modal?
      fromDetailsModal: false,
      goalId: this.id,
      // Current content view
      view: this.action,
    };
  },

  mounted() {
    this.$refs.goalActionModal.$refs.modal.focus();
  },

  methods: {
    /**
     * Close this modal
     */
    closeModal() {
      this.$emit('request-close');
    },

    /**
     * Gets the component path for the goal plugin type
     *
     * @param {String} plugin plugin name
     * @param {String} path component path
     * @return {String}
     */
    getComponentPluginPath(plugin, path) {
      return 'performgoal_type_' + plugin + '/components/' + path;
    },

    /**
     * Gets the component that allows for a new goal to be created
     *
     * @return {function}
     */
    getGoalCreationComponent() {
      return tui.asyncComponent(
        this.getComponentPluginPath(
          this.goalPluginType,
          'manage/PerformGoalActionModalCreate'
        )
      );
    },

    /**
     * Gets the component that displays a goals details
     *
     * @return {function}
     */
    getGoalDetailsComponent() {
      return tui.asyncComponent(
        this.getComponentPluginPath(
          this.goalPluginType,
          'manage/PerformGoalActionModalDetails'
        )
      );
    },

    /**
     * Gets the component that allows for a goal to be edited
     *
     * @return {function}
     */
    getGoalEditComponent() {
      return tui.asyncComponent(
        this.getComponentPluginPath(
          this.goalPluginType,
          'manage/PerformGoalActionModalEdit'
        )
      );
    },

    /**
     * Gets the component that allows for a goals progress to be updated
     *
     * @return {function}
     */
    getGoalUpdateComponent() {
      return tui.asyncComponent(
        this.getComponentPluginPath(
          this.goalPluginType,
          'manage/PerformGoalActionModalUpdate'
        )
      );
    },

    /**
     * Handle the created event
     *
     * @param {Boolean} exit close the action modal
     * @param {Object} data event data
     */
    handleCreated(exit, data) {
      this.$emit('created', {
        data: data,
        message: this.$str('goal_created_success', 'perform_goal'),
      });

      this.goalId = data.goal.id;

      if (exit) {
        this.closeModal();
      } else {
        this.switchView('details');
      }
    },

    /**
     * Handle the edited event
     *
     * @param {Object} data event data
     */
    handleEdited(data) {
      this.$emit('edited', {
        data: data,
      });

      this.goalId = data.goal.id;
      this.switchView('details');
    },

    /**
     * Either switch back to the detail view if we came from there, or close the modal
     */
    handleExit() {
      return this.fromDetailsModal
        ? this.switchView('details')
        : this.closeModal();
    },

    /**
     * Handle the update of content (comments / tasks)
     *
     */
    handleContentUpdate() {
      this.$emit('content-update', {});
    },

    /**
     * Handle the progress update event
     *
     * @param {Object} data event data
     */
    handleUpdate(data) {
      this.$emit('update', {
        data: data,
      });

      this.handleExit();
    },

    /**
     * Emit the change event and decide how to exit
     */
    handleSubmitted(change) {
      this.$emit('change', change);
      this.handleExit();
    },

    /**
     * Open the delete modal
     */
    showDelete() {
      this.$emit('show-delete');
    },

    /**
     * Switch the current modal content view
     */
    switchView(view) {
      this.$refs.goalActionModal.$refs.modal.focus();
      this.fromDetailsModal = this.view == 'details';
      this.view = view;
    },
  },
};
</script>
