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
  @module perform_goal
-->

<template>
  <div
    v-if="goal && settings.enable_status_change"
    class="tui-performGoalChangeStatusForm"
  >
    <div class="tui-performGoalChangeStatusForm__header">
      <h2 class="tui-performGoalChangeStatusForm__header-title">
        {{ $str('goal_progression_update', 'perform_goal') }}
        <span
          v-if="required"
          class="tui-performGoalChangeStatusForm__header-titleRequired"
        >
          <span aria-hidden="true">*</span>
          <span class="sr-only">{{ $str('required', 'core') }}</span>
        </span>
      </h2>

      <div
        v-if="statusChange"
        class="tui-performGoalChangeStatusForm__header-updatedBy"
      >
        <template v-if="statusChange.status_changer_user">
          {{
            $str('goal_progress_updated_by', 'perform_goal', {
              name: statusChange.status_changer_user.fullname,
              subject: settings.status_change_relationship_name,
              date: statusChange.created_at,
            })
          }}
        </template>
        <template v-else>
          {{ $str('goal_progress_updated_by_unknown', 'perform_goal') }}
        </template>
      </div>
    </div>

    <!-- Change status -->
    <div
      v-if="!statusChange"
      class="tui-performGoalChangeStatusForm__changeStatus"
    >
      <FormRow
        :label="
          $str(
            'goal_status_response_subject',
            'perform_goal',
            settings.status_change_relationship_name
          )
        "
      >
        <component
          :is="changeStatusComponent"
          :can-update="canUpdate"
          :content="content"
          :from-print="fromPrint"
          :participant-instance-id="participantInstanceId"
          :section-element-id="sectionElementId"
          @updated="handleStatusUpdate"
        />
      </FormRow>
    </div>

    <!-- Show status -->
    <div v-else class="tui-performGoalChangeStatusForm__showStatus">
      <component
        :is="statusResponseComponent"
        :goal="goal"
        :status-change="statusChange"
      />
    </div>
  </div>
</template>

<script>
import { FormRow } from 'tui/components/uniform';

// Util
import { notify } from 'tui/notifications';

export default {
  components: {
    FormRow,
  },

  props: {
    canUpdate: { type: Boolean, default: true },
    content: { type: Object, required: true },
    elementData: { type: Object, required: true },
    fromPrint: { type: Boolean },
    participantInstanceId: { type: [String, Number] },
    required: { type: Boolean },
    sectionElementId: { type: [String, Number] },
    subjectUser: { type: Object, required: true },
  },

  emits: ['update'],

  data() {
    return {
      // Goal data
      goal: this.content.goal,
      // Settings data
      settings: this.elementData.content_type_settings,
      // Data after update
      statusChange: this.content.status_change,
    };
  },

  computed: {
    /**
     * Gets the vue component that displays the change status UI
     *
     * @return {Function}
     */
    changeStatusComponent() {
      if (!this.goal.plugin_name) {
        return null;
      }

      return tui.asyncComponent(
        this.componentPluginPath + 'view/PerformGoalLinkedReviewChangeStatus'
      );
    },

    /**
     * Gets the component path for the goal plugin type
     *
     * @return {string}
     */
    componentPluginPath() {
      return 'performgoal_type_' + this.goal.plugin_name + '/components/';
    },

    /**
     * Gets the vue component that displays the status result UI
     *
     * @return {Function}
     */
    statusResponseComponent() {
      if (!this.goal.plugin_name) {
        return null;
      }

      return tui.asyncComponent(
        this.componentPluginPath + 'view/PerformGoalLinkedReviewStatusResponse'
      );
    },
  },

  methods: {
    /**
     * Handle update from goal status/progress change
     *
     * @param {Object} data the goal data returned from the update query
     */
    handleStatusUpdate(data) {
      this.statusChange = data.perform_status_change;

      this.$emit('update');

      // Success notification
      notify({
        message: this.$str('goal_progress_updated', 'perform_goal'),
        type: 'success',
      });
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalChangeStatusForm {
  display: flex;
  flex-direction: column;
  gap: var(--gap-4);
  max-width: 1200px;
  padding: var(--gap-4) 0;
  border-bottom: var(--border-width-normal) solid var(--color-neutral-7);

  &__header {
    display: flex;
    flex-direction: column;
    gap: var(--gap-2);
    margin: 0;

    &-title {
      @include font(h4);
      margin: 0;
    }

    &-titleRequired {
      color: var(--color-prompt-alert);
      font-weight: var(--label-weight);
    }

    &-updatedBy {
      @include font(body-sm);
      color: var(--color-neutral-6);
    }
  }

  &__changeStatus {
    display: flex;
    flex-direction: column;
  }
}
</style>
