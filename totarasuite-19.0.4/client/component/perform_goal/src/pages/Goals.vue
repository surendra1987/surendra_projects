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
  @package perform_goal
-->

<template>
  <Layout class="tui-performGoals" :title="$str('goals_title', 'perform_goal')">
    <template v-if="hasGoals" v-slot:header-buttons>
      <Button
        :styleclass="{ primary: 'true' }"
        :text="$str('goal_create', 'perform_goal')"
        @click="openActionModal('create')"
      />
    </template>

    <!-- Profile card -->
    <template v-slot:user-overview>
      <MiniProfileCard v-if="!currentUsersGoals" :display="user.card_display" />
    </template>

    <template v-slot:content>
      <!-- Empty state -->
      <div v-if="!$apollo.loading && !hasGoals" class="tui-performGoals__empty">
        <GoalIcon class="tui-performGoals__empty-icon" />

        <div class="tui-performGoals__empty-text">
          {{ noGoalsText }}
        </div>

        <Button
          :styleclass="{ primary: 'true' }"
          :text="$str('goal_create', 'perform_goal')"
          @click="openActionModal('create')"
        />
      </div>

      <!-- Content -->
      <div v-else-if="!initialLoad" class="tui-performGoals__content">
        <!-- Goal list -->
        <div class="tui-performGoals__goals">
          <h2 class="tui-performGoals__goals-heading">
            {{ goalsListHeading }}
          </h2>

          <div class="tui-performGoals__goals-content">
            <GoalsFilter
              v-model:value="filters"
              :filter-options="extraFilterOptions"
            />

            <div class="tui-performGoals__goals-results">
              <GoalsSortBar
                v-model:value="sortByFilter"
                :loading="showLoading"
                :total="goalCount"
                :sort-options="availableSortOptions"
              />

              <GoalsList
                :goals="goals"
                :loading="showLoading"
                @delete="openDeleteModal($event)"
                @open-details="openActionModal('details', $event)"
                @open-edit="openActionModal('edit', $event)"
                @open-update="openActionModal('update', $event)"
              />
            </div>
          </div>
        </div>
      </div>
    </template>

    <template v-slot:modals>
      <!-- Delete modal -->
      <GoalDeleteModal
        :id="goalId"
        :open="showDeleteModal"
        @cancel="hideDeleteModal"
        @handle-deleted="handleDeleted"
      />

      <!-- Actions modal -->
      <ModalPresenter :open="showActionModal" @request-close="hideActionModal">
        <GoalActionModal
          :id="goalId"
          :action="action"
          :subject-id="userId"
          @content-update="handleGoalContentUpdate"
          @created="handleGoalChange"
          @edited="handleGoalChange"
          @request-close="hideActionModal"
          @show-delete="openDeleteModal"
          @update="handleGoalChange"
        />
      </ModalPresenter>
    </template>
  </Layout>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import GoalActionModal from 'perform_goal/components/view/PerformGoalActionModal';
import GoalDeleteModal from 'perform_goal/components/manage/PerformGoalDeleteModal';
import GoalIcon from 'tui/components/icons/Goal';
import GoalsFilter from 'perform_goal/components/view/PerformGoalsFilter';
import GoalsList from 'perform_goal/components/view/PerformGoalsList';
import GoalsSortBar from 'perform_goal/components/view/PerformGoalsSortBar';
import Layout from 'tui/components/layouts/LayoutOneColumn';
import MiniProfileCard from 'tui/components/profile/MiniProfileCard';
import ModalPresenter from 'tui/components/modal/ModalPresenter';

// Util
import { notify } from 'tui/notifications';

// GraphQL
import PerformGoalsQuery from 'perform_goal/graphql/user_goals';

