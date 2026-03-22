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

  @author Brian Barnes <brian.barnes@totara.com>
  @module core_course
-->

<template>
  <div class="tui-samplesCourseAdder">
    An adder component for selecting Courses

    <SamplesExample>
      <Button text="Add Courses" @click="adderOpen" />

      <CourseAdder
        :existing-items="addedIds"
        :open="showAdder"
        :show-loading-btn="showAddButtonSpinner"
        @added="adderUpdate"
        @add-button-clicked="toggleLoading"
        @cancel="adderCancelled"
      />

      <div class="tui-samplesIndividualsAdder__selected">
        <h4>Selected Items:</h4>
        <div v-for="course in addedCourses" :key="course.id">
          {{ course }}
        </div>
      </div>
    </SamplesExample>

    <SamplesCtl>
      <FormRow label="Pre selected courses">
        <InputText v-model:value="preSelected" />
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import CourseAdder from 'core_course/components/course_adder/CourseAdder';
import Button from 'tui/components/buttons/Button';
import InputText from 'tui/components/form/InputText';
import FormRow from 'tui/components/form/FormRow';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';

export default {
  components: {
    Button,
    CourseAdder,
    InputText,
    FormRow,
    SamplesExample,
    SamplesCtl,
  },

  data() {
    return {
      addedCourses: [],
      preSelected: '',
      showAdder: false,
      showAddButtonSpinner: false,
    };
  },

  computed: {
    addedIds() {
      try {
        return JSON.parse(this.preSelected);
      } catch (e) {
        return [];
      }
    },
  },

  methods: {
    adderOpen() {
      this.showAdder = true;
    },

    adderCancelled() {
      this.showAdder = false;
    },

    adderUpdate(selection) {
      this.preSelected = JSON.stringify(selection.ids);
      this.addedCourses = selection.data;
      this.showAddButtonSpinner = false;
      this.showAdder = false;
    },

    toggleLoading() {
      this.showAddButtonSpinner = true;
    },
  },
};
</script>

<style lang="scss">
.tui-samplesCourseAdder {
  &__selected {
    & > * + * {
      margin-top: var(--gap-4);
    }
  }
}
</style>

<sample-template>
  <Button text="Add Courses" @click="adderOpen" />

  <CourseAdder
    :existing-items="addedIds"
    :open="showAdder"
    :show-loading-btn="showAddButtonSpinner"
    @added="adderUpdate"
    @add-button-clicked="toggleLoading"
    @cancel="adderCancelled"
  />

  <div class="tui-samplesIndividualsAdder__selected">
    <h4>Selected Items:</h4>
    <div v-for="course in addedCourses" :key="course.id">
      {{ course }}
    </div>
  </div>
</sample-template>
