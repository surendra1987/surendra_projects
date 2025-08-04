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
  <div class="tui-totara_program-addCourses" @click="openAdder">
    <ButtonIcon
      :aria-label="$str('addcourses', 'totara_program')"
      :styleclass="{ circle: true, primary: true }"
    >
      <slot name="icon">
        <Add :size="400" />
      </slot>
    </ButtonIcon>
    <CourseAdder
      :existing-items="existingCourses"
      :open="showAdder"
      :show-loading-btn="loading"
      :custom-query-filters="{
        completion_tracked: true,
        tenant_id: tenantId,
      }"
      @added="addCourses"
      @add-button-clicked="toggleLoading"
      @cancel="cancelAdd"
    />
  </div>
</template>
<script>
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import Add from 'tui/components/icons/Add';
import CourseAdder from 'core_course/components/course_adder/CourseAdder';

export default {
  components: {
    ButtonIcon,
    Add,
    CourseAdder,
  },

  props: {
    existingCourses: Array,
    tenantId: {
      required: false,
      default: null,
      type: [String, Number],
    },
  },

  emits: ['add'],

  data() {
    return {
      showAdder: false,
      loading: false,
    };
  },

  methods: {
    openAdder() {
      this.showAdder = true;
    },

    addCourses(courses) {
      this.loading = false;
      this.showAdder = false;
      this.$emit('add', courses);
    },

    cancelAdd() {
      this.showAdder = false;
    },

    toggleLoading() {
      this.loading = true;
    },
  },
};
</script>
<style lang="scss">
.tui-totara_program-addCourses {
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px dashed var(--color-primary);
  border-radius: var(--card-border-radius);
}
</style>
