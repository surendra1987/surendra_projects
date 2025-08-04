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

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @package core_my
-->

<template>
  <Layout
    class="tui-myPerformOverview"
    :flush-body="true"
    :title="$str('overview_page_title', 'core_my')"
  >
    <!-- Feedback notification banner -->
    <template v-if="showNotificationBanner" v-slot:feedback-banner>
      <div v-for="banner in banners" :key="banner.component">
        <NotificationBanner
          v-if="banner.show"
          :dismissable="false"
          :message="banner.message"
          :type="banner.type"
        />
      </div>
    </template>

    <!-- Profile card -->
    <template v-slot:user-overview>
      <MiniProfileCard
        v-if="!currentUsersOverview"
        :display="user.card_display"
      />
    </template>

    <!-- Top content -->
    <template v-slot:pre-body>
      <div class="tui-myPerformOverview__period">
        <!-- Period select -->
        <Label
          class="tui-myPerformOverview__period-label"
          :for-id="$id('periodSelect')"
          :label="$str('overview_period_label', 'core_my')"
        />

        <Select
          :id="$id('periodSelect')"
          v-model:value="period"
          char-length="10"
          :options="periodOptions"
        />
      </div>
    </template>

    <!-- Left content -->
    <template v-slot:left-content="{ stacked }">
      <div class="tui-myPerformOverview__content">
        <template v-if="stacked">
          <!-- Required actions -->
          <RequiredAction
            v-if="showRequiredAction"
            :activities-about-others="activitiesAboutOthersCount"
            :activities-about-others-overdue="activitiesAboutOthersOverdueCount"
            :current-users-overview="currentUsersOverview"
            :require-manual-participants-count="requireManualParticipantsCount"
            :show-activities-about-others="showActivitiesAboutOthers"
            :show-require-manual-participants="showRequireManualParticipants"
            :stacked-page="true"
            :user="user"
          />
        </template>

        <!-- Goals content -->
        <GoalsSection
          v-if="goalsEnabled"
          :current-users-overview="currentUsersOverview"
          :period="period"
          :stacked-page="stacked"
          :url="goalsUrl"
          :user-id="user.id"
        />

        <!-- Competencies content -->
        <CompetenciesSection
          v-if="competenciesEnabled"
          :assign-competencies-url="competenciesAssignUrl"
          :current-users-overview="currentUsersOverview"
          :period="period"
          :stacked-page="stacked"
          :url="competenciesUrl"
          :user-id="user.id"
          @display-banner="addBanner"
        />

        <!-- Activities content -->
        <ActivitiesSection
          v-if="activitiesEnabled"
          :period="period"
          :stacked-page="stacked"
          :url="activitiesUrl"
          :user-id="user.id"
        />
      </div>
    </template>

    <!-- Right content -->
    <template v-slot:right-content="{ stacked }">
      <div class="tui-myPerformOverview__side">
        <template v-if="!stacked">
          <!-- Required actions -->
          <RequiredAction
            v-if="showRequiredAction"
            :activities-about-others="activitiesAboutOthersCount"
            :activities-about-others-overdue="activitiesAboutOthersOverdueCount"
            :current-users-overview="currentUsersOverview"
            :require-manual-participants-count="requireManualParticipantsCount"
            :show-activities-about-others="showActivitiesAboutOthers"
            :show-require-manual-participants="showRequireManualParticipants"
            :user="user"
          />
        </template>
      </div>
    </template>
  </Layout>
</template>

<script>
import ActivitiesSection from 'core_my/components/overview/OverviewActivitiesSection';
import CompetenciesSection from 'core_my/components/overview/OverviewCompetenciesSection';
import GoalsSection from 'core_my/components/overview/OverviewGoalsSection';
import Label from 'tui/components/form/Label';
import Layout from 'core_my/components/layouts/PageLayoutTwoColumn';
import MiniProfileCard from 'tui/components/profile/MiniProfileCard';
import NotificationBanner from 'tui/components/notifications/NotificationBanner';
import RequiredAction from 'core_my/components/overview/OverviewRequiredAction';
import Select from 'tui/components/form/Select';

export default {
  components: {
    ActivitiesSection,
    CompetenciesSection,
    GoalsSection,
    Label,
    Layout,
    MiniProfileCard,
    NotificationBanner,
    RequiredAction,
    Select,
  },

  props: {
    // Count of activities about others
    activitiesAboutOthersCount: {
      type: Number,
    },
    // Count of overdue activities about others
    activitiesAboutOthersOverdueCount: {
      type: Number,
    },
    // Show the activity section
    activitiesEnabled: {
      type: Boolean,
    },
    // URL for current users activity page
    activitiesUrl: {
      type: String,
    },
    // URL to manually assign competencies
    competenciesAssignUrl: {
      type: String,
    },
    // Show the competency section
    competenciesEnabled: {
      type: Boolean,
    },
    // URL for displayed users competency page
    competenciesUrl: {
      type: String,
    },
    // Logged in users ID
    currentUserId: {
      type: Number,
    },
    // Show the goals section
    goalsEnabled: {
      type: Boolean,
    },
    // Link to displayed users goal page
    goalsUrl: {
      type: String,
    },
    // List of options for the period dropdown
    periodOptions: {
      type: Array,
    },
    // Number of activities this user needs to select participants in
    requireManualParticipantsCount: {
      type: Number,
    },
    // Displayed users data
    user: {
      type: Object,
    },
  },

  data() {
    return {
      // Notification banners
      banners: {},
      // Period filter value
      period: 14,
    };
  },

  computed: {
    /**
     * Is the current user viewing their own overview page
     *
     * @return {Boolean}
     */
    currentUsersOverview() {
      return this.user.id == this.currentUserId;
    },

    /**
     * Are activities enabled and are there activities about others
     *
     * @return {Boolean}
     */
    showActivitiesAboutOthers() {
      return this.activitiesEnabled && this.activitiesAboutOthersCount > 0;
    },

    /**
     * Is there at least one notification banner to be shown
     *
     * @return {Boolean}
     */
    showNotificationBanner() {
      for (const key in this.banners) {
        if (this.banners[key].show) {
          return true;
        }
      }
      return false;
    },

    /**
     * Are activities enabled and are there activities this user needs to select participants in
     *
     * @return {Boolean}
     */
    showRequireManualParticipants() {
      return this.activitiesEnabled && this.requireManualParticipantsCount > 0;
    },

    /**
     * Is there content to show on the required action section?
     *
     * @return {Boolean}
     */
    showRequiredAction() {
      return (
        this.showActivitiesAboutOthers || this.showRequireManualParticipants
      );
    },
  },

  methods: {
    /**
     * Update the collection of notification banner data
     *
     * @param {Object} banner
     */
    addBanner(banner) {
      this.banners[banner.component] = banner;
    },
  },
};
</script>

<style lang="scss">
.tui-myPerformOverview {
  &__period {
    display: flex;
    flex-wrap: wrap;

    &-label {
      margin: auto 0;
    }
  }

  &__content {
    & > * + * {
      margin-top: var(--gap-4);
    }
  }

  &__side {
    & > * + * {
      margin-top: var(--gap-4);
    }
  }
}
</style>
