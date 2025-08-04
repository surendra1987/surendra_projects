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
  <Card :has-shadow="true">
    <div
      class="tui-myPerformOverviewRequiredAction"
      :class="{
        'tui-myPerformOverviewRequiredAction--stacked': stackedPage,
      }"
    >
      <h2 class="tui-myPerformOverviewRequiredAction__header">
        {{ $str('overview_require_action_section_title', 'core_my') }}
      </h2>

      <div class="tui-myPerformOverviewRequiredAction__body">
        <!-- Pending activity participant selection -->
        <RequiredActionSection
          v-if="showRequireManualParticipants"
          class="tui-myPerformOverviewRequiredAction__participantsSelection"
          :title="
            $str(
              'overview_activities_awaiting_participant_selection',
              'core_my'
            )
          "
        >
          <template v-slot:icon>
            <UsersIcon
              :aria-hidden="true"
              class="tui-myPerformOverviewRequiredAction__participantsSelection-icon"
              size="300"
            />
          </template>

          <template v-slot:content="{ stacked }">
            <RequiredActionSectionItem
              :has-action="currentUsersOverview"
              :stacked="stacked"
            >
              <template v-slot:text>
                {{ pendingParticipantSelectionString }}
              </template>

              <template v-slot:action>
                <ActionLink
                  :aria-label="
                    $str(
                      'a11y_overview_select_activity_participants',
                      'core_my'
                    )
                  "
                  :href="$url(participantSelectionURL)"
                  :styleclass="{ primary: true, small: true }"
                  :text="$str('overview_button_select', 'core_my')"
                />
              </template>
            </RequiredActionSectionItem>
          </template>
        </RequiredActionSection>

        <!-- Activities about others -->
        <RequiredActionSection
          v-if="showActivitiesAboutOthers"
          class="tui-myPerformOverviewRequiredAction__activitiesAboutOthers"
          :title="$str('overview_activities_actions_about_others', 'core_my')"
        >
          <template v-slot:icon>
            <ActivitiesIcon
              :aria-hidden="true"
              class="tui-myPerformOverviewRequiredAction__activitiesAboutOthers-icon"
              size="300"
            />
          </template>

          <template v-slot:content="{ stacked }">
            <RequiredActionSectionItem
              :has-action="currentUsersOverview"
              :stacked="stacked"
            >
              <template v-slot:text>
                {{ activitiesAboutOthersString }}
              </template>

              <template v-slot:action>
                <ActionLink
                  :aria-label="$str('a11y_overview_view_activities', 'core_my')"
                  :href="$url('/mod/perform/activity/index.php')"
                  :styleclass="{ small: true }"
                  :text="$str('overview_overdue_action_link_view', 'core_my')"
                />
              </template>
            </RequiredActionSectionItem>
          </template>
        </RequiredActionSection>
      </div>
    </div>
  </Card>
</template>

<script>
import ActionLink from 'tui/components/links/ActionLink';
import ActivitiesIcon from 'tui/components/icons/Activities';
import Card from 'tui/components/card/Card';
import RequiredActionSection from 'core_my/components/overview/OverviewRequiredActionSection';
import RequiredActionSectionItem from 'core_my/components/overview/OverviewRequiredActionSectionItem';
import UsersIcon from 'tui/components/icons/UsersAction';

