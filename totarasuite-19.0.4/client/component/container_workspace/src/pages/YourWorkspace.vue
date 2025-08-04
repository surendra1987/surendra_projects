<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Qingyang Liu <gary.liu@totara.com>
  @module container_workspace
-->
<template>
  <Responsive
    :breakpoints="boundaries"
    class="tui-yourWorkspace"
    @responsive-resize="currentBoundaryName = $event"
  >
    <PageHeading :title="$str('your_spaces', 'container_workspace')">
      <template v-slot:buttons>
        <Button
          v-if="canCreateWorkspace"
          :text="$str('createnew', 'totara_engage')"
          @click="openModal = true"
        />
        <ActionLink
          :styleclass="{
            primary: true,
          }"
          :href="$url('/container/type/workspace/spaces.php')"
          :text="$str('find_spaces', 'container_workspace')"
        />
      </template>
    </PageHeading>

    <YourWorkspaceFilter
      :selected-sort="inner.selectedSort"
      :selected-access="inner.selectedAccess"
      :search-term="inner.searchTerm"
      :spaces-cursor="workspace.cursor"
      :spaces-is-loading="$apollo.queries.workspace.loading"
      class="tui-yourWorkspace__filter"
      @submit-search="updateFilter"
      @filter="updateFilter"
      @clear="updateFilter"
    />
    <template v-if="workspace.items.length > 0">
      <SpaceCardsGrid
        :max-grid-units="12"
        :workspace-units="cardUnits"
        :workspaces="workspace.items"
        :is-loading="$apollo.queries.workspace.loading"
        :cursor="workspace.cursor"
        class="tui-yourWorkspace__grid"
        @leave-workspace="leaveWorkspace"
        @loadmoreitems="loadMoreItems"
      />
    </template>
    <template v-else-if="showRecommended && !workspace.items.length">
      <RecommendSpaces
        class="tui-yourWorkspace__recommendedSpaces"
        @join-workspace="joinWorkspace"
      />
    </template>
  </Responsive>
  <ModalPresenter :open="openModal" @request-close="openModal = false">
    <WorkspaceModal @create-workspace="createWorkspace" />
  </ModalPresenter>
</template>

<script>
import PageHeading from 'tui/components/layouts/PageHeading';
import Responsive from 'tui/components/responsive/Responsive';
import YourWorkspaceFilter from 'container_workspace/components/filter/YourWorkspaceFilter';
import SpaceCardsGrid from 'container_workspace/components/grid/SpaceCardsGrid';
import { cardGrid } from 'container_workspace/index';
import RecommendSpaces from 'container_workspace/components/recommend/RecommendedSpaces';
import { config } from 'tui/config';
import ActionLink from 'tui/components/links/ActionLink';
import Button from 'tui/components/buttons/Button';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import WorkspaceModal from 'container_workspace/components/modal/WorkspaceModal';

// GraphQL queries
import findWorkspaces from 'container_workspace/graphql/find_workspaces';

export default {
  components: {
    SpaceCardsGrid,
    YourWorkspaceFilter,
    PageHeading,
    Responsive,
    ActionLink,
    Button,
    ModalPresenter,
    WorkspaceModal,
    RecommendSpaces,
  },

  props: {
    selectedSort: {
      type: String,
      required: true,
    },

    searchTerm: {
      type: String,
      default: '',
    },

    selectedAccess: {
      type: String,
      default: null,
    },

    canCreateWorkspace: {
      type: Boolean,
      required: true,
    },

    showRecommended: {
      type: Boolean,
      required: true,
    },

    creation: String,
  },

  data() {
    return {
      categoryInteractor: {},
      workspace: {
        cursor: {
          total: 0,
          next: null,
        },
        items: [],
      },

      // This is to cache the props inside the page. So that we won't change the props by any accidents.
      inner: {
        selectedSource: this.selectedSource,
        selectedSort: this.selectedSort,
        searchTerm: this.searchTerm,
        selectedAccess: this.selectedAccess,
      },

      currentBoundaryName: 'l',
      openModal: false,
    };
  },

  apollo: {
    workspace: {
      query: findWorkspaces,
      fetchPolicy: 'network-only',
      variables() {
        return {
          source: 'MEMBER_AND_OWNED',
          sort: this.inner.selectedSort,
          search_term: this.inner.searchTerm,
          access: this.inner.selectedAccess,
          theme: config.theme.name,
        };
      },

      /**
       *
       * @param {Object[]}      workspaces
       * @param {Object}        cursor
       * @returns {Object}
       */
      update({ workspaces, cursor }) {
        return {
          cursor: cursor,
          items: workspaces,
        };
      },
    },
  },

  computed: {
    boundaries() {
      return Object.values(cardGrid);
    },

    /**
     *
     * @returns {Number}
     */
    cardUnits() {
      if (!cardGrid[this.currentBoundaryName]) {
        // Default to 2.
        return 2;
      }

      return cardGrid[this.currentBoundaryName].cardUnits;
    },
  },

  mounted() {
    if (this.creation === 'cw') {
      this.openModal = true;
    }
  },

  methods: {
    /**

     * @param {String}        sort
     * @param {String|null}   searchTerm
     * @param {String|null}   access
     */
    updateFilter({ sort, searchTerm, access }) {
      this.inner.selectedSort = sort;
      this.inner.searchTerm = searchTerm;
      this.inner.selectedAccess = access;
    },

    async loadMoreItems() {
      if (!this.workspace.cursor.next) {
        return;
      }

      this.$apollo.queries.workspace.fetchMore({
        variables: {
          cursor: this.workspace.cursor.next,
          source: 'MEMBER_AND_OWNED',
          sort: this.inner.selectedSort,
          search_term: this.inner.searchTerm,
          access: this.inner.selectedAccess,
        },
        updateQuery: (previousResult, { fetchMoreResult }) => {
          const oldData = previousResult;
          const newData = fetchMoreResult;
          const newList = oldData.workspaces.concat(newData.workspaces);

          return {
            cursor: newData.cursor,
            workspaces: newList,
          };
        },
      });
    },

    createWorkspace() {
      this.$apollo.queries.workspace.refetch();
      this.openModal = false;
    },

    leaveWorkspace() {
      this.$apollo.queries.workspace.refetch();
    },

    /**
     *
     * @param {Number} workspaceId
     */
    joinWorkspace(workspaceId) {
      this.navigateToWorkspace({ id: workspaceId });
    },
  },
};
</script>

<style lang="scss">
.tui-yourWorkspace {
  &__filter {
    margin-top: var(--gap-8);
  }

  &__grid {
    margin-top: var(--gap-4);
  }

  &__recommendedSpaces {
    margin-top: var(--gap-12);
  }
}
</style>
