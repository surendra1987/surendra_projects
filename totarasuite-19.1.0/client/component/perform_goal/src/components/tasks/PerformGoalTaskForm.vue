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
  <div
    v-focus-within
    class="tui-performGoalDetailsTaskForm"
    @focusin="interactedWithForm"
  >
    <Checkbox
      :aria-hidden="true"
      :checked="completed"
      class="tui-performGoalDetailsTaskForm__checkbox"
      :disabled="true"
      :large="true"
    />

    <Uniform
      ref="form"
      class="tui-performGoalDetailsTaskForm__form"
      :initial-values="initialValues"
      input-width="full"
      @change="hasFormChanges"
      @submit="$emit('submit', $event)"
    >
      <div class="tui-performGoalDetailsTaskForm__form-input">
        <FormScope :path="'description'">
          <FormText
            char-length="full"
            :disabled="loading"
            :placeholder="
              $str(
                editing ? 'goal_task_edit_prompt' : 'goal_task_add_prompt',
                'perform_goal'
              )
            "
            :name="editing ? 'edit' : 'create'"
          />
        </FormScope>
      </div>

      <div v-if="formActive" class="tui-performGoalDetailsTaskForm__form-bar">
        <div
          v-if="hasAttachedResource"
          :aria-label="$str('a11y_goal_task_attached_resource', 'perform_goal')"
          class="tui-performGoalDetailsTaskForm__form-barResource"
        >
          <!-- Preview course card -->
          <CourseCard
            class="tui-performGoalDetailsTaskForm__form-barResourceCard"
            :available="linkedResourcePreview.resource_exists"
            :disabled="loading"
            :editing="true"
            :has-access="linkedResourcePreview.resource_can_view"
            :image="linkedResourcePreview.image"
            :title="linkedResourcePreview.fullname"
            @remove="removeResource"
          />
        </div>

        <!-- Add course button -->
        <ButtonIcon
          v-else
          ref="add-course"
          :aria-label="$str('goal_task_add_course', 'perform_goal')"
          :disabled="loading"
          :styleclass="{ small: 'true', transparentNoPadding: 'true' }"
          :text="$str('goal_task_add_course', 'perform_goal')"
          @click="openCoursePicker"
        >
          <AddIcon />
        </ButtonIcon>

        <!-- Save and cancel buttons -->
        <div class="tui-performGoalDetailsTaskForm__form-barButtons">
          <Button
            :disabled="loading"
            :styleclass="{ small: 'true', transparent: 'true' }"
            :text="$str('goal_task_cancel', 'perform_goal')"
            @click="clearTask"
          />

          <Button
            :disabled="!hasChanges || (loading && !saving)"
            :loading="saving"
            :styleclass="{ small: 'true' }"
            :text="$str('goal_task_save', 'perform_goal')"
            type="submit"
          />
        </div>
      </div>
    </Uniform>

    <!-- Course picker -->
    <ModalPresenter :open="showCoursePicker" @request-close="closeCoursePicker">
      <CoursePickerModal @add="courseSelected" />
    </ModalPresenter>
  </div>
</template>

<script>
import AddIcon from 'tui/components/icons/Add';
import Button from 'tui/components/buttons/Button';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import Checkbox from 'tui/components/form/Checkbox';
import CourseCard from 'perform_goal/components/tasks/PerformGoalTaskCourseCard';
import CoursePickerModal from 'core_course/components/course_picker/CoursePickerModal';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import { FormScope, FormText, Uniform } from 'tui/components/uniform';

const courseTypeCode = 1;

