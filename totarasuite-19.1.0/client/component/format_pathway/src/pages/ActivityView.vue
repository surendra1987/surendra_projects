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

  @author Jack Humphrey <jack.humphrey@totara.com>
  @package format_pathway
-->
<template>
  <div
    class="tui-format_pathway-activityView"
    :class="{ 'tui-format_pathway-activityView--stacked': stacked }"
  >
    <Loader :loading="$apollo.loading">
      <Responsive
        :breakpoints="[
          { name: 'small', boundaries: [0, 960] },
          { name: null, boundaries: [960, 1672] },
        ]"
        @responsive-resize="resize"
      >
        <Grid
          :direction="stacked ? 'vertical' : 'horizontal'"
          :gutter-size="showNavigation ? 'var(--gap-page-columns)' : '0px'"
        >
          <GridItem v-show="showNavigation" :units="3">
            <div class="tui-format_pathway-activityView__nav">
              <SidePanel
                :id="$id('side-panel')"
                class="tui-format_pathway-activityView__sidePanel"
                :overflows="false"
                :show-button-control="false"
                chromeless
                animated
                :sticky="false"
                :open="navigationExpanded"
                @sidepanel-collapsed="hideNavigation"
              >
                <CourseToolbar
                  :side-panel-id="$id('side-panel')"
                  :course-settings="courseSettings"
                  :expanded="showNavigation"
                  :show-course-settings="hasActiveActivity"
                  @collapse-request="collapseNavigation"
                  @expand-request="expandNavigation"
                />
                <div class="tui-format_pathway-activityView__sidePanelInner">
                  <CourseInformation
                    :course-data="courseData"
                    :course-id="courseId"
                    :completion-data="courseCompletionData"
                    :course-interactor="courseInteractor"
                    :has-active-activity="hasActiveActivity"
                  />
                  <CourseNavigation
                    v-if="hasActiveActivity"
                    :active-course-module-id="activeCourseModuleId"
                    :completion-data="activityCompletionData"
                    :navigation-data="navigationData"
                    :can-view-hidden="
                      courseInteractor.can_view_hidden_activities
                    "
                  />
                  <SettingsTree
                    v-else
                    v-model:value="openTreeBranches"
                    class="tui-format_pathway-activityView__courseSettingsTree"
                    label-type="link"
                    :tree-data="courseSettings"
                  />
                </div>
              </SidePanel>
            </div>
          </GridItem>
          <GridItem :units="showNavigation ? 9 : 12">
            <div class="tui-format_pathway-activityView__activity">
              <ActivityToolbar
                :show-expand-button="!showNavigation"
                :previous-activity-url="previousActivityUrl"
                :next-activity-url="nextActivityUrl"
                :back-link-url="backLinkUrl"
                :back-link-text="backLinkText"
                :show-back-link="showBackLink"
                :on-activity-page="onActivityPage"
                class="tui-format_pathway-activityView__activityToolbar"
                @expand-request="expandNavigation"
              />
              <div class="tui-format_pathway-activityView__activityContent">
                <div
                  v-if="
                    showIncompatibleActivityWarning || showGuestEnrolmentBanner
                  "
                  class="tui-format_pathway-activityView__activityNotification"
                >
                  <NotificationBanner
                    v-if="showIncompatibleActivityWarning"
                    type="warning"
                    :message="
                      $str('incompatible_activity_warning', 'format_pathway')
                    "
                  />
                  <NotificationBanner
                    v-else-if="showGuestEnrolmentBanner"
                    type="info"
                    :message="getGuestEnrolmentBannerMessage"
                  />
                </div>
                <ContentRenderer :html="mainContent" />
                <ActivityFooter
                  :active-activity-completion="activeActivityCompletion"
                  :activity-completing="activityCompleting"
                  :course-completion-data="courseCompletionData"
                  :current-activity-url="currentActivityUrl"
                  :next-activity-url="nextActivityUrl"
                  :on-activity-page="onActivityPage"
                  :course-interactor="courseInteractor"
                  :show-exit-activity-button="showExitActivityButton"
                  @mark-activity-as-complete="
                    markActivityAsComplete(activeCourseModuleId)
                  "
                />
              </div>
            </div>
          </GridItem>
        </Grid>
      </Responsive>
    </Loader>
  </div>
