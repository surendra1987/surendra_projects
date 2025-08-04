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
  @package perform_goal
-->

<template>
  <div class="tui-performGoalDetailsTaskItem">
    <Checkbox
      :id="'checkbox_id' + task.id"
      class="tui-performGoalDetailsTaskItem__checkbox"
      :aria-label="screenReaderLabel"
      :checked="taskCompleted"
      :disabled="!canManage"
      :large="true"
      @change="completionChange($event)"
    />

    <div class="tui-performGoalDetailsTaskItem__content">
      <div class="tui-performGoalDetailsTaskItem__content-left">
        <div
          v-if="task.description"
          class="tui-performGoalDetailsTaskItem__content-label"
          :class="{
            'tui-performGoalDetailsTaskItem__content-label--complete': taskCompleted,
          }"
        >
          {{ task.description }}
        </div>

        <div
          v-if="hasAttachedResource"
          class="tui-performGoalDetailsTaskItem__content-resource"
        >
          <CourseCard
            class="tui-performGoalDetailsTaskItem__content-resourceCard"
            :available="task.resource_exists"
            :editing="false"
            :has-access="task.resource_can_view"
            :image="resourceAvailable ? resourceData.image_data : null"
            :progress="resourceAvailable ? resourceData.subject_progress : null"
            :title="resourceAvailable ? resourceData.name : null"
            :url="resourceAvailable ? resourceData.url : null"
          />
        </div>
      </div>

      <!-- Actions menu -->
      <div class="tui-performGoalDetailsTaskItem__actions">
        <Dropdown position="bottom-right">
          <template v-slot:trigger="{ toggle, isOpen }">
            <MoreButton
              :aria-expanded="isOpen.toString()"
              :aria-label="
                $str('goal_task_actions', 'perform_goal', screenReaderLabel)
              "
              @click="toggle"
            />
          </template>

          <DropdownButton
            :aria-label="$str('a11y_goal_task_actions_edit', 'perform_goal')"
            @click="enableEditTask"
          >
            {{ $str('goal_task_actions_edit', 'perform_goal') }}
          </DropdownButton>

          <DropdownButton
            :aria-label="$str('a11y_goal_task_actions_delete', 'perform_goal')"
            @click="deleteTask"
          >
            {{ $str('goal_task_actions_delete', 'perform_goal') }}
          </DropdownButton>
        </Dropdown>
      </div>
    </div>
  </div>
</template>

<script>
import Checkbox from 'tui/components/form/Checkbox';
import CourseCard from 'perform_goal/components/tasks/PerformGoalTaskCourseCard';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownButton from 'tui/components/dropdown/DropdownButton';
import MoreButton from 'tui/components/buttons/MoreIcon';

export default {
  components: {
    Checkbox,
    CourseCard,
    Dropdown,
    DropdownButton,
    MoreButton,
  },

  props: {
    // Has goal can manage permissions
    canManage: { type: Boolean },
    // Is the task being edited
    editing: { type: Boolean },
    // Goal task data
    task: { type: Object, required: true },
  },

  emits: ['completion', 'delete', 'enable-edit'],

  data() {
    return {
      // Is there a recent change to the completion that may have not been saved yet?
      hasOptimisticChange: false,
      // What is the state after the most recent change to the completion?
      optimisticCompletionState: false,
    };
  },

  computed: {
    /**
     * Resource has been attached to task
     *
     * @return {Boolean}
     */
    hasAttachedResource() {
      return this.task.resource !== null;
    },

    /**
     * Resource has been attached and it is available to the user
     *
     * @return {Boolean}
     */
    resourceAvailable() {
      return (
        this.task.resource_exists &&
        this.task.resource_can_view &&
        this.hasAttachedResource
      );
    },

    /**
     * Task resource data if available
     *
     * @return {Object}
     */
    resourceData() {
      if (!this.resourceAvailable) {
        return false;
      }
      return JSON.parse(this.task.resource.resource_custom_data);
    },

    /**
     * Label for task checkbox
     *
     * @return {String}
     */
    screenReaderLabel() {
      if (this.task.description) {
        return this.task.description;
      } else if (this.resourceAvailable) {
        return this.resourceData.name;
      } else if (!this.task.resource_can_view) {
        return this.$str('goal_task_course_no_access', 'perform_goal');
      } else {
        return this.$str('goal_task_course_not_available', 'perform_goal');
      }
    },

    /**
     * Check if the task has been completed
     *
     * @return {Boolean}
     */
    taskCompleted() {
      if (this.hasOptimisticChange) {
        return this.optimisticCompletionState;
      }

      return this.task.completed_at && this.task.completed_at.length > 0;
    },
  },

  watch: {
    task() {
      this.hasOptimisticChange = false;
    },
  },

  methods: {
    /**
     * Trigger change of task completion
     *
     * @param {Boolean} checked checkbox checked state
     */
    completionChange(checked) {
      this.hasOptimisticChange = true;
      this.optimisticCompletionState = checked;

      this.$emit('completion', { id: this.task.id, state: checked });
    },

    /**
     * Delete goal task
     *
     */
    deleteTask() {
      this.$emit('delete', this.task.id);
    },

    /**
     * Enable edit goal task UI
     *
     */
    enableEditTask() {
      // Existing values formatted for edit form
      let existingValues = {
        completed: this.taskCompleted,
        description: this.task.description,
        hasAttachedResource: this.hasAttachedResource,
        resourceData: null,
        resource_id: null,
        resource_type: null,
      };

      // If task has resource provide required fields
      if (this.hasAttachedResource) {
        existingValues.resource_id = this.task.resource.resource_id;
        existingValues.resource_type = this.task.resource.resource_type;

        existingValues.resourceData = {
          fullname: this.resourceData.name || null,
          image: this.resourceData.image_data || null,
          resource_exists: this.task.resource_exists,
          resource_can_view: this.task.resource_can_view,
        };
      }

      this.$emit('enable-edit', { id: this.task.id, values: existingValues });
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalDetailsTaskItem {
  display: flex;

  &__checkbox label {
    cursor: pointer;
  }

  &__content {
    display: flex;
    flex-grow: 1;
    gap: var(--gap-4);
    justify-content: space-between;

    &-left {
      display: flex;
      flex-direction: column;
      flex-grow: 1;
      gap: var(--gap-2);
      word-break: break-word;
    }

    &-label {
      font-weight: 500;
      &--complete {
        color: var(--color-neutral-6);
        text-decoration: line-through;
      }
    }

    &-resource {
      display: flex;
      flex-grow: 1;
    }

    &-resourceCard {
      min-width: 234px;
      max-width: 340px;
    }
  }

  &__actions {
    display: flex;
    flex-direction: column;

    &-item {
      display: flex;
    }
  }
}
</style>
