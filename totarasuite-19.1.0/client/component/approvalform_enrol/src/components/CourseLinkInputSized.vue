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

  @author Jack Humphrey <jack.humphrey@totara.com>
  @module approvalform_enrol
-->

<template>
  <InputSizedText>
    <CourseLink :value="value" />
  </InputSizedText>
</template>

<script>
import CourseLink from 'approvalform_enrol/components/CourseLink';
import InputSizedText from 'tui/components/form/InputSizedText';

export default {
  components: {
    InputSizedText,
    CourseLink,
  },

  props: {
    value: String,
  },
};
</script>
