<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Brian Barnes <brian.barnes@totara.com>
  @module totara_program
-->
<template>
  <div class="tui-totara_program-courseSet">
    <Card :clickable="false">
      <div class="tui-totara_program-courseSet__card">
        <div v-if="!editing" class="tui-totara_program-courseSet__cardHeading">
          <h2 class="tui-totara_program-courseSet__cardHeadingText">
            {{ label }}
          </h2>
          <CourseSetAction
            v-if="!editing && !editingOther"
            :is-deletable="true"
            :course-set-title="label"
            :can-course-set-move-down="canCourseSetMoveDown"
            :can-course-set-move-up="canCourseSetMoveUp"
            class="tui-totara_program-courseSet__cardHeadingAction"
            @edit-course-set="editing = true"
            @delete-course-set="confirmDeleteCourseSet"
            @move-course-set-up="handleMoveCourseSet('moveUp')"
            @move-course-set-down="handleMoveCourseSet('moveDown')"
          />
        </div>
        <div v-if="!editing" class="tui-totara_program-courseSet__cardSummary">
          <Tag
            :bold="true"
            :large="true"
            :no-border="true"
            :label="$str('required', 'core') + ':'"
            :text="requiredData"
          />
          <Tag
            v-if="showMinimumScore"
            :bold="true"
            :large="true"
            :no-border="true"
            :label="$str('minimumscore', 'totara_program') + ':'"
            :text="coursesumfieldtotal.toString()"
          />
          <Tag
            v-if="timeallowed && timeallowed != 0"
            :bold="true"
            :large="true"
            :no-border="true"
            :label="$str('minimumtimerequired', 'totara_program') + ':'"
            :text="timeRequired"
          />
        </div>
        <CourseSetForm
          v-if="editing"
          class="tui-totara_program-courseSet__cardForm"
          :custom-fields="customFields"
          :form-data="formData"
          :server-courses="courses"
          :tenant-id="tenantId"
          @submit="handleSubmit"
          @cancel="handleCancel"
        />
        <CoursesGrid
          v-if="!editing"
          :can-add="false"
          :courses="sortedCourses"
        />
      </div>
      <DeleteConfirmationModal
        :open="deleteModalOpen"
        :title="deleteCourseSetTitle"
        :confirm-button-text="$str('delete', 'core')"
        :loading="deleting"
        @confirm="deleteCourseSet"
        @cancel="deleteModalOpen = false"
      >
        <p>
          {{ $str('delete_course_set_confirm_message', 'totara_program') }}
        </p>
      </DeleteConfirmationModal>
    </Card>
    <ToggleSet
      v-if="!last"
      :value="nextsetoperator"
      :aria-label="$str('label:nextsetoperatorfor', 'totara_program', label)"
      @input="toggleChange"
    >
      <ToggleButton
        value="NEXTSETOPERATOR_THEN"
        :text="$str('then', 'totara_program')"
        :aria-label="$str('then', 'totara_program')"
      />
      <ToggleButton
        value="NEXTSETOPERATOR_OR"
        :text="$str('or', 'totara_program')"
        :aria-label="$str('or', 'totara_program')"
      />
      <ToggleButton
        value="NEXTSETOPERATOR_AND"
        :text="$str('and', 'totara_program')"
        :aria-label="$str('and', 'totara_program')"
      />
    </ToggleSet>
  </div>
</template>
<script>
import Card from 'tui/components/card/Card';
import CoursesGrid from 'totara_program/components/manage_program/content/CoursesGrid';
import CourseSetAction from 'totara_program/components/manage_program/action/CourseSetAction';
import CourseSetForm from 'totara_program/components/manage_program/content/CourseSetForm';
import DeleteConfirmationModal from 'tui/components/modal/ConfirmationModal';
import { notify } from 'tui/notifications';
import ToggleSet from 'tui/components/toggle/ToggleSet';
import ToggleButton from 'tui/components/toggle/ToggleButton';
import Tag from 'tui/components/tag/Tag';

// GraphQL queries.
import deleteCourseSet from 'totara_program/graphql/delete_courseset';
import saveCourseSet from 'totara_program/graphql/save_courseset';
import updateOperator from 'totara_program/graphql/update_courseset_nextset_operator';

