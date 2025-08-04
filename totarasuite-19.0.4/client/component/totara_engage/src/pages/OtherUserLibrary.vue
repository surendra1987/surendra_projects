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

  @author Alvin Smith <alvin.smith@totaralearning.com>
  @module totara_engage
-->

<template>
  <Responsive
    :breakpoints="[
      { name: 'small', boundaries: [0, 768] },
      { name: null, boundaries: [768, 1672] },
    ]"
    @responsive-resize="resize"
  >
    <div class="tui-otherUserLibrary">
      <ContributionBaseContent
        :loading="$apollo.loading"
        :loading-more="loadingMore"
        :cards="contribution.cards"
        :show-footnotes="false"
        :is-load-more-visible="isLoadMoreVisible"
        :total-cards="contribution.cursor.total"
        :custom-title="countSharedResource"
        :custom-load-more-text="loadMoreText"
        :show-empty-content="filterChange"
        :show-empty-contribution="showEmptyContribution"
        :custom-empty-content="$str('nocontributions', 'totara_engage', name)"
        @scrolled-to-bottom="scrolledToBottom"
        @load-more="loadMore"
      >
        <template v-slot:buttons>
          <a v-if="profileUrl" :href="profileUrl" :title="name">
            <BackArrow class="tui-otherUserLibrary__backArrow" size="200" />
            <span> {{ name }} </span>
          </a>
        </template>

        <template v-slot:heading>
          <h1>{{ $str('usersresources', 'totara_engage', name) }}</h1>
        </template>

        <template v-slot:filters>
          <ContributionFilter
            v-if="contributionCount"
            component="totara_engage"
            area="otheruserlib"
            :has-bottom-bar="true"
            :has-top-bar="true"
            :value="filterValue"
            :show-access="false"
            :show-type="true"
            :show-topic="true"
            @access="filterAccess"
            @type="filterType"
            @topic="filterTopic"
            @sort="filterSort"
          />
        </template>
      </ContributionBaseContent></div
  ></Responsive>
</template>

<script>
import ContributionBaseContent from 'totara_engage/components/contribution/BaseContent';
import ContributionFilter from 'totara_engage/components/contribution/Filter';
import BackArrow from 'tui/components/icons/BackArrow';
import Responsive from 'tui/components/responsive/Responsive';

import LibraryMixin from 'totara_engage/mixins/library_mixin';
import OtherContributionMixin from 'totara_engage/mixins/other_contribution_mixin';

export default {
  components: {
    ContributionBaseContent,
    ContributionFilter,
    BackArrow,
    Responsive,
  },

  mixins: [OtherContributionMixin, LibraryMixin],

  props: {
    userId: {
      type: Number,
      required: true,
    },
    name: {
      type: String,
      required: true,
    },
    profileUrl: String,
  },

  data() {
    return {
      currentBoundaryName: null,
    };
  },

  computed: {
    loadMoreText() {
      return `${this.$str(
        'viewedresources',
        'engage_article',
        this.contribution.cards.length
      )} ${this.$str(
        'itemscount',
        'totara_engage',
        this.contribution.cursor.total
      )}`;
    },
    countSharedResource() {
      if (this.contribution.cursor.total === 1)
        return this.$str(
          'itemscountone',
          'totara_engage',
          this.contribution.cursor.total
        );
      return this.$str(
        'itemscount',
        'totara_engage',
        this.contribution.cursor.total
      );
    },
    filterChange() {
      const { type, topic, sort } = this.filterValue;
      return type !== null || topic !== null || sort !== 5;
    },
    showEmptyContribution() {
      return this.contributionCount === 0;
    },
  },

  created() {
    this.contributionComponent = 'totara_engage';
  },

  methods: {
    resize(boundaryName) {
      this.currentBoundaryName = boundaryName;
    },
  },
};
</script>

<style lang="scss">
.tui-otherUserLibrary {
  &__header {
    padding-left: 0;
  }

  &__backArrow {
    margin-right: gap(2);
  }
}
</style>
