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
  <Section
    class="tui-overviewActivitiesSection"
    :initial-load="initialLoad"
    :last-view-all-page="viewAllLastPage"
    :loading="isOverviewLoading"
    :loading-view-all="isOverviewStatusLoading"
    :open-view-all="viewAllModalOpen"
    :stacked-page="stackedPage"
    :title="$str('overview_activities_section_title', 'core_my')"
    :url="url"
    :view-all-title="viewAllTitle"
    @view-all-closed="closeViewAllModal"
    @view-all-load-more="loadMore"
  >
    <template v-slot:chart-area>
      <!-- Chart content -->
      <OverviewDoughnut
        :achieved-count="activitiesOverview.state_counts.completed"
        class="tui-overviewActivitiesSection__chart"
        :completed="true"
        :not-progressed-count="activitiesOverview.state_counts.not_progressed"
        :not-started-count="activitiesOverview.state_counts.not_started"
        :progressed-count="activitiesOverview.state_counts.progressed"
      />

      <!-- Counts content -->
      <OverviewCount
        class="tui-overviewActivitiesSection__count"
        :due-soon="activitiesOverview.due_soon"
        :item-label="$str('overview_activities_count_label', 'core_my')"
        :items="activitiesOverview.total"
      />
    </template>

    <template
      v-if="!activitiesOverview.total && !isOverviewLoading"
      v-slot:no-content
    >
      <!-- Empty state -->
      <div>
        {{ $str('overview_no_activities', 'core_my') }}
      </div>
    </template>

    <template v-slot:content>
      <div
        class="tui-overviewActivitiesSection__content"
        :class="{
          'tui-overviewActivitiesSection__content--stacked': stackedPage,
        }"
      >
        <!-- Completed activities -->
        <OverviewStatusTable
          v-if="activitiesOverview.activities.completed.length"
          class="tui-overviewActivitiesSection__content-completed"
          :data="activitiesOverview.activities.completed"
          :loading="isOverviewLoading"
          :stacked-page="stackedPage"
          :title="$str('overview_status_heading_completed', 'core_my')"
          :total="activitiesOverview.state_counts.completed"
          @view-all="
            openViewAllModal({
              state: STATE_COMPLETED,
              title: $str(
                'overview_view_all_activities_completed_heading',
                'core_my',
                activitiesOverview.state_counts.completed
              ),
            })
          "
        >
          <template v-slot:sub-content="{ row }">
            <ItemAchieved :completed="true" :date="row.completion_date" />

            <ItemAssignmentType
              v-if="row.job_assignment"
              :assignment-type="row.job_assignment"
            />
          </template>
        </OverviewStatusTable>

        <!-- Progressed activities -->
        <OverviewStatusTable
          v-if="activitiesOverview.activities.progressed.length"
          class="tui-overviewActivitiesSection__content-progressed"
          :data="activitiesOverview.activities.progressed"
          :loading="isOverviewLoading"
          :stacked-page="stackedPage"
          :title="$str('overview_status_heading_progressed', 'core_my')"
          :total="activitiesOverview.state_counts.progressed"
          @view-all="
            openViewAllModal({
              state: STATE_PROGRESSED,
              title: $str(
                'overview_view_all_activities_progressed_heading',
                'core_my',
                activitiesOverview.state_counts.progressed
              ),
            })
          "
        >
          <template v-slot:sub-content="{ row }">
            <ItemUpdated
              :date="row.last_update.date"
              :description="row.last_update.description"
            />

            <ItemDueDate
              v-if="row.due.date"
              :date="row.due.date"
              :due-soon="row.due.due_soon"
              :overdue="row.due.overdue"
            />

            <ItemAssignmentType
              v-if="row.job_assignment"
              :assignment-type="row.job_assignment"
            />
          </template>
        </OverviewStatusTable>

        <!-- Not progressed activities -->
        <OverviewStatusTable
          v-if="activitiesOverview.activities.not_progressed.length"
          class="tui-overviewActivitiesSection__content-notProgressed"
          :data="activitiesOverview.activities.not_progressed"
          :loading="isOverviewLoading"
          :stacked-page="stackedPage"
          :title="$str('overview_status_heading_not_progressed', 'core_my')"
          :total="activitiesOverview.state_counts.not_progressed"
          @view-all="
            openViewAllModal({
              state: STATE_NOT_PROGRESSED,
              title: $str(
                'overview_view_all_activities_not_progressed_heading',
                'core_my',
                activitiesOverview.state_counts.not_progressed
              ),
            })
          "
        >
          <template v-slot:sub-content="{ row }">
            <ItemUpdated
              :date="row.last_update.date"
              :description="row.last_update.description"
            />

            <ItemDueDate
              v-if="row.due.date"
              :date="row.due.date"
              :due-soon="row.due.due_soon"
              :overdue="row.due.overdue"
            />

            <ItemAssignmentType
              v-if="row.job_assignment"
              :assignment-type="row.job_assignment"
            />
          </template>
        </OverviewStatusTable>

        <!-- Not started activities -->
        <OverviewStatusTable
          v-if="activitiesOverview.activities.not_started.length"
          class="tui-overviewActivitiesSection__content-notStarted"
          :data="activitiesOverview.activities.not_started"
          :loading="isOverviewLoading"
          :stacked-page="stackedPage"
          :title="$str('overview_status_heading_not_started', 'core_my')"
          :total="activitiesOverview.state_counts.not_started"
          @view-all="
            openViewAllModal({
              state: STATE_NOT_STARTED,
              title: $str(
                'overview_view_all_activities_not_started_heading',
                'core_my',
                activitiesOverview.state_counts.not_started
              ),
            })
          "
        >
          <template v-slot:sub-content="{ row }">
            <ItemAssigned :date="row.assignment_date" />

            <ItemDueDate
              v-if="row.due.date"
              :date="row.due.date"
              :due-soon="row.due.due_soon"
              :overdue="row.due.overdue"
            />

            <ItemAssignmentType
              v-if="row.job_assignment"
              :assignment-type="row.job_assignment"
            />
          </template>
        </OverviewStatusTable>
      </div>
    </template>

    <template v-slot:view-all-content>
      <OverviewStatusTable
        class="tui-overviewActivitiesSection__content-viewAll"
        :data="activitiesByStatus.activities"
        :loading="isOverviewStatusLoadingFirstPage"
        :stacked-page="stackedPage"
        :view-all-mode="true"
      >
        <template v-slot:sub-content="{ row }">
          <!-- Complete content -->
          <template v-if="viewAllState === STATE_COMPLETED">
            <ItemAchieved :completed="true" :date="row.completion_date" />

            <ItemAssignmentType
              v-if="row.job_assignment"
              :assignment-type="row.job_assignment"
            />
          </template>

          <!-- Progressed content -->
          <template v-else-if="viewAllState === STATE_PROGRESSED">
            <ItemUpdated
              :date="row.last_update.date"
              :description="row.last_update.description"
            />

            <ItemDueDate
              v-if="row.due.date"
              :date="row.due.date"
              :due-soon="row.due.due_soon"
              :overdue="row.due.overdue"
            />

            <ItemAssignmentType
              v-if="row.job_assignment"
              :assignment-type="row.job_assignment"
            />
          </template>

          <!-- Not progressed content -->
          <template v-else-if="viewAllState === STATE_NOT_PROGRESSED">
            <ItemUpdated
              :date="row.last_update.date"
              :description="row.last_update.description"
            />

            <ItemDueDate
              v-if="row.due.date"
              :date="row.due.date"
              :due-soon="row.due.due_soon"
              :overdue="row.due.overdue"
            />

            <ItemAssignmentType
              v-if="row.job_assignment"
              :assignment-type="row.job_assignment"
            />
          </template>

          <!-- Not started content -->
          <template v-else-if="viewAllState === STATE_NOT_STARTED">
            <ItemAssigned :date="row.assignment_date" />

            <ItemDueDate
              v-if="row.due.date"
              :date="row.due.date"
              :due-soon="row.due.due_soon"
              :overdue="row.due.overdue"
            />

            <ItemAssignmentType
              v-if="row.job_assignment"
              :assignment-type="row.job_assignment"
            />
          </template>
        </template>

        <template v-slot:description="{ row }">
          <div v-html="row.description" />
        </template>
      </OverviewStatusTable>
    </template>
  </Section>
