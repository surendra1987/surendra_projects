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
  <div class="tui-totara_program-content">
    <CourseSet
      v-for="{
        id,
        label,
        courses,
        programid,
        sortorder,
        nextsetoperator,
        completiontype,
        mincourses,
        coursesumfield,
        coursesumfieldtotal,
        timeallowed,
      } in sortedCourseSets"
      :id="id"
      :key="id"
      :label="label"
      :courses="courses"
      :programid="programid"
      :sortorder="sortorder"
      :nextsetoperator="nextsetoperator"
      :completiontype="completiontype"
      :mincourses="mincourses"
      :coursesumfield="coursesumfield"
      :coursesumfieldtotal="coursesumfieldtotal"
      :timeallowed="timeallowed"
      :custom-fields="scoreFields"
      :editing-other="editingCourseSet"
      :tenant-id="program.tenant_id"
      :last="isLast(id)"
      :can-course-set-move-up="canCourseSetMove(sortorder).canMoveUp"
      :can-course-set-move-down="canCourseSetMove(sortorder).canMoveDown"
      @change-next-set="updateCourseSetOperator"
      @edit-changed="editChanged"
      @remove-course-set="removeCourseSet(id)"
      @update-course-set="updateCourseSet"
      @move-course-set="moveCourseSet"
    />
    <Button
      :text="$str('addcourseset', 'totara_program')"
      :disabled="editingCourseSet"
      @click="addCourseSet"
    />
  </div>
</template>
<script>
import Button from 'tui/components/buttons/Button';
import CourseSet from 'totara_program/components/manage_program/content/CourseSet';
import { notify } from 'tui/notifications';

// GraphQL queries.
import moveCourseSet from 'totara_program/graphql/move_courseset';

