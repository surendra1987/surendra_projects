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

  @author Simon Chester <simon.chester@totara.com>
  @module format_pathway
-->

<template>
  <ActivityView
    v-if="mainContent"
    :course-id="parseInt(pageProps.courseid)"
    :exclude-nav="Boolean(pageProps.excludeNav)"
    :on-activity-page="pageProps.onActivityPage"
    :main-content="mainContent"
    :active-course-module-id="pageProps.cmid"
    :back-link-text="pageProps.backLinkText"
    :back-link-url="pageProps.backLinkUrl"
  />
</template>

<script>
import ActivityView from 'format_pathway/pages/ActivityView';
export default {
  components: {
    ActivityView,
  },

  props: {
    pageProps: Object,
    templateId: String,
  },

  data() {
    return {
      mainContent: null,
    };
  },

  created() {
    this.readDOM();
  },

  methods: {
    readDOM() {
      this.mainContent = document.getElementById(this.templateId).innerHTML;
    },
  },
};
</script>
