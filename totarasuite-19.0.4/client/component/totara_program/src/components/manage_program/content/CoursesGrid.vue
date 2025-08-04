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
  <div class="tui-totara_program-coursesGrid">
    <component
      :is="editing ? 'Droppable' : 'passthrough'"
      v-slot="{ attrs, events }"
      :source-id="$id('course-set')"
      :source-name="
        $str('coursesincoursesetx', 'totara_program', courseSetName)
      "
      layout-interaction="grid-line"
      :reorder-only="true"
      axis="horizontal"
      @drop="handleDrop"
    >
      <div
        v-bind="attrs"
        class="tui-totara_program-coursesGrid__container"
        v-on="events || {}"
      >
        <div v-if="editing" class="tui-totara_program-coursesGrid__addCourse">
          <AddCourses
            class="tui-totara_program-coursesGrid__addCourseCard"
            :existing-courses="existing"
            :tenant-id="tenantId"
            @add="addCourses"
          />
        </div>
        <template v-for="(course, index) in courses" :key="course.id">
          <component
            :is="editing ? 'Draggable' : 'passthrough'"
            v-slot="{ dragging, attrs, events, anyDragging }"
            :index="index"
            type="course"
            :value="course"
          >
            <div
              v-bind="attrs"
              :class="[
                'tui-totara_program-coursesGrid__course',
                dragging && 'tui-totara_program-coursesGrid__course--dragging',
              ]"
              v-on="events || {}"
            >
              <LearningCard
                :title="course.fullname"
                :image="course.image"
                :actions="getActions(course.id)"
              >
                <template v-slot:hero="{ popFront }">
                  <DragHandleIcon
                    v-if="editing && (!anyDragging || dragging)"
                    :class="[
                      'tui-totara_program-coursesGrid__courseDrag',
                      popFront,
                    ]"
                  />
                </template>
              </LearningCard>
            </div>
          </component>
        </template>
      </div>
    </component>
  </div>
</template>
<script>
import AddCourses from 'totara_program/components/manage_program/content/AddCourses';
import Draggable from 'tui/components/drag_drop/Draggable';
import Droppable from 'tui/components/drag_drop/Droppable';
import DragHandleIcon from 'tui/components/icons/DragHandle';
import LearningCard from 'tui/components/card/LearningCard';

export default {
  components: {
    AddCourses,
    Draggable,
    Droppable,
    DragHandleIcon,
    LearningCard,
  },

  props: {
    courses: {
      type: Array,
      default() {
        return [];
      },
    },

    courseSetName: String,
    editing: Boolean,
    tenantId: {
      required: false,
      default: null,
      type: [String, Number],
    },
  },

  emits: ['add-courses', 'move-course', 'remove-course'],

  computed: {
    existing() {
      return this.courses.map(course => {
        return course.id;
      });
    },
  },

  methods: {
    addCourses(courses) {
      this.$emit('add-courses', courses.data);
    },

    handleDrop(d) {
      this.$emit('move-course', d.item.value, d.destination.index);
    },

    getActions(id) {
      if (this.editing) {
        return [
          {
            label: this.$str('removecourse', 'totara_program'),
            inMenu: true,
            persistent: false,
            onClick: () => this.$emit('remove-course', id),
          },
        ];
      }
      return [];
    },
  },
};
</script>
<style lang="scss">
.tui-totara_program-coursesGrid {
  padding: var(--gap-2);

  &__container {
    position: relative;
    display: grid;
    grid-template-columns: repeat(
      auto-fill,
      minmax(min(var(--tui-card-default-width), 100%), 1fr)
    );
    gap: var(--gap-4);
  }

  &__addCourseCard {
    min-width: var(--tui-card-default-width);
    height: 100%;
  }

  &__courseDrag {
    position: absolute;
    top: var(--gap-2);
    left: var(--gap-2);
    display: none;
  }

  &__course:hover &__courseDrag,
  &__course--dragging &__courseDrag {
    display: block;
  }
}
</style>