export default {
  components: {
    Button,
    GoalActionModal,
    GoalDeleteModal,
    GoalIcon,
    GoalsFilter,
    GoalsList,
    GoalsSortBar,
    Layout,
    MiniProfileCard,
    ModalPresenter,
  },

  props: {
    // Available goal statues from all goal types
    availableGoalStatuses: { type: Array },
    // Sort options for goal list
    availableSortOptions: { type: Array },
    // Logged in users ID
    currentUserId: { type: Number },
    // Sort options default value
    defaultSortOption: { type: String },
    // Open page to particular goal
    openGoal: { type: String },
    // Page subject
    user: { type: Object },
    // Page subjects ID
    userId: { type: [String, Number] },
  },

  data() {
    return {
      // Action modal view
      action: null,
      // Is the goal currently being deleted
      deleting: false,
      extraFilterOptions: {
        status: this.availableGoalStatuses,
      },
      // Goal filter values
      filters: {
        bar: {
          search: '',
        },
        extra: {
          status: [],
        },
      },
      // List of users goals
      goals: [],
      goalCount: 0,
      // The id of the selected goal
      goalId: null,
      // Does the user have existing goals
      hasGoals: false,
      initialLoad: true,
      showActionModal: false,
      showDeleteModal: false,
      // Don't show loading state for certain actions
      silentUpdate: false,
      // Sort filter option values
      sortByFilter: this.defaultSortOption,
    };
  },

  computed: {
    /**
     * Active filter options (reactive value for the query)
     *
     * @return {Object}
     */
    currentFilterOptions() {
      return {
        search: this.filters.bar.search,
        status: this.filters.extra.status,
        user: { id: this.userId },
      };
    },

    /**
     * Is the current user viewing their own goals page
     *
     * @return {Boolean}
     */
    currentUsersGoals() {
      return this.user.id === this.currentUserId;
    },

    /**
     * Goals list heading depending on own or other users page
     */
    goalsListHeading() {
      return this.currentUsersGoals
        ? this.$str('personal_goals_heading', 'perform_goal')
        : this.$str(
            'personal_goals_heading_for_user',
            'perform_goal',
            this.user.fullname
          );
    },

    /**
     * No goals text depending on own or other users page
     */
    noGoalsText() {
      return this.currentUsersGoals
        ? this.$str('no_goals', 'perform_goal')
        : this.$str('no_goals_for_user', 'perform_goal', this.user.fullname);
    },

    /**
     * Display loading state
     */
    showLoading() {
      return this.$apollo.loading && !this.silentUpdate;
    },
  },

  mounted() {
    // Open goal if URL param provided
    if (this.openGoal) {
      this.openActionModal('details', this.openGoal);
    }
  },

  /**
   * Fetch the latest goal data
   *
   */
  apollo: {
    goals: {
      query: PerformGoalsQuery,
      variables() {
        return {
          input: {
            filters: this.currentFilterOptions,
            pagination: {
              // Until pagination is implemented in the front end, we have to make sure we get all the results,
              // so set an unreasonable high limit.
              limit: 100000,
            },
            options: {
              sort_by: this.sortByFilter,
            },
          },
        };
      },
      update({ perform_goal_user_goals: data }) {
        this.initialLoad = false;
        this.hasGoals = data.has_goals;
        this.goalCount = data.total;
        this.silentUpdate = false;
        return data.items;
      },
    },
  },

  methods: {
    /**
     * Goal has been deleted
     */
    handleDeleted() {
      this.hideDeleteModal();
      this.hideActionModal();
      this.handleGoalChange({
        message: this.$str('goal_delete_modal_success', 'perform_goal'),
      });
    },

    /**
     * Goal content has been updated (comment/task counts)
     *
     */
    handleGoalContentUpdate(change) {
      this.silentUpdate = true;
      this.handleGoalChange(change);
    },

    /**
     * Goal has been modified
     *
     */
    handleGoalChange(change) {
      if (change.message) {
        notify({
          message: change.message,
          type: 'success',
        });
      }

      if (change.data && change.data.goal) {
        this.goalId = change.data.goal.id;
      }

      this.$apollo.queries.goals.refetch();
    },

    /**
     * Hide the action goal modal
     */
    hideActionModal() {
      this.showActionModal = false;
    },

    /**
     * Hide the delete goal modal
     */
    hideDeleteModal() {
      this.showDeleteModal = false;
    },

    /**
     * Open the action goal modal
     *
     * @param {String} action details, edit and update
     * @param {Number} goalId
     */
    openActionModal(action, goalId) {
      this.action = action;
      this.goalId = goalId;
      this.showActionModal = true;
    },

    /**
     * Open the delete goal modal
     *
     * @param {Number} goalId
     */
    openDeleteModal(goalId) {
      if (goalId) {
        this.goalId = goalId;
      }
      this.showDeleteModal = true;
    },
  },
};
</script>

<style lang="scss">
.tui-performGoals {
  min-height: 500px;

  &__empty {
    display: flex;
    flex-direction: column;
    gap: var(--gap-4);
    align-items: center;

    &-icon {
      color: var(--color-primary);
      font-size: rem-px(64);
    }

    &-text {
      @include font(h4);
      text-align: center;
    }
  }

  &__goals {
    display: flex;
    flex-direction: column;
    gap: var(--gap-4);

    &-heading {
      @include font(h4);
      margin: 0;
    }

    &-cardContent {
      padding: var(--gap-4);
    }

    &-cardActions {
      align-self: flex-start;
      padding: var(--gap-4);
    }

    &-content {
      display: flex;
      flex-direction: column;
      gap: var(--gap-8);
    }

    &-results {
      display: flex;
      flex-direction: column;
      gap: var(--gap-4);
    }
  }
}

@media screen and (min-width: $tui-screen-xs) {
  .tui-performGoals {
    &__empty {
      &-icon {
        font-size: rem-px(88);
      }

      &-text {
        @include font(h3);
      }
    }
  }
}
</style>
