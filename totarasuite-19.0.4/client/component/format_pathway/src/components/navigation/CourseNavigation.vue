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

  @author Aaron Machin <aaron.machin@totara.com>
  @module format_pathway
-->

<template>
  <ProgressNavigation
    v-if="!$apollo.loading"
    :navigation-data="navigationData"
    :completion-data="completionData"
    :active-course-module-id="activeCourseModuleId"
    :can-view-hidden="canViewHidden"
  />
</template>

<script>
import ProgressNavigation from 'format_pathway/components/navigation/ProgressNavigation';

export default {
  name: 'CourseNavigation',

  components: {
    ProgressNavigation,
  },

  props: {
    activeCourseModuleId: Number,
    completionData: Array,
    navigationData: Object,
    canViewHidden: Boolean,
  },
};
</script>
