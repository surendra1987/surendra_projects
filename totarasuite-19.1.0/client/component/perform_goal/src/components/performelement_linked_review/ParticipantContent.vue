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
  <div v-if="goal" class="tui-linkedReviewViewPerformGoal">
    <h3 class="tui-linkedReviewViewPerformGoal__title">
      <Button
        v-if="!preview && canView"
        :styleclass="{ transparent: true, primary: true }"
        :text="goal.name"
        @click="showGoalModal = true"
      />
      <template v-else>
        {{ goal.name }}
      </template>
    </h3>

    <div
      v-if="goal.description"
      class="tui-linkedReviewViewPerformGoal__description"
      v-html="goal.description"
    />

    <div class="tui-linkedReviewViewPerformGoal__overview">
      <div class="tui-linkedReviewViewPerformGoal__overview-updatedAt">
        {{ $str('updated_as_of', 'perform_goal', goal.updated_at) }}
      </div>

      <div class="tui-linkedReviewViewPerformGoal__overview-box">
        <!-- Preview (Admin view) -->
        <ContentPreview v-if="preview" :goal="goal" />

        <!-- Goal plugin type view -->
        <component :is="goalDetailsComponent" v-else :goal="goal" />
      </div>
    </div>

    <!-- Actions modal -->
    <ModalPresenter
      v-if="!preview && subjectUser"
      :open="showGoalModal"
      @request-close="hideGoalModal"
    >
      <GoalActionModal
        :id="goal.id"
        action="details"
        :show-actions="false"
        :subject-id="subjectUser.id"
        @request-close="hideGoalModal"
      />
    </ModalPresenter>
  </div>

  <div v-else class="tui-linkedReviewViewPerformGoalMissing">
    {{ $str('perform_review_goal_missing', 'perform_goal') }}
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ContentPreview from 'perform_goal/components/performelement_linked_review/PerformGoalLinkedReviewPreview';
import GoalActionModal from 'perform_goal/components/view/PerformGoalActionModal';
import ModalPresenter from 'tui/components/modal/ModalPresenter';

export default {
  components: {
    Button,
    ContentPreview,
    GoalActionModal,
    ModalPresenter,
  },

  props: {
    content: { type: Object },
    element: { type: Object },
    preview: { type: Boolean },
    subjectUser: { type: Object },
  },

  data() {
    return {
      showGoalModal: false,
    };
  },

  computed: {
    /**
     * Can this user view the goal details page
     *
     * @return {Boolean}
     */
    canView() {
      return this.content && this.content.permissions
        ? this.content.permissions.can_view
        : false;
    },

    /**
     * Gets the goal response data
     *
     * @return {Object}
     */
    goal() {
      if (!this.content) {
        return null;
      }

      if (this.content.name) {
        return this.content;
      } else if (this.content.goal) {
        return this.content.goal;
      }
      return null;
    },

    /**
     * Gets the vue component that displays the preview of selected goal
     *
     * @return {Function}
     */
    goalDetailsComponent() {
      if (!this.goal.plugin_name) {
        return null;
      }

      let componentPluginPath =
        'performgoal_type_' + this.goal.plugin_name + '/components/';

      return tui.asyncComponent(
        componentPluginPath + 'view/PerformGoalLinkedReviewPreview'
      );
    },
  },

  methods: {
    /**
     * Hide the goal modal
     */
    hideGoalModal() {
      this.showGoalModal = false;
    },
  },
};
</script>

<style lang="scss">
.tui-linkedReviewViewPerformGoal {
  display: flex;
  flex-direction: column;
  gap: var(--gap-4);

  &__title {
    @include font(h4);
    margin: 0;
  }

  &__overview {
    display: flex;
    flex-direction: column;
    gap: var(--gap-1);

    &-updatedAt {
      @include font(body-sm);
    }

    &-box {
      display: flex;
      padding: var(--gap-4);
      background: var(--color-neutral-1);
      border: var(--border-width-thin) solid var(--color-neutral-5);
    }
  }
}
</style>
