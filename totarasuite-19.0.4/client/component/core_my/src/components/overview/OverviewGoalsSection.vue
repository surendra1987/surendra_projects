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
    class="tui-overviewGoalsSection"
    :initial-load="initialLoad"
    :last-view-all-page="viewAllLastPage"
    :loading="isOverviewLoading"
    :loading-view-all="isOverviewStatusLoading"
    :open-view-all="viewAllModalOpen"
    :stacked-page="stackedPage"
    :title="$str('overview_goals_section_title', 'core_my')"
    :url="url"
    :view-all-title="viewAllTitle"
    @view-all-closed="closeViewAllModal"
    @view-all-load-more="loadMore"
  >
    <template v-slot:chart-area>
      <!-- Chart content -->
      <OverviewDoughnut
        class="tui-overviewGoalsSection__chart"
        :achieved-count="goalsOverview.state_counts.achieved"
        :not-progressed-count="goalsOverview.state_counts.not_progressed"
        :not-started-count="goalsOverview.state_counts.not_started"
        :progressed-count="goalsOverview.state_counts.progressed"
      />

      <!-- Counts content -->
      <OverviewCount
        class="tui-overviewGoalsSection__count"
        :due-soon="goalsOverview.due_soon"
        :items="goalsOverview.total"
        :item-label="$str('overview_goals_count_label', 'core_my')"
      />
    </template>

    <template
      v-if="!goalsOverview.total && !isOverviewLoading"
      v-slot:no-content
    >
      <div class="tui-overviewGoalsSection__noGoals">
        <!-- Empty state -->
        <div>
          {{ $str('overview_no_goals', 'core_my') }}
        </div>
      </div>
    </template>

    <template v-slot:content>
      <div
        class="tui-overviewGoalsSection__content"
        :class="{
          'tui-overviewGoalsSection__content--stacked': stackedPage,
        }"
      >
        <!-- Achieved goals -->
        <OverviewStatusTable
          v-if="goalsOverview.goals.achieved.length"
          class="tui-overviewGoalsSection__content-achieved"
          :data="goalsOverview.goals.achieved"
          :loading="isOverviewLoading"
          :stacked-page="stackedPage"
          :title="$str('overview_status_heading_achieved', 'core_my')"
          :total="goalsOverview.state_counts.achieved"
          @view-all="
            openViewAllModal({
              state: STATE_ACHIEVED,
              title: $str(
                'overview_view_all_goals_achieved_heading',
                'core_my',
                goalsOverview.state_counts.achieved
              ),
            })
          "
        >
          <template v-slot:sub-content="{ row }">
            <ItemAchieved :date="row.achievement_date" />
          </template>
        </OverviewStatusTable>

        <!-- Progressed goals -->
        <OverviewStatusTable
          v-if="goalsOverview.goals.progressed.length"
          class="tui-overviewGoalsSection__content-progressed"
          :data="goalsOverview.goals.progressed"
          :loading="isOverviewLoading"
          :stacked-page="stackedPage"
          :title="$str('overview_status_heading_progressed', 'core_my')"
          :total="goalsOverview.state_counts.progressed"
          @view-all="
            openViewAllModal({
              state: STATE_PROGRESSED,
              title: $str(
                'overview_view_all_goals_progressed_heading',
                'core_my',
                goalsOverview.state_counts.progressed
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
          </template>
        </OverviewStatusTable>

        <!-- Not progressed goals -->
        <OverviewStatusTable
          v-if="goalsOverview.goals.not_progressed.length"
          class="tui-overviewGoalsSection__content-notProgressed"
          :data="goalsOverview.goals.not_progressed"
          :loading="isOverviewLoading"
          :stacked-page="stackedPage"
          :title="$str('overview_status_heading_not_progressed', 'core_my')"
          :total="goalsOverview.state_counts.not_progressed"
          @view-all="
            openViewAllModal({
              state: STATE_NOT_PROGRESSED,
              title: $str(
                'overview_view_all_goals_not_progressed_heading',
                'core_my',
                goalsOverview.state_counts.not_progressed
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
          </template>
        </OverviewStatusTable>

        <!-- Not started goals -->
        <OverviewStatusTable
          v-if="goalsOverview.goals.not_started.length"
          class="tui-overviewGoalsSection__content-notStarted"
          :data="goalsOverview.goals.not_started"
          :loading="isOverviewLoading"
          :stacked-page="stackedPage"
          :title="$str('overview_status_heading_not_started', 'core_my')"
          :total="goalsOverview.state_counts.not_started"
          @view-all="
            openViewAllModal({
              state: STATE_NOT_STARTED,
              title: $str(
                'overview_view_all_goals_not_started_heading',
                'core_my',
                goalsOverview.state_counts.not_started
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
          </template>
        </OverviewStatusTable>
      </div>
    </template>

    <template v-slot:view-all-content>
      <OverviewStatusTable
        class="tui-overviewGoalsSection__content-viewAll"
        :data="goalsByStatus.goals"
        :loading="isOverviewStatusLoadingFirstPage"
        :stacked-page="stackedPage"
        :view-all-mode="true"
      >
        <template v-slot:sub-content="{ row }">
          <!-- Achieved content -->
          <template v-if="viewAllState === STATE_ACHIEVED">
            <ItemAchieved :date="row.achievement_date" />
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
import ItemDueDate from 'core_my/components/overview/OverviewItemDueDate';
import ItemUpdated from 'core_my/components/overview/OverviewItemUpdated';
import OverviewCount from 'core_my/components/overview/OverviewCount';
import OverviewDoughnut from 'core_my/components/overview/OverviewDoughnut';
import OverviewStatusTable from 'core_my/components/overview/OverviewStatusTable';
import Section from 'core_my/components/overview/OverviewSection';
// Queries
import GoalsOverviewQuery from 'perform_goal/graphql/overview';
import GoalsOverviewStatusQuery from 'perform_goal/graphql/overview_by_state';

const STATE_ACHIEVED = 'achieved';
const STATE_NOT_PROGRESSED = 'not_progressed';
const STATE_NOT_STARTED = 'not_started';
const STATE_PROGRESSED = 'progressed';

export default {
  components: {
    ItemAchieved,
    ItemAssigned,
    ItemDueDate,
    ItemUpdated,
    OverviewCount,
    OverviewDoughnut,
    OverviewStatusTable,
    Section,
  },

  props: {
    // Time period filter value
    period: { type: Number },
    // Outer layout is stacked
    stackedPage: { type: Boolean },
    // URL to goals content
    url: { required: true, type: String },
    // User ID for users page being viewed
    userId: { type: Number },
  },

  data() {
    return {
      // Overview status goal data
      goalsByStatus: {
        goals: [],
      },
      // Overview goal data
      goalsOverview: {
        goals: {
          achieved: [{}],
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
      // State achieved string
      STATE_ACHIEVED,
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
      return this.$apollo.queries.goalsOverview.loading;
    },

    /**
     * Are we currently querying overview data for a status via graphQL?
     *
     * @return {Boolean}
     */
    isOverviewStatusLoading() {
      return this.$apollo.queries.goalsByStatus.loading;
    },

    /**
     * Are we currently querying first page of overview view all data via graphQL?
     *
     * @return {Boolean}
     */
    isOverviewStatusLoadingFirstPage() {
      return (
        this.$apollo.queries.goalsByStatus.loading &&
        this.viewAllCurrentPage === 1
      );
    },
  },

  apollo: {
    goalsOverview: {
      query: GoalsOverviewQuery,
      variables() {
        return {
          input: {
            filters: {
              id: this.userId,
              period: this.period,
            },
            sort: [
              {
                column: 'target_date',
                direction: 'DESC',
              },
            ],
          },
        };
      },
      update({ perform_goal_overview: data }) {
        this.initialLoad = false;
        return data;
      },
    },

    goalsByStatus: {
      query: GoalsOverviewStatusQuery,
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
            sort: [
              {
                column: 'last_updated',
                direction: 'DESC',
              },
            ],
          },
        };
      },
      update({ perform_goal_overview_by_state: data }) {
        this.viewAllLastPage = data.total <= data.goals.length;
        return data;
      },
    },
  },

  methods: {
    /**
     * Close view all modal
     */
    closeViewAllModal() {
      this.viewAllModalOpen = false;
      this.viewAllCurrentPage = 1;
      this.viewAllLastPage = true;
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

    /**
     * Load more goals on the view all modal
     *
     */
    loadMore() {
      // Increase page number
      this.viewAllCurrentPage += 1;

      // Fetch additional data
      this.$apollo.queries.goalsByStatus.fetchMore({
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
            sort: [
              {
                column: 'last_updated',
                direction: 'DESC',
              },
            ],
          },
        },
        updateQuery: (previousResult, { fetchMoreResult }) => {
          fetchMoreResult.perform_goal_overview_by_state.goals.unshift(
            ...previousResult.perform_goal_overview_by_state.goals
          );

          let numberOfGoals =
            fetchMoreResult.perform_goal_overview_by_state.goals.length;

          this.viewAllLastPage = fetchMoreResult.total <= numberOfGoals;

          return fetchMoreResult;
        },
      });
    },
  },
};
</script>

<style lang="scss">
.tui-overviewGoalsSection {
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

  &__noGoals {
    & > * + * {
      margin-top: var(--gap-4);
    }
  }
}
</style>