export default {
  components: {
    ActionLink,
    ActivitiesIcon,
    Card,
    RequiredActionSection,
    RequiredActionSectionItem,
    UsersIcon,
  },

  props: {
    // Count of activities about others
    activitiesAboutOthers: {
      type: Number,
    },
    // Count of overdue activities about others
    activitiesAboutOthersOverdue: {
      type: Number,
    },
    // Overview is about current user
    currentUsersOverview: {
      type: Boolean,
    },
    // Number of activities waiting participant selection
    requireManualParticipantsCount: {
      type: Number,
    },
    // Should we see the activities about others section
    showActivitiesAboutOthers: {
      type: Boolean,
    },
    // Should we see the require manual participants section
    showRequireManualParticipants: {
      type: Boolean,
    },
    // Page layout is stacked
    stackedPage: {
      type: Boolean,
    },
    // Displayed users data
    user: {
      type: Object,
    },
  },

  data() {
    return {
      // Link for participant selection page
      participantSelectionURL: '/mod/perform/activity/select-participants.php',
    };
  },

  computed: {
    /**
     * The string output to inform the user there is pending
     * activities about others
     *
     * @return {String}
     */
    activitiesAboutOthersString() {
      let message;
      let overdue = this.activitiesAboutOthersOverdue;
      let plural = this.activitiesAboutOthers > 1;

      if (this.currentUsersOverview) {
        // About current user
        if (plural) {
          if (overdue && parseInt(overdue) === 1) {
            message = this.$str(
              'overview_activities_about_others_requires_response_one_overdue_plural',
              'core_my',
              {
                count: this.activitiesAboutOthers,
              }
            );
          } else if (overdue) {
            message = this.$str(
              'overview_activities_about_others_requires_response_overdue_plural',
              'core_my',
              {
                count: this.activitiesAboutOthers,
                overdue: this.activitiesAboutOthersOverdue,
              }
            );
          } else {
            message = this.$str(
              'overview_activities_about_others_requires_response_plural',
              'core_my',
              { count: this.activitiesAboutOthers }
            );
          }
        } else {
          if (overdue) {
            message = this.$str(
              'overview_activities_about_others_requires_response_overdue',
              'core_my'
            );
          } else {
            message = this.$str(
              'overview_activities_about_others_requires_response',
              'core_my'
            );
          }
        }
      } else {
        if (plural) {
          if (overdue && parseInt(overdue) === 1) {
            message = this.$str(
              'overview_activities_about_others_requires_user_response_one_overdue_plural',
              'core_my',
              {
                count: this.activitiesAboutOthers,
                name: this.user.fullname,
              }
            );
          } else if (overdue) {
            message = this.$str(
              'overview_activities_about_others_requires_user_response_overdue_plural',
              'core_my',
              {
                count: this.activitiesAboutOthers,
                name: this.user.fullname,
                overdue: this.activitiesAboutOthersOverdue,
              }
            );
          } else {
            message = this.$str(
              'overview_activities_about_others_requires_user_response_plural',
              'core_my',
              {
                count: this.activitiesAboutOthers,
                name: this.user.fullname,
              }
            );
          }
        } else {
          if (overdue) {
            message = this.$str(
              'overview_activities_about_others_requires_user_response_overdue',
              'core_my',
              { name: this.user.fullname }
            );
          } else {
            message = this.$str(
              'overview_activities_about_others_requires_user_response',
              'core_my',
              { name: this.user.fullname }
            );
          }
        }
      }
      return message;
    },

    /**
     * The string output to inform the user there is pending
     * participation selection for activities
     *
     * @return {String}
     */
    pendingParticipantSelectionString() {
      let message;
      let plural = this.requireManualParticipantsCount > 1;

      if (this.currentUsersOverview) {
        // About current user
        if (plural) {
          message = this.$str(
            'overview_activities_pending_your_participant_selection',
            'core_my',
            { count: this.requireManualParticipantsCount }
          );
        } else {
          message = this.$str(
            'overview_activity_pending_your_participant_selection',
            'core_my'
          );
        }
      } else {
        if (plural) {
          message = this.$str(
            'overview_activities_pending_their_participant_selection',
            'core_my',
            {
              count: this.requireManualParticipantsCount,
              name: this.user.fullname,
            }
          );
        } else {
          message = this.$str(
            'overview_activity_pending_their_participant_selection',
            'core_my',
            { name: this.user.fullname }
          );
        }
      }
      return message;
    },
  },
};
</script>

<style lang="scss">
.tui-myPerformOverviewRequiredAction {
  width: 100%;
  padding: var(--gap-4);

  & > * + * {
    margin-top: var(--gap-4);
  }

  &__header {
    @include font(h3);
    margin: 0;
  }

  &__body {
    & > * + * {
      margin-top: var(--gap-6);
    }
  }

  &__activitiesAboutOthers,
  &__participantsSelection {
    &-icon {
      color: var(--color-prompt-info);
    }
  }

  &--stacked {
    .tui-myPerformOverviewRequiredAction__header {
      @include font(h4);
    }
  }
}
</style>
