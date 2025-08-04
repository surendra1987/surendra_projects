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

  @author Johannes Cilliers <johannes.cilliers@totaralearning.com>
  @module container_workspace
-->

<template>
  <div>
    <ConfirmationModal
      :open="warning.modal"
      :title="$str('warning_change_title', 'container_workspace')"
      :confirm-button-text="$str('continue', 'core')"
      :loading="sharingItems"
      @confirm="shareItems(currentItems)"
      @cancel="closeWarning"
    >
      {{ warning.message }}
    </ConfirmationModal>

    <EngageAdderModal
      :title="$str('workspace:add_library', 'container_workspace')"
      :open="showAdder"
      :cards="contribution.cards"
      :cursor="contribution.cursor"
      :filter-value="filterValue"
      filter-component="container_workspace"
      filter-area="adder"
      :show-loading-btn="addingItems || sharingItems"
      @load-more="loadMoreItems"
      @added="processAddingItems"
      @cancel="$emit('close', $event)"
      @topic="filterTopic"
      @search="filterSearch"
      @section="filterSection"
    />

    <CourseAdder
      :open="showCourseAdder"
      :show-loading-btn="addingItems || sharingItems"
      :existing-items="sharedCourseIds"
      @added="processAddingCourses"
      @cancel="$emit('close', $event)"
    />
  </div>
</template>

<script>
import CourseAdder from 'core_course/components/course_adder/CourseAdder';
import EngageAdderModal from 'totara_engage/components/modal/EngageAdderModal';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import { config } from 'tui/config';

// GraphQL
import shareWithRecipient from 'totara_engage/graphql/share_with_recipient';
import contributionCards from 'container_workspace/graphql/contribution_cards';
import checkLibraryAccess from 'container_workspace/graphql/check_share_access_for_library';
import sharedCourseIdsQuery from 'engage_course/graphql/ids_shared_to_workspace';
import shareCoursesMutation from 'engage_course/graphql/share_with_recipient_by_ids';

// Mixins
import ContributionMixin, {
  contributionMixinData,
} from 'totara_engage/mixins/contribution_mixin';
export default {
  components: {
    CourseAdder,
    EngageAdderModal,
    ConfirmationModal,
  },

  mixins: [ContributionMixin],

  props: {
    workspaceId: {
      type: [String, Number],
      required: true,
    },
    showAdder: Boolean,
    showCourseAdder: Boolean,
  },

  emits: ['close'],

  data() {
    return {
      ...contributionMixinData(),
      // This variable is to keep track of the current selection.
      currentItems: [],
      sharingItems: false,
      addingItems: false,
      warning: {
        message: '',
        modal: false,
      },
      courseAdderVisible: false,
      sharedCourseIds: [],
    };
  },

  watch: {
    showAdder(value) {
      this.skipCardsQuery = !value;
    },

    async showCourseAdder(value) {
      if (value) {
        const result = await this.$apollo.query({
          query: sharedCourseIdsQuery,
          variables: { workspace_id: this.workspaceId },
          fetchPolicy: 'network-only',
        });
        this.sharedCourseIds = result.data.ids;
        if (this.showCourseAdder) {
          this.courseAdderVisible = true;
        }
      } else {
        this.courseAdderVisible = false;
      }
    },
  },

  created() {
    // Overwrite values defined in ContributionMixin.
    this.skipCardsQuery = true;
  },

  apollo: {
    contribution: {
      query: contributionCards,
      fetchPolicy: 'network-only',
      variables() {
        return Object.assign({}, this.filterValue, {
          workspace_id: this.workspaceId,
          area: 'adder',
          include_footnotes: false,
          image_preview_mode: 'totara_engage_adder_thumbnail',
          theme: config.theme.name,
        });
      },
      update({ contribution: { cursor, cards } }) {
        return {
          cursor: cursor,
          cards: this.canContribute ? this.$_addContributeCard(cards) : cards,
        };
      },
      skip() {
        return this.skipCardsQuery;
      },
    },
  },

  methods: {
    /**
     * @param {String[]} selection
     */
    async processAddingItems(selection) {
      if (this.addingItems) {
        return;
      }
      this.addingItems = true;
      const items = selection.map(item => JSON.parse(item));
      this.currentItems = items;

      let warning, message;
      try {
        ({ warning, message } = await this.checkAccessSetting(items));
      } finally {
        this.addingItems = false;
      }

      if (warning) {
        this.warning.message = message;
        this.warning.modal = true;

        this.$emit('close');
        return;
      }

      await this.shareItems(items);
    },

    /**
     * @param {String[]} selection
     */
    async processAddingCourses(selection) {
      if (this.sharingItems) {
        return;
      }

      this.sharingItems = true;

      try {
        await this.$apollo.mutate({
          mutation: shareCoursesMutation,
          refetchQueries: ['container_workspace_shared_cards'],
          variables: {
            ids: selection.ids,
            recipient: {
              instanceid: this.workspaceId,
              component: 'container_workspace',
              area: 'library',
            },
          },
        });
      } finally {
        this.sharingItems = false;
        this.$emit('close');
      }
    },

    /**
     * Check the access settings of the items before actually submitting the sharing item to the workspaces.
     * @param {Object} items
     * @return {{warning: String, message: String}}
     */
    async checkAccessSetting(items) {
      const {
        data: {
          result: { warning, message },
        },
      } = await this.$apollo.query({
        query: checkLibraryAccess,
        variables: {
          items: items,
          workspace_id: this.workspaceId,
        },
      });

      return {
        warning: warning,
        message: message,
      };
    },

    /**
     * Note that we do not want to usee the local variable {@see currentItems} because if there is something wrong
     * with the cache invalidation then currentIems will just cause some un-expected behaviour.
     *
     * @param {Object[]} items
     * @return {Promise<void>}
     */
    async shareItems(items) {
      if (this.sharingItems) {
        return;
      }

      this.sharingItems = true;

      try {
        await this.$apollo.mutate({
          mutation: shareWithRecipient,
          refetchQueries: ['container_workspace_shared_cards'],
          variables: {
            items: items,
            recipient: {
              instanceid: this.workspaceId,
              component: 'container_workspace',
              area: 'library',
            },
          },
        });

        this.warning.modal = false;
        this.warning.message = '';
      } finally {
        this.$emit('close');
        this.sharingItems = false;
      }
    },

    closeWarning() {
      this.warning.modal = false;
      this.warning.message = '';

      this.currentItems = [];
    },

    async loadMoreItems() {
      if (!this.contribution.cursor.next) {
        return;
      }

      this.$apollo.queries.contribution.fetchMore({
        variables: {
          cursor: this.contribution.cursor.next,
          workspace_id: this.workspaceId,
          area: 'adder',
          include_footnotes: false,
          image_preview_mode: 'totara_engage_adder_thumbnail',
          theme: config.theme.name,
        },
        updateQuery: (previousResult, { fetchMoreResult }) => {
          const oldData = previousResult;
          const newData = fetchMoreResult;
          const newList = oldData.contribution.cards.concat(
            newData.contribution.cards
          );

          return {
            contribution: {
              cursor: newData.contribution.cursor,
              cards: newList,
            },
          };
        },
      });
    },
  },
};
</script>
