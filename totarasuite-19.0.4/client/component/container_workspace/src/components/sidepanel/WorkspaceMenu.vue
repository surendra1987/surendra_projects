<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Kian Nguyen <kian.nguyen@totaralearning.com>
  @module container_workspace
  @deprecated since Totara 19.0
-->

<template>
  <SidePanelNav
    v-model:value="innerSelectedWorkspaceId"
    class="tui-workspaceMenu"
    :aria-label="$str('workspace_navigation', 'container_workspace')"
  >
    <SidePanelNavGroup
      :title="$str('your_spaces', 'container_workspace')"
      class="tui-workspaceMenu__group"
    >
      <template v-slot:heading-side>
        <ContributeWorkspace
          v-if="canCreateWorkspace"
          :disabled="$apollo.queries.workspaces.loading"
          @create-workspace="addWorkspace"
        />
      </template>

      <template v-if="!$apollo.queries.workspaces.loading">
        <SidePanelNavLinkItem
          v-for="({ url, name, id, interactor }, index) in workspaces"
          :id="id"
          :key="index"
          :text="name"
          :url="url"
          :notification="innerSelectedWorkspaceId != id && !interactor.has_seen"
          :notification-text="
            $str('workspace_updated_notification', 'container_workspace', name)
          "
        />
      </template>
    </SidePanelNavGroup>
  </SidePanelNav>
</template>

<script>
import ContributeWorkspace from 'container_workspace/components/create/ContributeWorkspace';
import apolloClient from 'tui/apollo/client';
import SidePanelNav from 'tui/components/sidepanel/SidePanelNav';
import SidePanelNavGroup from 'tui/components/sidepanel/SidePanelNavGroup';
import SidePanelNavLinkItem from 'tui/components/sidepanel/SidePanelNavLinkItem';

// GraphQL queries
import getWorkspaces from 'container_workspace/graphql/my_workspace_urls';
import getCategoryInteractor from 'container_workspace/graphql/workspace_category_interactor';

export default {
  components: {
    SidePanelNavGroup,
    ContributeWorkspace,
    SidePanelNav,
    SidePanelNavLinkItem,
  },

  props: {
    selectedWorkspaceId: [Number, String],
  },

  emits: ['create-workspace'],

  apollo: {
    workspaces: {
      query: getWorkspaces,
      context: { batch: true },
    },

    categoryInteractor: {
      query: getCategoryInteractor,
      context: { batch: true },
      variables() {
        return {
          workspace_id: null,
        };
      },
      update({ category_interactor }) {
        return category_interactor;
      },
    },
  },

  data() {
    return {
      categoryInteractor: {},
      innerSelectedWorkspaceId: this.selectedWorkspaceId,
    };
  },

  computed: {
    /**
     * @returns {boolean}
     */
    canCreateWorkspace() {
      return this.categoryInteractor.can_create;
    },
  },

  watch: {
    selectedWorkspaceId(value) {
      this.innerSelectedWorkspaceId = value;
    },
  },

  methods: {
    /**
     *
     * @param {Object} workspace
     */
    addWorkspace(workspace) {
      const workspaces = Array.prototype.slice.call(this.workspaces);

      apolloClient.writeQuery({
        query: getWorkspaces,
        data: {
          workspaces: Array.prototype.concat.call(workspaces, [workspace]),
        },
      });

      // Update the workspace-menu selection.
      this.innerSelectedWorkspaceId = workspace.id;

      // Chaining it up to the parent.
      this.$emit('create-workspace', workspace);
    },

    /**
     * @deprecated since 13.5
     * @returns {boolean}
     */
    canCreate() {
      console.warn(
        'canCreate() method has been deprecated, please do not use it.'
      );
      return this.categoryInteractor.can_create;
    },
  },
};
</script>

<style lang="scss">
.tui-workspaceMenu {
  width: 100%;
  max-height: 100%;

  &__separator {
    width: 18%;
    margin-left: var(--gap-8);
    border: var(--border-width-thin) dashed var(--color-neutral-5);
  }
}
</style>
