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
  <!-- Assignments tab -->
  <div class="tui-performManageActivityAssignments">
    <Form>
      <!-- Assignment Setting -->
      <AssignmentSetting
        v-if="settingData.assignments"
        :activity-id="activityId"
        :data="settingData.assignments"
        :loading="$apollo.loading"
        :track-id="settingData.default_track.track_id"
        @updated="handleUpdate"
      />

      <div class="tui-performManageActivityAssignments__participation">
        <h2 class="tui-performManageActivityAssignments__participation-title">
          {{ $str('participation_settings_heading', 'mod_perform') }}
        </h2>

        <SyncParticipation
          v-if="settingData.sync_participation"
          :activity-id="activityId"
          :data="settingData.sync_participation"
          @updated="handleUpdate"
        />

        <!-- Role for choosing participants setting  -->
        <ParticipantSelection
          v-if="settingData.manual_relationships"
          :activity-id="activityId"
          :data="settingData.manual_relationships"
          @updated="handleUpdate"
        />
      </div>
    </Form>
  </div>
</template>

<script>
import AssignmentSetting from 'mod_perform/components/manage_activity/settings/Assignment';
import Form from 'tui/components/form/Form';
import ParticipantSelection from 'mod_perform/components/manage_activity/settings/ParticipantSelectionRole';
import SyncParticipation from 'mod_perform/components/manage_activity/settings/SyncParticipation';

// Util
import { notify } from 'tui/notifications';

// graphQL
import activityControls from 'mod_perform/graphql/activity_controls';

export default {
  components: {
    AssignmentSetting,
    Form,
    ParticipantSelection,
    SyncParticipation,
  },

  props: {
    activityId: { required: true, type: [Number, String] },
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
      fetchPolicy: 'network-only',
      variables() {
        return {
          input: {
            activity_id: this.activityId,
            control_keys: [
              'assignments',
              'default_track',
              'manual_relationships',
              'sync_participation',
            ],
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

<style lang="scss">
.tui-performManageActivityAssignments {
  &__participation {
    display: flex;
    flex-direction: column;
    gap: var(--gap-4);
    margin-top: var(--gap-12);

    &-title {
      @include font(h3);
      margin: 0;
    }
  }
}
</style>