</template>

<script>
import ItemAchieved from 'core_my/components/overview/OverviewItemAchieved';
import ItemAssigned from 'core_my/components/overview/OverviewItemAssigned';
import ItemAssignmentType from 'core_my/components/overview/OverviewItemAssignmentType';
import ItemDueDate from 'core_my/components/overview/OverviewItemDueDate';
import ItemUpdated from 'core_my/components/overview/OverviewItemUpdated';
import OverviewCount from 'core_my/components/overview/OverviewCount';
import OverviewDoughnut from 'core_my/components/overview/OverviewDoughnut';
import OverviewStatusTable from 'core_my/components/overview/OverviewStatusTable';
import Section from 'core_my/components/overview/OverviewSection';
// Queries
import ActivitiesOverviewQuery from 'mod_perform/graphql/subject_instance_overview';
import ActivitiesOverviewStatusQuery from 'mod_perform/graphql/subject_instance_overview_by_status';

const STATE_COMPLETED = 'completed';
const STATE_NOT_PROGRESSED = 'not_progressed';
const STATE_NOT_STARTED = 'not_started';
const STATE_PROGRESSED = 'progressed';

export default {
  components: {
    ItemAchieved,
    ItemAssigned,
    ItemAssignmentType,
    ItemDueDate,
    ItemUpdated,
    OverviewCount,
    OverviewDoughnut,
    OverviewStatusTable,
    Section,
  },

  props: {
    // Time period filter value
    period: {
      type: Number,
    },
    // Outer layout is stacked
    stackedPage: {
      type: Boolean,
    },
    // URL to activities content
    url: {
      required: true,
      type: String,
    },
    // User ID for users page being viewed
    userId: {
      type: Number,
    },
  },

  data() {
    return {
      // Overview status activity data
      activitiesByStatus: {
        activities: [],
      },
      // Overview activity data
      activitiesOverview: {
        activities: {
          completed: [{}],
          not_progressed: [{}],
          not_started: [{}],
          progressed: [{}],
        },
        state_counts: {
          achieved: null,
          not_progressed: null,
          not_started: null,
          progressed: null,
        },
        total: 0,
      },
      // First page load
      initialLoad: true,
      // State completed string
      STATE_COMPLETED,
      // State not progressed string
      STATE_NOT_PROGRESSED,
      // State not started string
      STATE_NOT_STARTED,
      // State progressed string
      STATE_PROGRESSED,
      // Current load more page on status modal
      viewAllCurrentPage: 1,
      // Last view all status page
      viewAllLastPage: true,
      // Boolean for displaying status modal
      viewAllModalOpen: false,
      // Number of status modal items displayed per page
      viewAllPageLimit: 10,
      // State being displayed in status modal
      viewAllState: null,
      // Title being displayed in status modal
      viewAllTitle: null,
    };
  },

  computed: {
    /**
     * Are we currently querying overview data via graphQL?
     *
     * @return {Boolean}
     */
    isOverviewLoading() {
      return this.$apollo.queries.activitiesOverview.loading;
    },

    /**
     * Are we currently querying overview data for a status via graphQL?
     *
     * @return {Boolean}
     */
    isOverviewStatusLoading() {
      return this.$apollo.queries.activitiesByStatus.loading;
    },

    /**
     * Are we currently querying first page of overview view all data via graphQL?
     *
     * @return {Boolean}
     */
    isOverviewStatusLoadingFirstPage() {
      return (
        this.$apollo.queries.activitiesByStatus.loading &&
        this.viewAllCurrentPage === 1
      );
    },
  },

  apollo: {
    activitiesOverview: {
      query: ActivitiesOverviewQuery,
      variables() {
        return {
          input: {
            filters: {
              id: this.userId,
              period: this.period,
            },
          },
        };
      },
      update({ mod_perform_subject_instance_overview: data }) {
        this.initialLoad = false;
        return data;
      },
    },

    activitiesByStatus: {
      query: ActivitiesOverviewStatusQuery,
      fetchPolicy: 'cache-and-network',
      skip() {
        return !this.viewAllState || !this.viewAllModalOpen;
      },
      variables() {
        return {
          input: {
            filters: {
              id: this.userId,
              period: this.period,
              status: this.viewAllState,
            },
            pagination: {
              limit: this.viewAllPageLimit,
              page: 1,
            },
          },
        };
      },
      update({ mod_perform_subject_instance_overview_by_status: data }) {
        this.viewAllLastPage = data.total <= data.activities.length;
        return data;
      },
    },
  },

  methods: {
    /**
     * Close view all modal
     *
     */
    closeViewAllModal() {
      this.viewAllModalOpen = false;
      this.viewAllCurrentPage = 1;
      this.viewAllLastPage = true;
    },

    /**
     * Load more activities on the view all modal
     *
     */
    loadMore() {
      // Increase page number
      this.viewAllCurrentPage += 1;

      // Fetch additional data
      this.$apollo.queries.activitiesByStatus.fetchMore({
        variables: {
          input: {
            filters: {
              id: this.userId,
              period: this.period,
              status: this.viewAllState,
            },
            pagination: {
              limit: this.viewAllPageLimit,
              page: this.viewAllCurrentPage,
            },
          },
        },
        updateQuery: (previousResult, { fetchMoreResult }) => {
          fetchMoreResult.mod_perform_subject_instance_overview_by_status.activities.unshift(
            ...previousResult.mod_perform_subject_instance_overview_by_status
              .activities
          );

          let numberOfActivities =
            fetchMoreResult.mod_perform_subject_instance_overview_by_status
              .activities.length;

          this.viewAllLastPage = fetchMoreResult.total <= numberOfActivities;

          return fetchMoreResult;
        },
      });
    },

    /**
     * Open view all modal for particular status
     *
     * @param {Object} data
     */
    openViewAllModal(data) {
      this.viewAllTitle = data.title;
      this.viewAllState = data.state;
      this.viewAllModalOpen = true;
    },
  },
};
</script>

<style lang="scss">
.tui-overviewActivitiesSection {
  &__chart {
    min-width: 100px;
    max-width: 320px;
    margin: auto;
  }

  &__content {
    & > * + * {
      margin-top: var(--gap-6);
    }

    &--stacked {
      margin-top: var(--gap-4);
    }
  }
}
</style>
