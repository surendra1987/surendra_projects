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
  <span v-if="showPlainName">{{ name }}</span>
  <a v-else :href="url">{{ name }}</a>
</template>

<script>
export default {
  props: {
    value: String,
  },

  computed: {
    parsedValue() {
      if (!this.value) {
        return null;
      }
      try {
        const data = JSON.parse(this.value);
        return data;
      } catch (e) {
        return null;
      }
    },

    url() {
      return (this.parsedValue && this.parsedValue.url) || '#';
    },

    showPlainName() {
      if (!this.parsedValue) {
        return true;
      }

      return this.parsedValue.course_deleted ?? false;
    },

    name() {
      return (
        (this.parsedValue && this.parsedValue.name) ||
        this.$str('course_name_placeholder', 'approvalform_enrol')
      );
    },
  },
};
</script>
