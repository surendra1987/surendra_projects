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
  <div class="tui-performGoalDetailsTaskList">
    <Loader :loading="creating || deleting || editing">
      <fieldset
        v-if="hasTasks"
        ref="tasks"
        class="tui-performGoalDetailsTaskList__tasks"
      >
        <legend class="sr-only">
          {{ $str('a11y_goal_tasks_to_be_completed', 'perform_goal') }}
        </legend>
        <div class="tui-performGoalDetailsTaskList__tasks-progress">
          {{
            $str('goal_tasks_completed', 'perform_goal', {
              completed: tasksCompleted,
              total: tasksTotal,
            })
          }}
        </div>

        <ul class="tui-performGoalDetailsTaskList__tasks-items">
          <li
            v-for="task in goalTasks"
            :key="task.id"
            class="tui-performGoalDetailsTaskList__tasks-itemsItem"
          >
            <!-- Editing task -->
            <TaskForm
              v-if="task.id === editingTask"
              :data="editingData"
              :loading="creating || deleting"
              :saving="editing"
              @cancelled="editDisabled"
              @submit="handleEdit"
            />

            <!-- Viewing task -->
            <TaskItem
              v-else
              :ref="'task-' + task.id"
              :key="task.id"
              :can-manage="permissions.can_manage"
              :task="task"
              @completion="handleCompletion"
              @delete="handleDeletion"
              @enable-edit="enableEdit"
            />
            <Separator :key="'separator' + task.id" />
          </li>
        </ul>
      </fieldset>
    </Loader>
    <div
      v-if="permissions.can_manage"
      class="tui-performGoalDetailsTaskList__add"
    >
      <TaskForm
        :loading="creating || deleting"
        :saving="creating"
        @add-cancelled="focusLastElement"
        @submit="handleCreation"
      />
    </div>
  </div>
</template>

<script>
import Loader from 'tui/components/loading/Loader';
import { notify } from 'tui/notifications';
import Separator from 'tui/components/decor/Separator';
import TaskForm from 'perform_goal/components/tasks/PerformGoalTaskForm';
import TaskItem from 'perform_goal/components/tasks/PerformGoalTaskItem';

// Util
import { getTabbableElements } from 'tui/dom/focus';

// GraphQL
import CompletionTaskQuery from 'perform_goal/graphql/set_goal_task_progress';
import CreateTaskQuery from 'perform_goal/graphql/create_goal_task';
import DeleteTaskQuery from 'perform_goal/graphql/delete_goal_task';
import GoalTasks from 'perform_goal/graphql/get_goal_tasks';
import UpdateTaskQuery from 'perform_goal/graphql/update_goal_task';

