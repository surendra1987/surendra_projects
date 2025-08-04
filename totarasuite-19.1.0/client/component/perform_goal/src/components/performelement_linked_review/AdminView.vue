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

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module perform_goal
-->

<template>
  <ParticipantContent
    v-if="!$apollo.loading"
    :content="getPreviewData()"
    :preview="true"
  />
</template>

<script>
import ParticipantContent from 'perform_goal/components/performelement_linked_review/ParticipantContent';

// GraphQL
import dateTodayQuery from 'totara_webapi/graphql/status';

export default {
  components: {
    ParticipantContent,
  },

  data() {
    return {
      dateToday: '01/01/1970',
    };
  },

  apollo: {
    dateToday: {
      query: dateTodayQuery,
      update({ totara_webapi_status }) {
        return totara_webapi_status.date;
      },
    },
  },

  methods: {
    /**
     * Set placeholder data for preview view
     */
    getPreviewData() {
      return {
        description: this.$str('goal_example_goal_description', 'perform_goal'),
        name: this.$str('goal_example_goal_title', 'perform_goal'),
        status: {
          label: this.$str('goal_example_goal_status', 'perform_goal'),
        },
        target_date: this.dateToday,
        updated_at: this.dateToday,
      };
    },
  },
};
</script>
