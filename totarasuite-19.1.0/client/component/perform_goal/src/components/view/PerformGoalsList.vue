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
  @package perform_goal
-->

<template>
  <Loader class="tui-performGoalsList" :loading="loading">
    <ActionCard
      v-for="item in goals"
      :key="item.goal.id"
      class="tui-performGoalsList__card"
      :no-padding="true"
      :no-wrap="true"
    >
      <template v-slot:card-body>
        <div class="tui-performGoalsList__card-content">
          <component
            :is="getGoalCardComponent(item.goal.plugin_name)"
            :goal="item.goal"
            @goal-clicked="$emit('open-details', item.goal.id)"
          />
        </div>
      </template>

      <template v-slot:card-action>
        <GoalActionsMenu
          v-if="item.permissions.can_manage"
          class="tui-performGoalsList__card-actions"
          :goal-id="item.goal.id"
          @goal-delete="$emit('delete', item.goal.id)"
          @goal-edit="$emit('open-edit', item.goal.id)"
          @goal-update="$emit('open-update', item.goal.id)"
        />
      </template>
    </ActionCard>
  </Loader>
</template>

<script>
// Components
import ActionCard from 'tui/components/card/ActionCard';
import GoalActionsMenu from 'perform_goal/components/view/GoalActionsMenu';
import Loader from 'tui/components/loading/Loader';
import SelectFilter from 'tui/components/filters/SelectFilter';

export default {
  components: {
    ActionCard,
    GoalActionsMenu,
    Loader,
    SelectFilter,
  },

  props: {
    // Goals data
    goals: { type: Array },
    // List currently loading
    loading: { type: Boolean },
  },

  emits: ['open-details', 'delete', 'open-edit', 'open-update'],

  methods: {
    /**
     * Gets the component path for the goal plugin type
     *
     * @param {String} plugin plugin name
     * @param {String} path component path
     * @return {String}
     */
    getComponentPluginPath(plugin, path) {
      return 'performgoal_type_' + plugin + '/components/' + path;
    },

    /**
     * Gets the vue component that displays the goal card
     *
     * @param {String} plugin plugin name
     * @return {Function}
     */
    getGoalCardComponent(plugin) {
      return tui.asyncComponent(
        this.getComponentPluginPath(plugin, 'view/PerformGoalCard')
      );
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalsList {
  display: flex;
  flex-direction: column;
  gap: var(--gap-4);

  &__card {
    &-content {
      display: flex;
      flex-grow: 1;
      padding: var(--gap-4);
    }

    &-actions {
      align-self: flex-start;
      padding: var(--gap-4);
    }
  }
}
</style>
