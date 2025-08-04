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
  @module samples
-->

<template>
  <div class="tui-samplesCoursePicker">
    <SamplesExample>
      <div v-if="selectedCourse" class="tui-samplesCoursePicker__selected">
        <p>Selected course:</p>
        {{ selectedCourse }}
      </div>
      <div v-else>
        <p>No course selected</p>
      </div>

      <Button text="Pick Course" @click="openPicker" />

      <ModalPresenter :open="showPicker" @request-close="hidePicker">
        <CoursePickerModal @add="handleAdd" />
      </ModalPresenter>
    </SamplesExample>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import CoursePickerModal from 'core_course/components/course_picker/CoursePickerModal';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';

export default {
  components: {
    Button,
    CoursePickerModal,
    ModalPresenter,
    SamplesExample,
  },

  data() {
    return {
      selectedCourse: null,
      showPicker: false,
    };
  },

  methods: {
    handleAdd(value) {
      this.hidePicker();
      this.selectedCourse = value;
    },

    hidePicker() {
      this.showPicker = false;
    },

    openPicker() {
      this.showPicker = true;
    },
  },
};
</script>