export default {
  components: {
    Button,
    CourseSet,
  },

  props: {
    program: Object,
    scoreFields: Array,
  },

  data() {
    let courseSets = JSON.parse(JSON.stringify(this.program.coursesets));

    return {
      courseSets: courseSets,
      editingCourseSet: false,
    };
  },

  computed: {
    /**
     * Returns a sorted copy of the courseSets array,
     * sorted in ascending order by the sortorder property of each element.
     * @returns {Array} A sorted copy of the courseSets array.
     */
    sortedCourseSets() {
      return [...this.courseSets].sort((a, b) => {
        if (a.sortorder && b.sortorder) {
          return a.sortorder - b.sortorder;
        }
        return 0;
      });
    },
    /**
     * Returns the maximum sort order of the course sets in the program.
     * @returns {number} The maximum sort order or 0 if there are no course sets.
     */
    maxSortOrder() {
      if (this.sortedCourseSets.length > 0) {
        return this.sortedCourseSets[this.sortedCourseSets.length - 1]
          .sortorder;
      } else {
        return 0;
      }
    },
    /**
     * Returns the minimum sort order of the course sets in the program.
     * @returns {number} The minimum sort order or 0 if there are no course sets.
     */
    minSortOrder() {
      if (this.sortedCourseSets.length > 0) {
        return this.sortedCourseSets[0].sortorder;
      } else {
        return 0;
      }
    },
  },

  methods: {
    /**
     * Add a standard course set
     */
    addCourseSet() {
      this.courseSets.push({
        id: null,
        programid: this.program.id,
        label: this.$str(
          'legend:courseset',
          'totara_program',
          this.courseSets.length + 1
        ),
        timeallowed: '0',
        mincourses: '0',
        completiontype: 'ALL',
        coursesumfieldtotal: '0',
        coursesumfield: '0',
        nextsetoperator: 'NEXTSETOPERATOR_THEN',
        courses: [],
        sortorder: Number(this.maxSortOrder) + 1,
      });
      this.editingCourseSet = true;
    },
    /**
     * @param {number} id - The ID of the course set to be removed
     * @return {void}
     */
    removeCourseSet(id) {
      this.courseSets = this.courseSets.filter(courseSet => {
        return courseSet.id !== id;
      });
      if (id === null) {
        this.editingCourseSet = false;
      }
      this.resetSortOrder();
    },
    /**
     * @param val
     */
    editChanged(val) {
      this.editingCourseSet = val;
    },
    /**
     * Updates a course set with the provided partialCourseSet data.
     * @param {Object} partialCourseSet - An object representing the partial course set data to update.
     */
    updateCourseSet(partialCourseSet) {
      // Get the recently added\update course set and refresh courseSets.
      this.courseSets = this.courseSets.map(courseSet => {
        if (courseSet.id === partialCourseSet.id) {
          courseSet.label = partialCourseSet.label;
          courseSet.courses = partialCourseSet.courses;
          courseSet.completiontype = partialCourseSet.completiontype;
          courseSet.mincourses = partialCourseSet.mincourses;
          courseSet.coursesumfield = partialCourseSet.coursesumfield;
          courseSet.coursesumfieldtotal = partialCourseSet.coursesumfieldtotal;
          courseSet.timeallowed = partialCourseSet.timeallowed;
        } else if (courseSet.id == null) {
          courseSet.id = partialCourseSet.id;
          courseSet.courses = partialCourseSet.courses;
          courseSet.label = partialCourseSet.label;
          courseSet.completiontype = partialCourseSet.completiontype;
          courseSet.mincourses = partialCourseSet.mincourses;
          courseSet.coursesumfield = partialCourseSet.coursesumfield;
          courseSet.coursesumfieldtotal = partialCourseSet.coursesumfieldtotal;
          courseSet.timeallowed = partialCourseSet.timeallowed;
        }
        // Make sure that an array of course sets is in order in terms of each
        // set's sort order property and reset the sort order properties to ensure
        // that it begins from 1 and there are no gaps in the order.
        this.resetSortOrder();
        return courseSet;
      });
    },
    /**
     * @param id {number} - The ID of the course set to update.
     * @param operator {string} - The operator to set as the next set operator for the course set.
     */
    updateCourseSetOperator(id, operator) {
      let courseSet = this.courseSets.find(courseSet => courseSet.id == id);
      courseSet.nextsetoperator = operator;
    },
    isLast(id) {
      let last_course_Set = this.sortedCourseSets[
        this.sortedCourseSets.length - 1
      ];
      return last_course_Set.id == id;
    },
    /**
     * Determines if the current course set can be moved up or down within the list of course sets.
     * @param {number} currentSortOrder - The current sort order of the course set to check.
     * @returns {{ canMoveUp: boolean, canMoveDown: boolean }} - An object indicating if the course set can move up or down.
     */
    canCourseSetMove(currentSortOrder) {
      if (this.courseSets.length === 1) {
        return { canMoveUp: false, canMoveDown: false };
      } else {
        return {
          canMoveUp: currentSortOrder != this.minSortOrder,
          canMoveDown: currentSortOrder != this.maxSortOrder,
        };
      }
    },
    /**
     * Moves a course set either up or down in the program by updating its sort order property.
     * @async
     * @param {Object} arg - The argument object.
     * @param {string} arg.action - The direction to move the course set. Either "moveUp" or "moveDown".
     * @param {number} arg.current_sort_order - The current sort order of the course set.
     * @param {number} arg.id - The ID of the course set to be moved.
     */
    async moveCourseSet(arg) {
      const is_move_up = arg.action === 'moveUp';
      const { previous, next } = this.getAdjacentSortOrders(
        arg.current_sort_order
      );

      try {
        await this.$apollo
          .mutate({
            mutation: moveCourseSet,
            refetchAll: false,
            variables: {
              input: {
                id: Number(arg.id),
                program_id: this.program.id,
                new_sortorder: is_move_up ? Number(previous) : Number(next),
              },
            },
          })
          .then(({ data }) => {
            if (data.totara_program_move_courseset) {
              if (is_move_up) {
                this.courseSets = this.courseSets.map(courseSet => {
                  if (
                    courseSet.sortorder < arg.current_sort_order &&
                    courseSet.sortorder >= previous
                  ) {
                    courseSet.sortorder++;
                  }

                  if (courseSet.id === arg.id) {
                    courseSet.sortorder = previous;
                  }

                  return courseSet;
                });
              } else {
                this.courseSets = this.courseSets.map(courseSet => {
                  if (
                    courseSet.sortorder > arg.current_sort_order &&
                    courseSet.sortorder <= next
                  ) {
                    courseSet.sortorder--;
                  }

                  if (courseSet.id === arg.id) {
                    courseSet.sortorder = next;
                  }
                  return courseSet;
                });
              }
              // Make sure that an array of course sets is in order in terms of each
              // set's sort order property and reset the sort order properties to ensure
              // that it begins from 1 and there are no gaps in the order.
              this.resetSortOrder();
            } else {
              //Trigger tui error notification.
              notify({
                message: this.$str('error:move_course_set', 'totara_program'),
                type: 'error',
              });
            }
          });
      } catch (e) {
        // Trigger tui error notification.
        await notify({
          message: this.$str('error:move_course_set', 'totara_program'),
          type: 'error',
        });
      }
    },
    /**
     * Returns the previous and next sort orders relative to the current sort order.
     *
     * @param {number} current - The current sort order.
     * @returns {Object} An object containing the previous and next sort orders.
     */
    getAdjacentSortOrders(current) {
      const sortOrders = this.sortedCourseSets.map(
        courseSet => courseSet.sortorder
      );
      const currentIndex = sortOrders.indexOf(current);
      let previous = null;
      let next = null;

      if (currentIndex > -1) {
        previous =
          currentIndex > 0 ? sortOrders[currentIndex - 1] : this.minSortOrder;
        next =
          currentIndex < sortOrders.length - 1
            ? sortOrders[currentIndex + 1]
            : this.maxSortOrder;
      }

      return { previous, next };
    },
    resetSortOrder() {
      // Make sure that an array of course sets is in order in terms of each
      // set's sort order property and reset the sort order properties to ensure
      // that it begins from 1 and there are no gaps in the order.
      let count = 1;

      this.courseSets = this.sortedCourseSets.map(courseSet => {
        courseSet.sortorder = count;
        count++;
        return courseSet;
      });
    },
  },
};
</script>
<style lang="scss">
.tui-totara_program-content {
  & > * + * {
    margin-top: var(--gap-8);
  }
}
</style>