export default {
  components: {
    Card,
    CoursesGrid,
    CourseSetAction,
    CourseSetForm,
    DeleteConfirmationModal,
    ToggleSet,
    ToggleButton,
    Tag,
  },

  props: {
    id: [String, Number],
    label: String,
    courses: Array,
    customFields: Array,
    programid: [String, Number],
    sortorder: [String, Number],
    nextsetoperator: String,
    completiontype: String,
    mincourses: [String, Number],
    coursesumfield: [String, Number],
    coursesumfieldtotal: [String, Number],
    timeallowed: [String, Number],
    editingOther: Boolean,
    last: Boolean,
    canCourseSetMoveUp: Boolean,
    canCourseSetMoveDown: Boolean,
    tenantId: {
      required: false,
      default: null,
      type: [String, Number],
    },
  },

  emits: [
    'edit-changed',
    'change-next-set',
    'remove-course-set',
    'update-course-set',
    'move-course-set',
  ],

  data() {
    return {
      deleteModalOpen: false,
      deleting: false,
      editing: this.courses.length == 0,
      canChangeOperator: true,
    };
  },

  computed: {
    deleteCourseSetTitle() {
      // When label is empty.
      let title = this.$str(
        'delete_course_set_confirm_title',
        'totara_program'
      );
      if (this.label) {
        title = this.$str(
          'delete_course_set_confirm_title_for',
          'totara_program',
          this.label
        );
      }
      return title;
    },

    formData() {
      let data = {
        label: this.label,
        completiontype: this.completiontype,
        mincourses: this.mincourses,
        coursesumfield: this.coursesumfield,
        coursesumfieldtotal: this.coursesumfieldtotal,
        timeallowed: this.timeallowed,
      };
      return data;
    },

    sortedCourses() {
      return [...this.courses].sort((a, b) => {
        if (a.sortorder && b.sortorder) {
          return a.sortorder - b.sortorder;
        }
        return 0;
      });
    },

    requiredData() {
      switch (this.completiontype) {
        case 'ALL':
          return this.$str('allcourses', 'totara_program');
        case 'SOME':
          return this.$str('xcourses', 'totara_program', this.mincourses);
        case 'ANY':
          return this.$str('xcourses', 'totara_program', 1);
        case 'OPTIONAL':
          return this.$str('none', 'totara_core');
        default:
          return '';
      }
    },

    timeRequired() {
      let amount = this.timeallowed / (24 * 3600);

      if (amount % 365 === 0) {
        return this.$str('xyears', 'totara_program', amount / 365);
      } else if (amount % 30 === 0) {
        return this.$str('xmonths', 'totara_program', amount / 30);
      } else if (amount % 7 === 0) {
        return this.$str('xweeks', 'totara_program', amount / 7);
      } else {
        return this.$str('xdays', 'totara_program', amount);
      }
    },

    showMinimumScore() {
      if (this.completiontype === 'SOME') {
        return this.coursesumfield !== undefined && this.coursesumfield != 0;
      } else {
        return false;
      }
    },
  },

  watch: {
    editing(newVal) {
      this.$emit('edit-changed', newVal);
    },

    last(newVal) {
      if (!newVal) {
        this.canChangeOperator = false;
      }
    },

    editingOther(newVal) {
      if (!newVal) {
        // Update the next set operator on the server if it had changed client side
        this.canChangeOperator = true;
        if (this.nextsetoperator !== 'NEXTSETOPERATOR_THEN' && !this.last) {
          this.toggleChange(this.nextsetoperator);
        } else {
          this.$emit('change-next-set', this.id, 'NEXTSETOPERATOR_THEN');
        }
      }
    },
  },

  methods: {
    confirmDeleteCourseSet() {
      this.deleteModalOpen = true;
    },

    /**
     * @returns {Promise<void>}
     */
    async deleteCourseSet() {
      // If the modal is in the process of closing, skip
      if (!this.deleteModalOpen) {
        return;
      }

      this.deleting = true;
      await this.handleDeleteCourseSet();

      this.deleteModalOpen = false;
      this.deleting = false;
    },

    /**
     * @returns {Promise<void>}
     */
    async handleDeleteCourseSet() {
      try {
        await this.$apollo
          .mutate({
            mutation: deleteCourseSet,
            refetchAll: false,
            variables: {
              input: {
                id: this.id,
                program_id: this.programid,
              },
            },
          })
          .then(({ data }) => {
            if (data.totara_program_delete_courseset) {
              // Remove course set from the UI
              this.$emit('remove-course-set');
              // Trigger tui success notification.
              notify({
                type: 'success',
                message: this.$str(
                  'delete_course_set_success',
                  'totara_program'
                ),
              });
            } else {
              // Trigger tui error notification.
              notify({
                message: this.$str('error:delete_course_set', 'totara_program'),
                type: 'error',
              });
            }
          });
      } catch (e) {
        // Trigger tui error notification.
        await notify({
          message: this.$str('error:delete_course_set', 'totara_program'),
          type: 'error',
        });
      }
    },

    /**
     * Handles the submission of a course set form asynchronously.
     * @param {Object} courseSetForm - The course set form data.
     * @returns {Promise<void>}
     */
    async handleSubmit(courseSetForm) {
      this.editing = false;
      try {
        await this.$apollo
          .mutate({
            mutation: saveCourseSet,
            refetchAll: false,
            variables: {
              input: {
                id: this.id,
                label: courseSetForm.label,
                program_id: this.programid,
                course_ids: courseSetForm.courses.map(course => course.id),
                completion_type: courseSetForm.completion_type,
                min_courses: courseSetForm.min_courses,
                course_sum_field: courseSetForm.course_sum_field,
                course_sum_field_total: courseSetForm.course_sum_field_total,
                time_allowed: courseSetForm.time_allowed,
                sort_order: parseInt(this.sortorder),
              },
            },
          })
          .then(({ data }) => {
            if (data.totara_program_save_courseset) {
              this.$emit('update-course-set', {
                id: data.totara_program_save_courseset.id,
                label: data.totara_program_save_courseset.label,
                courses: courseSetForm.courses,
                completiontype: courseSetForm.completion_type,
                mincourses: courseSetForm.min_courses,
                coursesumfield: courseSetForm.course_sum_field,
                coursesumfieldtotal: courseSetForm.course_sum_field_total,
                timeallowed: courseSetForm.time_allowed,
              });
              // Trigger tui success notification.
              notify({
                message: this.$str('save_course_set_success', 'totara_program'),
                type: 'success',
              });
            } else {
              // Trigger tui error notification.
              notify({
                message: this.$str('error:save_course_set', 'totara_program'),
                type: 'error',
              });
            }
          });
      } catch (e) {
        // Trigger tui error notification.
        await notify({
          message: this.$str('error:save_course_set', 'totara_program'),
          type: 'error',
        });
      }
    },
    handleMoveCourseSet(action) {
      this.$emit('move-course-set', {
        id: this.id,
        current_sort_order: this.sortorder,
        action: action,
      });
    },
    handleCancel() {
      this.editing = false;
      if (this.id === null) {
        this.$emit('remove-course-set');
      }
    },

    async toggleChange(operator) {
      const oldState = this.nextsetoperator;
      this.$emit('change-next-set', this.id, operator);

      // if we can't change the next set operator, then don't send the mutation
      // By having the return here, it allows the watch on the editing other to send it later
      if (!this.canChangeOperator) {
        return;
      }

      try {
        let { data } = await this.$apollo.mutate({
          mutation: updateOperator,
          variables: {
            input: {
              program_id: this.programid,
              courseset_id: this.id,
              nextset_operator: operator,
            },
          },
        });

        if (!data.totara_program_update_courseset_nextset_operator) {
          this.revertNextSetOperator(oldState);
        }
      } catch (e) {
        this.revertNextSetOperator(oldState);
      }
    },

    async revertNextSetOperator(oldState) {
      this.$emit('change-next-set', this.id, oldState);
      await notify({
        type: 'error',
        message: this.$str(
          'error:unabletosavecourseset',
          'totara_program',
          this.label
        ),
      });
    },
  },
};
</script>
<style lang="scss">
.tui-totara_program-courseSet {
  &__card {
    flex-direction: column;
    width: 100%;
    padding: var(--gap-2);

    & > * + * {
      margin-top: var(--gap-2);
    }
  }

  &__cardHeading {
    display: flex;
  }

  &__cardHeadingText {
    flex-grow: 1;
    margin-top: 0;
  }

  &__cardForm {
    width: 100%;
  }

  & > * + * {
    margin-top: var(--gap-8);
  }
}
</style>
