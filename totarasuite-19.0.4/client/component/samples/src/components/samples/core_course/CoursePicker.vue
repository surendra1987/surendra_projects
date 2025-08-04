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
    {{ selectedCourse }}

    <SamplesExample>
      <CoursePicker @input="handlePickerChange" />
    </SamplesExample>
  </div>
</template>

<script>
import CoursePicker from 'core_course/components/course_picker/CoursePicker';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';

export default {
  components: {
    CoursePicker,
    SamplesExample,
  },

  data() {
    return {
      selectedCourse: {},
    };
  },

  methods: {
    handlePickerChange(value) {
      this.selectedCourse = value;
    },
  },
};
</script>