</template>

<script>
import ContentRenderer from 'format_pathway/components/ContentRenderer';
import CourseToolbar from 'format_pathway/components/CourseToolbar';
import CourseNavigation from 'format_pathway/components/navigation/CourseNavigation';
import ActivityToolbar from 'format_pathway/components/ActivityToolbar';
import ActivityFooter from 'format_pathway/components/ActivityFooter';
import CourseInformation from 'format_pathway/components/CourseInformation';
import SettingsTree from 'tui/components/settings_navigation/SettingsNavigationTree';
import Loader from 'tui/components/loading/Loader';
import Responsive from 'tui/components/responsive/Responsive';
import Grid from 'tui/components/grid/Grid';
import GridItem from 'tui/components/grid/GridItem';
import SidePanel from 'tui/components/sidepanel/SidePanel';
import { notify } from 'tui/notifications';
import NotificationBanner from 'tui/components/notifications/NotificationBanner';
import { WebStorageStore } from 'tui/storage';

const storage = new WebStorageStore('format_pathway', window.localStorage);
import { config } from 'tui/config';

import getCourse from 'format_pathway/graphql/get_course';
import getCourseCompletion from 'format_pathway/graphql/get_course_completion';
import getMyCourseActivityCompletions from 'format_pathway/graphql/get_my_course_activity_completions';
import activitySelfCompleteMutation from 'completion/graphql/activity_self_complete';
import getCourseNavigation from 'format_pathway/graphql/get_course_navigation';
import courseInteractor from 'format_pathway/graphql/get_course_interactor';
import courseSettings from 'course/graphql/course_settings_navigation_tree';

