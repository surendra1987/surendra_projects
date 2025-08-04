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
    class="tui-overviewCompetenciesSection"
    :initial-load="initialLoad"
    :last-view-all-page="viewAllLastPage"
    :loading="isOverviewLoading"
    :loading-view-all="isOverviewStatusLoading"
    :open-view-all="viewAllModalOpen"
    :stacked-page="stackedPage"
    :title="$str('overview_competencies_section_title', 'core_my')"
    :url="url"
    :view-all-title="viewAllTitle"
    @view-all-closed="closeViewAllModal"
    @view-all-load-more="loadMore"
  >
    <template v-slot:chart-area>
      <!-- Chart content -->
      <OverviewDoughnut
        class="tui-overviewCompetenciesSection__chart"
        :achieved-count="competencyOverview.state_counts.achieved"
        :not-progressed-count="competencyOverview.state_counts.not_progressed"
        :not-started-count="competencyOverview.state_counts.not_started"
        :progressed-count="competencyOverview.state_counts.progressed"
      />

      <!-- Counts content -->
      <OverviewCount
        class="tui-overviewCompetenciesSection__count"
        :item-label="$str('overview_competencies_count_label', 'core_my')"
        :items="competencyOverview.total"
      />
    </template>

    <template
      v-if="!competencyOverview.total && !isOverviewLoading"
      v-slot:no-content
    >
      <div class="tui-overviewCompetenciesSection__noCompetencies">
        <!-- Empty state -->
        <div>
          {{ $str('overview_no_competencies', 'core_my') }}
        </div>

        <ActionLink
          v-if="currentUsersOverview"
          :href="assignCompetenciesUrl"
          :text="$str('overview_self_assign_competencies', 'core_my')"
        />
      </div>
    </template>

    <template v-slot:content>
      <div
        class="tui-overviewCompetenciesSection__content"
        :class="{
          'tui-overviewCompetenciesSection__content--stacked': stackedPage,
        }"
      >
        <!-- Achieved competencies -->
        <OverviewStatusTable
          v-if="competencyOverview.competencies.achieved.length"
          class="tui-overviewCompetenciesSection__content-achieved"
          :data="competencyOverview.competencies.achieved"
          :loading="isOverviewLoading"
          :stacked-page="stackedPage"
          :title="
            $str('overview_status_heading_achieved_proficiency', 'core_my')
          "
          :total="competencyOverview.state_counts.achieved"
          @view-all="
            openViewAllModal({
              state: STATE_ACHIEVED,
              title: $str(
                'overview_view_all_competencies_achieved_heading',
                'core_my',
                competencyOverview.state_counts.achieved
              ),
            })
          "
        >
          <template v-slot:sub-content="{ row }">
            <ItemAchieved :date="row.achievement_date" />

            <ItemAchievementLevel :level="row.achievement_level" />

            <ItemAssignmentType
              v-if="row.assignment_type"
              :assignment-type="row.assignment_type"
            />
          </template>
        </OverviewStatusTable>

        <!-- Progressed competencies -->
        <OverviewStatusTable
          v-if="competencyOverview.competencies.progressed.length"
          class="tui-overviewCompetenciesSection__content-progressed"
          :data="competencyOverview.competencies.progressed"
          :loading="isOverviewLoading"
          :stacked-page="stackedPage"
          :title="$str('overview_status_heading_progressed', 'core_my')"
          :total="competencyOverview.state_counts.progressed"
          @view-all="
            openViewAllModal({
              state: STATE_PROGRESSED,
              title: $str(
                'overview_view_all_competencies_progressed_heading',
                'core_my',
                competencyOverview.state_counts.progressed
              ),
            })
          "
        >
          <template v-slot:sub-content="{ row }">
            <ItemUpdated
              :date="row.last_update.date"
              :description="row.last_update.description"
            />

            <ItemAchievementLevel :level="row.achievement_level" />

            <ItemAssignmentType
              v-if="row.assignment_type"
              :assignment-type="row.assignment_type"
            />
          </template>
        </OverviewStatusTable>

        <!-- Not progressed competencies -->
        <OverviewStatusTable
          v-if="competencyOverview.competencies.not_progressed.length"
          class="tui-overviewCompetenciesSection__content-notProgressed"
          :data="competencyOverview.competencies.not_progressed"
          :loading="isOverviewLoading"
          :stacked-page="stackedPage"
          :title="$str('overview_status_heading_not_progressed', 'core_my')"
          :total="competencyOverview.state_counts.not_progressed"
          @view-all="
            openViewAllModal({
              state: STATE_NOT_PROGRESSED,
              title: $str(
                'overview_view_all_competencies_not_progressed_heading',
                'core_my',
                competencyOverview.state_counts.not_progressed
              ),
            })
          "
        >
          <template v-slot:sub-content="{ row }">
            <ItemUpdated
              :date="row.last_update.date"
              :description="row.last_update.description"
            />

            <ItemAchievementLevel :level="row.achievement_level" />

            <ItemAssignmentType
              v-if="row.assignment_type"
              :assignment-type="row.assignment_type"
            />
          </template>
        </OverviewStatusTable>

        <!-- Not started competencies -->
        <OverviewStatusTable
          v-if="competencyOverview.competencies.not_started.length"
          class="tui-overviewCompetenciesSection__content-notStarted"
          :data="competencyOverview.competencies.not_started"
          :loading="isOverviewLoading"
          :stacked-page="stackedPage"
          :title="$str('overview_status_heading_not_started', 'core_my')"
          :total="competencyOverview.state_counts.not_started"
          @view-all="
            openViewAllModal({
              state: STATE_NOT_STARTED,
              title: $str(
                'overview_view_all_competencies_not_started_heading',
                'core_my',
                competencyOverview.state_counts.not_started
              ),
            })
          "
        >
          <template v-slot:sub-content="{ row }">
            <ItemAssigned :date="row.assignment_date" />

            <ItemAchievementLevel :level="row.achievement_level" />

            <ItemAssignmentType
              v-if="row.assignment_type"
              :assignment-type="row.assignment_type"
            />
          </template>
        </OverviewStatusTable>
      </div>
    </template>

    <template v-slot:view-all-content>
      <OverviewStatusTable
        class="tui-overviewCompetenciesSection__content-viewAll"
        :data="competenciesByStatus.items"
        :loading="isOverviewStatusLoadingFirstPage"
        :stacked-page="stackedPage"
        :view-all-mode="true"
      >
        <template v-slot:sub-content="{ row }">
          <!-- Achieved content -->
          <template v-if="viewAllState === STATE_ACHIEVED">
            <ItemAchieved :date="row.achievement_date" />

            <ItemAchievementLevel :level="row.achievement_level" />

            <ItemAssignmentType :assignment-type="row.assignment_type" />
          </template>

          <!-- Progressed content -->
          <template v-else-if="viewAllState === STATE_PROGRESSED">
            <ItemUpdated
              :date="row.last_update.date"
              :description="row.last_update.description"
            />

            <ItemAchievementLevel :level="row.achievement_level" />

            <ItemAssignmentType :assignment-type="row.assignment_type" />
          </template>

          <!-- Not progressed content -->
          <template v-else-if="viewAllState === STATE_NOT_PROGRESSED">
            <ItemUpdated
              :date="row.last_update.date"
              :description="row.last_update.description"
            />

            <ItemAchievementLevel :level="row.achievement_level" />

            <ItemAssignmentType :assignment-type="row.assignment_type" />
          </template>

          <!-- Not started content -->
          <template v-else-if="viewAllState === STATE_NOT_STARTED">
            <ItemAssigned :date="row.assignment_date" />

            <ItemAchievementLevel :level="row.achievement_level" />

            <ItemAssignmentType :assignment-type="row.assignment_type" />
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
import ActionLink from 'tui/components/links/ActionLink';
import ItemAchieved from 'core_my/components/overview/OverviewItemAchieved';
import ItemAchievementLevel from 'core_my/components/overview/OverviewItemAchievementLevel';
import ItemAssigned from 'core_my/components/overview/OverviewItemAssigned';
import ItemAssignmentType from 'core_my/components/overview/OverviewItemAssignmentType';
import ItemUpdated from 'core_my/components/overview/OverviewItemUpdated';
import OverviewCount from 'core_my/components/overview/OverviewCount';
import OverviewDoughnut from 'core_my/components/overview/OverviewDoughnut';
import OverviewStatusTable from 'core_my/components/overview/OverviewStatusTable';
import Section from 'core_my/components/overview/OverviewSection';
// Queries
import CompetencyOverviewQuery from 'totara_competency/graphql/perform_overview';
import CompetencyOverviewStatusQuery from 'totara_competency/graphql/perform_overview_by_state';

