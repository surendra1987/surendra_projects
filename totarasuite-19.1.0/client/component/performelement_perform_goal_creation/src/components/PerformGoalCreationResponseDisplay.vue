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
  @module performelement_perform_goal_creation
-->
<template>
  <div class="tui-performGoalCreationResponseDisplay">
    <template v-if="dataFormatted.length">
      <Card
        v-for="(item, i) in dataFormatted"
        :key="i"
        class="tui-performGoalCreationResponseDisplay__item"
        :no-border="true"
      >
        <div class="tui-performGoalCreationResponseDisplay__itemHeading">
          <h3 class="tui-performGoalCreationResponseDisplay__itemHeading-title">
            <Button
              v-if="canManage || canView(item)"
              :text="item.goal.name"
              :styleclass="{ transparent: true, primary: true }"
              @click="
                $emit('view', {
                  id: item.goal.id,
                  showActionsMenu: showActionsMenu,
                })
              "
            />
            <template v-else>
              {{ item.goal.name }}
            </template>
          </h3>

          <GoalActionsMenu
            v-if="showActionsMenu"
            :goal-id="item.goal.id"
            @goal-delete="$emit('delete', item.goal.id)"
            @goal-edit="$emit('edit', item.goal.id)"
          />
        </div>

        <div class="tui-performGoalCreationResponseDisplay__itemContent">
          <div class="tui-performGoalCreationResponseDisplay__itemContent-date">
            {{
              $str(
                activeSectionIsClosed
                  ? 'perform_goal_as_of'
                  : 'perform_goal_updated_on',
                'performelement_perform_goal_creation',
                item.goal.updated_at
              )
            }}
          </div>

          <div class="tui-performGoalCreationResponseDisplay__itemContent-box">
            <component
              :is="getGoalDetailsComponent(item.goal.plugin_name)"
              :from-print="fromPrint"
              :goal="item.goal"
            />
          </div>
        </div>
      </Card>
    </template>

    <NoResponseSubmitted v-else-if="!excludeNoResponseMessage" />
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import Card from 'tui/components/card/Card';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownButton from 'tui/components/dropdown/DropdownButton';
import GoalActionsMenu from 'perform_goal/components/view/GoalActionsMenu';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import MoreIcon from 'tui/components/icons/More';
import NoResponseSubmitted from 'mod_perform/components/element/participant_form/NoResponseSubmitted';

export default {
  components: {
    Button,
    ButtonIcon,
    Card,
    Dropdown,
    DropdownButton,
    GoalActionsMenu,
    ModalPresenter,
    MoreIcon,
    NoResponseSubmitted,
  },

  props: {
    // Is the current section closed
    activeSectionIsClosed: { type: Boolean },
    // Array of selected goal data
    data: { type: Array, required: true },
    // Current section ID
    element: { type: Object, required: true },
    // Exclude no response message when a participant couldn't provide a response
    excludeNoResponseMessage: { type: Boolean },
    // Displaying from the print preview
    fromPrint: { type: Boolean },
    // Was this response provided by another user
    otherParticipantResponse: { type: Boolean },
    // Current element permissions
    permissions: { type: Object },
  },

  emits: ['view', 'delete', 'edit'],

  data() {
    return {
      // Current selected goal
      currentGoalId: null,

      // Plugin name of current selected goal
      currentPluginName: null,

      // Selected goal for deletion
      goalToDelete: null,

      // Should we be showing the delete modal?
      showDeleteModal: false,

      // Should we be showing the edit modal?
      showEditModal: false,
    };
  },

  computed: {
    /**
     * Can this user manage the response
     *
     * @return {Boolean}
     */
    canManage() {
      return this.permissions && this.permissions.can_manage;
    },

    /**
     * The goal data is either coming from the create goal modal, or from already submitted goals
     * The response data from submitted goals is JSON encoded so we need to parse those here
     *
     * @return {Array}
     */
    dataFormatted() {
      // If goal data is a string then it's JSON data from an existing response
      return this.data.map(goal =>
        typeof goal === 'string' ? JSON.parse(goal) : goal
      );
    },

    /**
     * Only show the actions menu to edit and delete if:
     * The activity isn't closed, this isn't another users response, and this user can manage the goal
     *
     * @return {Boolean}
     */
    showActionsMenu() {
      return (
        !this.activeSectionIsClosed &&
        !this.otherParticipantResponse &&
        !this.fromPrint &&
        this.canManage
      );
    },
  },

  methods: {
    /**
     * Can this user view the response
     *
     * @param {Object}
     * @return {Boolean}
     */
    canView(item) {
      return item.permissions && item.permissions.can_view;
    },

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
     * Gets the vue component that displays the created goal(s) details
     *
     * @param {String} plugin plugin name
     * @return {Function}
     */
    getGoalDetailsComponent(plugin) {
      return tui.asyncComponent(
        this.getComponentPluginPath(
          plugin,
          'view/PerformGoalActivityCreationDetails'
        )
      );
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalCreationResponseDisplay {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  gap: var(--gap-4);
  @include font(body-sm);

  &__item {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    gap: var(--gap-2) 0;
    padding: var(--gap-4);
    background: var(--color-neutral-3);
  }

  &__itemHeading {
    @include font(h4);
    display: flex;
    justify-content: space-between;

    &-title {
      @include font(h4);
      margin: 0;
    }
  }

  &__itemContent {
    display: flex;
    flex-direction: column;
    gap: var(--gap-1);

    &-date {
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
