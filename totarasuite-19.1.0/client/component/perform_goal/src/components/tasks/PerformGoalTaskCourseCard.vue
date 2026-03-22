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

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module perform_goal
-->
<template>
  <Card
    class="tui-performGoalTaskCourseCard"
    :clickable="hasCourse && hasUrl"
    @click="cardClick"
  >
    <!-- Course image or warning Icon -->
    <div v-if="!hasCourse" class="tui-performGoalTaskCourseCard__invalid">
      <InvalidIcon state="warning" :alt="$str('warning', 'core')" />
    </div>
    <div
      v-else
      class="tui-performGoalTaskCourseCard__image"
      :style="{ backgroundImage: `url(` + image + `)` }"
    />

    <!-- Course label and title -->
    <div class="tui-performGoalTaskCourseCard__content">
      <div class="tui-performGoalTaskCourseCard__content-label">
        <span class="sr-only">
          {{ $str('a11y_goal_task_resource_type', 'perform_goal') }}
        </span>
        {{ $str('learning_type_course', 'totara_core') }}
      </div>
      <div class="tui-performGoalTaskCourseCard__content-title" :title="title">
        <template v-if="!available">
          {{ $str('goal_task_course_not_available', 'perform_goal') }}
        </template>
        <template v-else-if="!hasAccess">
          {{ $str('goal_task_course_no_access', 'perform_goal') }}
        </template>
        <a
          v-else-if="!editing && hasUrl"
          class="tui-performGoalTaskCourseCard__content-titleLink"
          :href="url"
        >
          {{ title }}
        </a>
        <template v-else>
          {{ title }}
        </template>
      </div>
    </div>

    <!-- Close button or progress displayed in top right of card -->
    <div v-if="editing" class="tui-performGoalTaskCourseCard__closeButton">
      <CloseButton
        :aria-label="$str('a11y_goal_task_resource_remove', 'perform_goal')"
        :disabled="disabled"
        :no-padding="true"
        @click="$emit('remove')"
      />
    </div>
    <div
      v-else-if="typeof progress === 'number'"
      class="tui-performGoalTaskCourseCard__progress"
    >
      {{ $str('goal_task_course_progress', 'perform_goal', progress) }}
    </div>
  </Card>
</template>

<script>
import Card from 'tui/components/card/Card';
import CloseButton from 'tui/components/buttons/CloseIcon';
import InvalidIcon from 'tui/components/icons/Invalid';

export default {
  components: {
    Card,
    CloseButton,
    InvalidIcon,
  },

  props: {
    // Is the course available (still exists)
    available: { type: Boolean },
    // Disable actions
    disabled: { type: Boolean },
    // Is the card in edit mode?
    editing: { type: Boolean },
    // Does current user have access to course
    hasAccess: { type: Boolean },
    // The course image src
    image: { type: String },
    // Current course progress
    progress: { type: [Number, String] },
    // The name of the course
    title: { type: String },
    // URL of course
    url: { type: String },
  },

  emits: ['remove'],

  computed: {
    /**
     * Course is available for current user
     *
     * @return {Boolean}
     */
    hasCourse() {
      return this.available && this.hasAccess;
    },

    /**
     * Resource has been attached to task
     *
     * @return {Boolean}
     */
    hasUrl() {
      return this.url && this.url.length > 0;
    },
  },

  methods: {
    /**
     * Redirect to the card course
     */
    cardClick() {
      window.location.href = this.url;
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalTaskCourseCard {
  display: flex;
  gap: var(--gap-2);
  width: 100%;
  padding: var(--gap-2);

  &__image {
    min-width: 36px;
    height: 36px;
    background: var(--color-neutral-3);
    background-position: center;
    background-size: cover;
    border-radius: var(--border-radius-small);
  }

  &__invalid {
    display: flex;
    align-items: flex-end;
  }

  &__content {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    gap: var(--gap-1);
    width: 0;
    overflow: hidden;
    @include font(body-sm);

    &-label {
      color: var(--color-neutral-6);
    }

    &-title {
      overflow: hidden;
      color: var(--color-neutral-7);
      white-space: nowrap;
      text-overflow: ellipsis;
    }

    &-titleLink {
      color: var(--color-neutral-7);

      &:hover,
      &:focus {
        color: var(--color-neutral-7);
      }
    }
  }

  &__closeButton {
    margin-left: auto;
  }

  &__progress {
    margin-left: auto;
    @include font(body-sm);
    color: var(--color-neutral-6);
  }
}
</style>