export default {
  components: {
    ContentRenderer,
    CourseNavigation,
    CourseToolbar,
    ActivityToolbar,
    ActivityFooter,
    CourseInformation,
    Loader,
    Responsive,
    Grid,
    GridItem,
    NotificationBanner,
    SidePanel,
    SettingsTree,
  },

  props: {
    courseId: {
      type: Number,
      required: true,
    },

    mainContent: String,
    excludeNav: Boolean,
    activeCourseModuleId: Number,
    onActivityPage: Boolean,
    backLinkUrl: String,
    backLinkText: String,
  },

  data() {
    return {
      courseData: {},
      courseCompletionData: {},
      currentBoundaryName: null,
      activityCompletionData: [],
      activityCompleting: false,
      navigationData: {},
      courseInteractor: {},
      navigationExpanded: null,
      showNavigation: this.navigationExpanded,
      courseSettings: [],
      openTreeBranches: [],
    };
  },

  apollo: {
    courseData: {
      query: getCourse,
      variables() {
        return {
          course_id: this.courseId,
        };
      },
      update({ core_course: data }) {
        return data;
      },
    },
    courseCompletionData: {
      query: getCourseCompletion,
      variables() {
        return {
          course_id: this.courseId,
        };
      },
      update({ core_course: data }) {
        return data;
      },
    },

    activityCompletionData: {
      query: getMyCourseActivityCompletions,
      variables() {
        return {
          course_id: this.courseId,
        };
      },
      update({ format_pathway_get_my_course_activity_completions: data }) {
        return data;
      },
    },

    navigationData: {
      query: getCourseNavigation,
      variables() {
        return {
          course_id: this.courseId,
        };
      },
      update({ format_pathway_get_course_navigation: data }) {
        return data;
      },
    },

    courseInteractor: {
      query: courseInteractor,
      variables() {
        return {
          course_id: this.courseId,
        };
      },
      update({ interactor: data }) {
        return data;
      },
    },

    courseSettings: {
      query: courseSettings,
      variables() {
        return {
          context_id: config.context.id,
          page_url: window.location.href,
        };
      },
      update({ data }) {
        return data.trees;
      },
    },
  },

  computed: {
    stacked() {
      return this.currentBoundaryName === 'small';
    },
    activeActivityCompletion() {
      return this.activityCompletionData.find(
        activity => parseInt(activity.cmid) === this.activeCourseModuleId
      );
    },

    /**
     * A flattened list of activities the user can cycle through
     *
     * @returns {Array}
     */
    availableActivities() {
      if (
        this.navigationData.sections &&
        this.navigationData.sections.length > 0
      ) {
        const map = this.navigationData.sections.flatMap(
          section => section.modules
        );
        return this.courseInteractor.can_view_hidden_activities
          ? map
          : map.filter(activity => activity.available);
      }
      return [];
    },

    /**
     * The location of the currently selected activity in activities
     *
     * @returns {Number}
     */
    currentActivityIndex() {
      if (this.availableActivities.length === 0 || !this.activeCourseModuleId) {
        return -1;
      }

      return this.availableActivities.findIndex(
        activity => activity.cmid == this.activeCourseModuleId
      );
    },

    /**
     * The current activity object
     *
     * @returns {Object}
     */
    currentActivity() {
      if (this.availableActivities[this.currentActivityIndex]) {
        return this.availableActivities[this.currentActivityIndex];
      }

      return null;
    },

    /**
     * The url for the next activity (if available)
     *
     * @returns {String}
     */
    nextActivityUrl() {
      if (!this.activeCourseModuleId || this.currentActivityIndex === -1) {
        return null;
      }

      const nextActivity = this.availableActivities[
        this.currentActivityIndex + 1
      ];
      if (nextActivity) {
        return this.$url(nextActivity.viewurl);
      }

      return null;
    },

    /**
     * The url for the current activity (if available)
     *
     * @returns {String}
     */
    currentActivityUrl() {
      if (this.currentActivity) {
        return this.currentActivity.viewurl;
      }

      return null;
    },

    /**
     * The url for the previous activity (if available)
     *
     * @returns {String}
     */
    previousActivityUrl() {
      if (!this.activeCourseModuleId || this.currentActivityIndex === -1) {
        return null;
      }

      const previousActivity = this.availableActivities[
        this.currentActivityIndex - 1
      ];
      if (previousActivity) {
        return this.$url(previousActivity.viewurl);
      }

      return null;
    },

    /**
     *
     * @returns {Boolean}
     */
    showIncompatibleActivityWarning() {
      if (this.activeCourseModuleId && this.navigationData.sections) {
        const module = this.navigationData.sections
          .flatMap(section => section.modules)
          .find(module => module.cmid == this.activeCourseModuleId);
        if (module) {
          return module.blacklisted;
        }
      }
      return false;
    },

    /**
     *
     * @returns {Boolean}
     */
    userIsGuest() {
      return (
        this.courseInteractor.is_guest || this.courseInteractor.is_site_guest
      );
    },

    /**
     *
     * @returns {Boolean}
     */
    showGuestEnrolmentBanner() {
      if (this.courseInteractor.is_enrolled || !this.onActivityPage) {
        return false;
      }
      if (this.userIsGuest) {
        return (
          this.courseInteractor.non_interactive_enrol_instance_enabled ||
          this.courseInteractor.guest_enrol_enabled
        );
      }

      return false;
    },

    /**
     *
     * @returns {Boolean}
     */
    showBackLink() {
      if (this.userIsGuest && !this.courseInteractor.guest_enrol_enabled) {
        return false;
      }
      return !this.onActivityPage && Boolean(this.backLinkUrl);
    },

    /**
     *
     * @returns {String}
     */
    getGuestEnrolmentBannerMessage() {
      const message = '';
      if (this.courseInteractor.is_enrolled) {
        return message;
      }

      if (
        this.courseInteractor.non_interactive_enrol_instance_enabled &&
        this.courseInteractor.is_guest &&
        !this.courseInteractor.is_site_guest
      ) {
        return this.$str(
          'view_course_as_guest_with_enrol_options',
          'container_course'
        );
      }

      if (this.courseInteractor.guest_enrol_enabled) {
        return this.$str('view_course_as_guest', 'container_course');
      }
      return message;
    },

    /**
     *
     * @returns {Boolean}
     */
    hasActiveActivity() {
      return Boolean(this.activeCourseModuleId);
    },

    /**
     *
     * @returns {Boolean}
     */
    showExitActivityButton() {
      return window.location.href.includes('player.php');
    },
  },

  watch: {
    navigationExpanded(val) {
      if (this.$el.offsetWidth > 960) {
        storage.set('navigation', { isOpen: val });
      }
    },
  },

  mounted() {
    // Never start with the side panel open on mobile.
    // We use innerWidth directly because currentBoundary isn't know on at this point or even in next tick.
    if (this.$el.offsetWidth > 960) {
      let state = true;
      const sidePanelState = storage.get('navigation');
      if (sidePanelState) {
        state = sidePanelState.isOpen;
      }
      this.navigationExpanded = state;
      this.showNavigation = state;
      return;
    }
    this.navigationExpanded = false;
    this.showNavigation = false;
  },

  methods: {
    expandNavigation() {
      this.navigationExpanded = true;
      this.showNavigation = true;
    },

    collapseNavigation() {
      this.navigationExpanded = false;
    },

    hideNavigation() {
      this.showNavigation = false;
    },

    /**
     * Handles responsive resizing which wraps the grid layout for this page
     *
     * @param {String} boundaryName
     */
    resize(boundaryName) {
      this.currentBoundaryName = boundaryName;
    },

    /**
     *
     * @param {Number} cmid
     */
    async markActivityAsComplete(cmid) {
      if (this.activityCompleting) {
        return;
      }

      this.activityCompleting = true;
      try {
        const data = await this.$apollo.mutate({
          mutation: activitySelfCompleteMutation,
          variables: {
            cmid: cmid,
            complete: true,
          },
          refetchQueries: [
            'format_pathway_get_course_navigation',
            'format_pathway_get_course_completion',
          ],
          awaitRefetchQueries: true,
          update: proxy => {
            const {
              format_pathway_get_my_course_activity_completions: completions,
            } = proxy.readQuery({
              query: getMyCourseActivityCompletions,
              variables: {
                course_id: this.courseId,
              },
            });

            proxy.writeQuery({
              query: getMyCourseActivityCompletions,
              variables: {
                course_id: this.courseId,
              },
              data: {
                format_pathway_get_my_course_activity_completions: completions.map(
                  activity =>
                    parseInt(activity.cmid) === cmid
                      ? { ...activity, completionstatus: true }
                      : activity
                ),
              },
            });
          },
        });

        if (data) {
          notify({
            type: 'success',
            message: this.$str(
              'activity_mark_as_complete_success',
              'format_pathway'
            ),
          });
        }
      } finally {
        this.activityCompleting = false;
      }
    },
  },
};
</script>

<style lang="scss">
.tui-format_pathway-activityView {
  margin-top: var(--gap-2);
  @include layout-page-padding();

  &__sidePanel {
    overflow: visible;
  }

  &__activity {
    & > * + * {
      margin-top: var(--gap-2);
    }
  }

  &__activityContent {
    max-width: 810px;
    margin: auto;
    margin-top: var(--gap-2);
    margin-bottom: var(--gap-5);
  }

  &__sidePanelInner {
    padding: var(--gap-2) 0;
    @include tui-stack-vertical(var(--gap-4));
  }

  &__activityNotification {
    padding-top: var(--gap-2);
  }
}
</style>