export default {
  components: {
    AddIcon,
    Button,
    ButtonIcon,
    Checkbox,
    CourseCard,
    CoursePickerModal,
    FormScope,
    FormText,
    ModalPresenter,
    Uniform,
  },

  props: {
    // Existing form data
    data: { type: Object },
    // Waiting on any task query
    loading: { type: Boolean },
    // Waiting on task request
    saving: { type: Boolean },
  },

  emits: ['submit', 'cancelled', 'add-cancelled'],

  data() {
    return {
      // is the existing task completed
      completed: false,
      // Editing an existing task
      editing: false,
      // Has the user started interacting with task
      formActive: false,
      // Has attached resource (available or not)
      hasAttachedResource: false,
      // Check for any changes to form fields
      hasChanges: false,
      // Form data
      initialValues: {
        description: { create: '', edit: '' },
        resource_type: null,
        resource_id: null,
      },
      // The resource selected to be added to this task
      linkedResourcePreview: null,
      // Show course picker
      showCoursePicker: false,
    };
  },

  watch: {
    /**
     * When the task has been saved reset the form
     *
     * @param {Boolean} value
     */
    saving(value) {
      if (!value && this.formActive) {
        this.$nextTick(() => {
          this.hasChanges = false;
          this.$refs.form.reset();
          this.$refs.form.focus();
          this.hasAttachedResource = false;
          this.linkedResourcePreview = null;
        });
      }
    },
  },

  created() {
    // If editing an existing task populate form with data
    if (typeof this.data === 'object') {
      this.editing = true;
      this.formActive = true;
      this.completed = this.data.completed;

      // form fields
      this.initialValues = {
        description: { edit: this.data.description },
        resource_type: this.data.resource_type || null,
        resource_id: this.data.resource_id || null,
      };

      // preview values
      if (this.data.hasAttachedResource) {
        this.hasAttachedResource = true;
        this.linkedResourcePreview = this.data.resourceData;
      }
    }
  },

  mounted() {
    if (this.editing) {
      // Set focus to field
      this.$refs.form.focus();
    }
  },

  methods: {
    /**
     * Reset the task form fields
     *
     */
    clearTask() {
      if (this.editing) {
        this.$emit('cancelled');
      } else {
        this.$emit('add-cancelled');

        this.$nextTick(() => {
          this.hasChanges = false;
          this.$refs.form.reset();
          this.formActive = false;
          this.hasAttachedResource = false;
          this.linkedResourcePreview = null;
        });
      }
    },

    /**
     * Hide UI for selecting course as a linked resource
     */
    closeCoursePicker() {
      this.showCoursePicker = false;
    },

    /**
     * Add selected course as the linked resource
     *
     * @param {Object} course The selected course data
     */
    courseSelected(course) {
      this.closeCoursePicker();
      this.linkResource(courseTypeCode, course.id, course);
    },

    /**
     * Check if the form fields have any changes
     *
     * @param {Object} values
     */
    hasFormChanges(values) {
      let initDesc;
      let desc;

      if (this.editing) {
        initDesc = this.initialValues.description.edit;
        desc = values.description.edit ? values.description.edit.trim() : '';
      } else {
        initDesc = this.initialValues.description.create;
        desc = values.description.create
          ? values.description.create.trim()
          : '';
      }

      if (desc.length === 0 && !values.resource_id) {
        // no values
        this.hasChanges = false;
      } else if (
        desc === initDesc &&
        values.resource_type === this.initialValues.resource_type &&
        values.resource_id === this.initialValues.resource_id
      ) {
        // no changes
        this.hasChanges = false;
      } else {
        this.hasChanges = true;
      }
    },

    /**
     * Focus has been set to the add fields, show additional content
     *
     */
    interactedWithForm() {
      this.formActive = true;
      this.$nextTick(() => {
        if (this.$refs.form && !this.editing) {
          this.$refs.form.$el.scrollIntoView();
        }
      });
    },

    /**
     * Add a resource to the task
     *
     * @param {Int} type type of resource
     * @param {String} id resource ID
     * @param {Object} resource
     */
    linkResource(type, id, resource) {
      this.$refs.form.update('resource_type', type);
      this.$refs.form.update('resource_id', id);
      this.hasAttachedResource = true;
      this.linkedResourcePreview = Object.assign({}, resource);
      // Ensure access checks pass for new resources
      this.linkedResourcePreview.resource_exists = true;
      this.linkedResourcePreview.resource_can_view = true;
      this.$refs.form.focus();
    },

    /**
     * Display UI for selecting course as a linked resource
     */
    openCoursePicker() {
      this.showCoursePicker = true;
    },

    /**
     * Remove resource from task
     */
    removeResource() {
      this.hasAttachedResource = false;
      this.linkedResourcePreview = null;
      // Clear form resource values
      this.$refs.form.update('resource_type', null);
      this.$refs.form.update('resource_id', null);

      this.$nextTick(() => {
        // Set focus back on add course button
        let courseButton = this.$refs['add-course'];
        if (courseButton) {
          courseButton.$el.focus();
        }
      });
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalDetailsTaskForm {
  display: flex;

  &__checkbox {
    margin-top: var(--gap-2);
  }

  &__content {
    display: flex;
    flex-grow: 1;
    gap: var(--gap-4);
    justify-content: space-between;
  }

  &__form {
    display: flex;
    flex-direction: column;
    gap: var(--gap-3);
    width: 100%;

    &-input {
      display: flex;
      flex-direction: column;
      flex-grow: 1;
      gap: var(--gap-2);
    }

    &-bar {
      display: flex;
      flex-wrap: wrap;
      gap: var(--gap-3);
      justify-content: space-between;
      margin: 0;
    }

    &-barButtons {
      display: flex;
      flex-grow: 1;
      gap: var(--gap-4);
      justify-content: flex-end;
      margin-top: auto;
    }

    &-barResource {
      display: flex;
      flex-grow: 1;
    }

    &-barResourceCard {
      min-width: 234px;
      max-width: 340px;
    }
  }
}
</style>