const STATE_ACHIEVED = 'achieved';
const STATE_NOT_PROGRESSED = 'not_progressed';
const STATE_NOT_STARTED = 'not_started';
const STATE_PROGRESSED = 'progressed';

export default {
  components: {
    ActionLink,
    ItemAchieved,
    ItemAchievementLevel,
    ItemAssigned,
    ItemAssignmentType,
    ItemUpdated,
    OverviewCount,
    OverviewDoughnut,
    OverviewStatusTable,
    Section,
  },

  props: {
    // URL to self-assign competencies page
    assignCompetenciesUrl: {
      type: String,
    },
    // Is this user viewing their own overview page
    currentUsersOverview: {
      type: Boolean,
    },
    // Time period filter value
    period: {
      type: Number,
    },
    // Outer layout is stacked
    stackedPage: {
      type: Boolean,
    },
    // URL to competencies content
    url: {
      required: true,
      type: String,
    },
    // User ID for users page being viewed
    userId: {
      type: Number,
    },
  },

  emits: ['display-banner'],

  data() {
    return {
      // Overview status competency data
      competenciesByStatus: {
        items: [],
      },
      // Overview competency data
      competencyOverview: {
        competencies: {
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
      return this.$apollo.queries.competencyOverview.loading;
    },

    /**
     * Are we currently querying overview data for a status via graphQL?
     *
     * @return {Boolean}
     */
    isOverviewStatusLoading() {
      return this.$apollo.queries.competenciesByStatus.loading;
    },

    /**
     * Are we currently querying first page of overview view all data via graphQL?
     *
     * @return {Boolean}
     */
    isOverviewStatusLoadingFirstPage() {
      return (
        this.$apollo.queries.competenciesByStatus.loading &&
        this.viewAllCurrentPage === 1
      );
    },
  },

  apollo: {
    competencyOverview: {
      query: CompetencyOverviewQuery,
      variables() {
        return {
          input: {
            filters: {
              id: this.userId,
              period: this.period,
            },
            sort_by: 'last_updated',
          },
        };
      },
      update({ totara_competency_perform_overview: data }) {
        this.initialLoad = false;
        this.togglePendingBanner(data.pending_changes);
        return data;
      },
    },

    competenciesByStatus: {
      query: CompetencyOverviewStatusQuery,
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
            sort_by: 'last_updated',
          },
        };
      },
      update({ totara_competency_perform_overview_by_state: data }) {
        this.viewAllLastPage = data.total <= data.items.length;
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
     * Load more competencies on the view all modal
     *
     */
    loadMore() {
      // Increase page number
      this.viewAllCurrentPage += 1;

      // Fetch additional data
      this.$apollo.queries.competenciesByStatus.fetchMore({
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
            sort_by: 'last_updated',
          },
        },
        updateQuery: (previousResult, { fetchMoreResult }) => {
          fetchMoreResult.totara_competency_perform_overview_by_state.items.unshift(
            ...previousResult.totara_competency_perform_overview_by_state.items
          );

          let numberOfCompetencies =
            fetchMoreResult.totara_competency_perform_overview_by_state.items
              .length;

          this.viewAllLastPage = fetchMoreResult.total <= numberOfCompetencies;

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

    /**
     * Show or hide the pending changes banner
     *
     * @param {Boolean} pendingChanges
     */
    togglePendingBanner(pendingChanges) {
      this.$emit('display-banner', {
        component: 'competencies',
        message: this.$str('overview_competencies_pending_changes', 'core_my'),
        show: pendingChanges,
        type: 'info',
      });
    },
  },
};
</script>

<style lang="scss">
.tui-overviewCompetenciesSection {
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

  &__noCompetencies {
    & > * + * {
      margin-top: var(--gap-4);
    }
  }
}
</style>