export default {
  components: {
    Loader,
    Separator,
    TaskForm,
    TaskItem,
  },

  props: {
    // Goal data
    goal: { type: Object, required: true },
    // Goal permissions
    permissions: { type: Object, required: true },
  },

  emits: ['content-update'],

  data() {
    return {
      // Creating a new goal task
      creating: false,
      // Deleting a goal task
      deleting: false,
      // Editing a goal task
      editing: false,
      // Task edited existing form data
      editingData: null,
      // Task being edited
      editingTask: null,
      // Goal tasks data
      goalTasks: [],
      // Number of completed tasks
      tasksCompleted: 0,
      // Number of tasks
      tasksTotal: 0,
    };
  },

  computed: {
    hasTasks() {
      return this.goalTasks.length;
    },
  },

  /**
   * Fetch the latest goal data
   *
   */
  apollo: {
    goalTasks: {
      query: GoalTasks,
      fetchPolicy: 'no-cache',
      variables() {
        return {
          goal_reference: {
            id: parseInt(this.goal.id),
          },
        };
      },
      update({ perform_goal_get_goal_tasks: data }) {
        this.tasksTotal = data.total;
        this.tasksCompleted = data.completed;
        this.creating = false;
        this.editing = false;

        if (this.deleting) {
          this.deleting = false;

          // Set focus at top of task list
          this.$nextTick(() => {
            this.focusFirstElement();
          });
        }

        return data.tasks;
      },
    },
  },

  methods: {
    /**
     * Set focus to the first tabbable element
     *
     */
    focusFirstElement() {
      if (!this.hasTasks) {
        return;
      }

      const tabbable = getTabbableElements(this.$refs.tasks);
      if (tabbable) {
        tabbable[0].focus();
      }
    },

    /**
     * Set focus to the last tabbable element
     *
     */
    focusLastElement() {
      if (!this.hasTasks) {
        return;
      }

      const tabbable = getTabbableElements(this.$refs.tasks);
      if (tabbable) {
        let lastItem = tabbable.length - 1;
        tabbable[lastItem].focus();
      }
    },

    /**
     * Handle a change in completion of a task
     *
     * @param {Object} data Task ID and state
     */
    async handleCompletion(data) {
      try {
        const result = await this.$apollo.mutate({
          mutation: CompletionTaskQuery,
          variables: {
            input: {
              goal_task_reference: {
                id: parseInt(data.id),
              },
              completed: data.state ? 1 : 0,
            },
          },
        });

        if (result && result.data.perform_goal_task_upsert_result) {
          // Refetch full task list
          this.$apollo.queries.goalTasks.refetch();
          this.handleTasksUpdate();
        }
      } catch (e) {
        // Error notification
        notify({
          message: this.$str('goal_task_completion_error', 'perform_goal'),
          type: 'error',
        });
      }
    },

    /**
     * Handle the submission of task creation
     *
     * @param {Object} values Form values.
     */
    async handleCreation(values) {
      try {
        this.creating = true;
        const result = await this.$apollo.mutate({
          mutation: CreateTaskQuery,
          variables: {
            input: {
              goal_id: this.goal.id,
              description: values.description.create
                ? values.description.create
                : null,
              resource_id: values.resource_id,
              resource_type: values.resource_type,
            },
          },
        });

        if (result && result.data.perform_goal_task_upsert_result.success) {
          // Refetch full task list
          this.$apollo.queries.goalTasks.refetch();
          this.handleTasksUpdate();
        } else if (
          result &&
          result.data.perform_goal_task_upsert_result.errors
        ) {
          // Error notification
          notify({
            message: result.data.perform_goal_task_upsert_result.errors.message,
            type: 'error',
          });
          this.creating = false;
        }
      } catch (e) {
        // Error notification
        notify({
          message: this.$str('goal_task_add_error', 'perform_goal'),
          type: 'error',
        });

        this.creating = false;
      }
    },

    /**
     * Handle the deletion of a task
     *
     * @param {Object} id Task ID.
     */
    async handleDeletion(id) {
      try {
        this.deleting = true;
        const result = await this.$apollo.mutate({
          mutation: DeleteTaskQuery,
          variables: {
            goal_task_reference: {
              id: parseInt(id),
            },
          },
        });

        if (result && result.data.perform_goal_delete_goal_task.success) {
          // Refetch full task list
          this.$apollo.queries.goalTasks.refetch();
          this.handleTasksUpdate();
        }
      } catch (e) {
        // Error notification
        notify({
          message: this.$str('goal_task_delete_error', 'perform_goal'),
          type: 'error',
        });

        this.deleting = false;
      }
    },

    /**
     * Handle the editing of a task
     *
     * @param {Object} values Form values.
     */
    async handleEdit(values) {
      try {
        this.editing = true;
        const result = await this.$apollo.mutate({
          mutation: UpdateTaskQuery,
          variables: {
            input: {
              goal_task_reference: {
                id: parseInt(this.editingTask),
              },
              description: values.description.edit
                ? values.description.edit
                : null,
              resource_id: values.resource_id,
              resource_type: values.resource_type,
            },
          },
        });
        if (result && result.data.perform_goal_task_upsert_result.success) {
          // Stop editing of task
          this.editDisabled();
          // Refetch full task list
          this.$apollo.queries.goalTasks.refetch();
        } else if (
          result &&
          result.data.perform_goal_task_upsert_result.errors
        ) {
          // Error notification
          notify({
            message: result.data.perform_goal_task_upsert_result.errors.message,
            type: 'error',
          });
          this.editing = false;
        }
      } catch (e) {
        // Error notification
        notify({
          message: this.$str('goal_task_update_error', 'perform_goal'),
          type: 'error',
        });

        this.editing = false;
      }
    },

    /**
     * Editing of task was stopped
     *
     */
    editDisabled() {
      // Ref name of current task
      let refName = 'task-' + this.editingTask;

      this.editingData = null;
      this.editingTask = null;

      this.$nextTick(() => {
        // Find task and it's first tabbable item
        let task = this.$refs[refName];
        if (task) {
          let tabbableItems = getTabbableElements(task[0].$el);

          if (tabbableItems.length) {
            tabbableItems[0].focus();
          }
        }
      });
    },

    /**
     * Enable edit for selected task
     *
     * @param {Object} data Task ID and form values.
     */
    enableEdit(data) {
      this.editingData = data.values;
      this.editingTask = data.id;
    },

    /**
     * Emit and event when there is a change
     * in number of tasks or number of completed tasks
     *
     */
    handleTasksUpdate() {
      this.$emit('content-update');
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalDetailsTaskList {
  display: flex;
  flex-direction: column;
  margin-top: var(--gap-1);

  &__tasks {
    display: flex;
    flex-direction: column;
    gap: var(--gap-4);

    &-progress {
      @include font(body-sm);
      color: var(--color-neutral-6);
    }

    &-items {
      display: flex;
      flex-direction: column;
      margin: 0;
      list-style: none;
    }
  }
}
</style>
