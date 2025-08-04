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

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @module totara_perform
-->

<template>
  <!-- Visibility and closure tab -->
  <div class="tui-performManageActivityVisibilityClosure">
    <Form>
      <!-- Visibility Setting -->
      <VisibilitySetting
        v-if="settingData.visibility"
        :activity-id="activityId"
        :data="settingData.visibility"
        @updated="handleUpdate"
      />

      <ClosureSetting
        v-if="settingData.closure"
        :activity-id="activityId"
        :data="settingData.closure"
        @updated="handleUpdate"
      />
    </Form>
  </div>
</template>

<script>
import ClosureSetting from 'mod_perform/components/manage_activity/settings/Closure';
import Form from 'tui/components/form/Form';
import VisibilitySetting from 'mod_perform/components/manage_activity/settings/Visibility';

// Util
import { notify } from 'tui/notifications';

// graphQL
import activityControls from 'mod_perform/graphql/activity_controls';

export default {
  components: {
    ClosureSetting,
    Form,
    VisibilitySetting,
  },

  props: {
    activityId: {
      required: true,
      type: [Number, String],
    },
  },

  data() {
    return {
      // Setting data populated from query
      settingData: '',
    };
  },

  apollo: {
    settingData: {
      query: activityControls,
      fetchPolicy: 'network-only', // Always refetch data on tab change
      variables() {
        return {
          input: {
            activity_id: this.activityId,
            control_keys: ['closure', 'visibility'],
          },
        };
      },
      update({ mod_perform_activity_controls: data }) {
        if (data.controls) {
          data = JSON.parse(data.controls);
        }
        return data;
      },
    },
  },

  methods: {
    /**
     * Handle when a setting has been updated
     *
     */
    handleUpdate() {
      // Setting saved success toast
      notify({
        message: this.$str('toast_success_activity_update', 'mod_perform'),
        type: 'success',
      });

      // As a setting change can impact others re-request the data query
      this.$apollo.queries.settingData.refetch();
    },
  },
};
</script>
