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
  @module totara_engage
-->

<template>
  <div
    class="tui-contributionBaseContent"
    :class="{
      'tui-contributionBaseContent--small': currentBoundaryName === 'small',
    }"
  >
    <section v-if="showHeading" class="tui-contributionBaseContent__navButtons">
      <slot name="buttons" />
    </section>

    <section v-if="showHeading" class="tui-contributionBaseContent__header">
      <div class="tui-contributionBaseContent__title">
        <slot name="heading" />
      </div>
      <slot name="bookmark" />
      <!-- creation buttons slot only available for your library component.-->
      <slot v-if="fromLibrary" name="creationButtons" />
    </section>

    <!-- section filters -->
    <section>
      <slot name="navigation" />
    </section>

    <!-- .tui-contributionFilter -->
    <slot name="filters" />

    <section
      v-show="(!loading || loadingMore) && !showEmptyContribution"
      class="tui-contributionBaseContent__counterContainer"
    >
      <div class="tui-contributionBaseContent__counter">
        <template v-if="customTitle">
          {{ customTitle }}
        </template>
        <template v-else>
          {{ countResource }}
        </template>
      </div>
    </section>

    <section
      v-show="!loading || loadingMore"
      class="tui-contributionBaseContent__cards"
    >
      <slot name="cards">
        <template v-if="showEmptyContent && cards.length === 0">
          <h4 class="tui-contributionBaseContent__emptyText">
            <template v-if="showEmptyContribution">
              {{ customEmptyContent }}
            </template>
            <template v-else>
              {{ $str('emptycontent', 'totara_engage') }}
            </template>
          </h4>
        </template>
        <CardsGrid
          v-else
          :cards="cards"
          :is-loading="loading"
          :show-footnotes="showFootnotes"
          @scrolledtobottom="scrolledToBottom"
        />
        <div
          v-if="isLoadMoreVisible && count < totalCards && !loading"
          class="tui-contributionBaseContent__loadMoreContainer"
        >
          <div class="tui-contributionBaseContent__viewedResources">
            <template v-if="customLoadMoreText">
              {{ customLoadMoreText }}
            </template>
            <template v-else>
              {{ $str('viewedresources', 'engage_article', count) }}
              {{ $str('resourcecount', 'totara_engage', totalCards) }}
            </template>
          </div>
          <Button
            class="tui-contributionBaseContent__loadMore"
            :text="$str('loadmore', 'engage_article')"
            @click="loadMore"
          />
        </div>
      </slot>
    </section>

    <PageLoader :fullpage="false" :loading="loading && !loadingMore" />
  </div>
</template>

<script>
import CardsGrid from 'totara_engage/components/contribution/CardsGrid';
import Button from 'tui/components/buttons/Button';
import PageLoader from 'tui/components/loading/Loader';

export default {
  components: {
    CardsGrid,
    Button,
    PageLoader,
  },

  props: {
    loading: {
      type: Boolean,
      required: true,
    },
    loadingMore: {
      type: Boolean,
      required: true,
    },
    isLoadMoreVisible: {
      type: Boolean,
      default: false,
    },
    cards: {
      type: Array,
      default: () => [],
    },
    totalCards: Number,
    showHeading: {
      type: Boolean,
      default: true,
    },
    showFootnotes: {
      type: Boolean,
      default: false,
    },
    customTitle: String,
    customLoadMoreText: String,
    showEmptyContent: {
      type: Boolean,
      default: false,
    },
    showEmptyContribution: Boolean,
    fromLibrary: Boolean,
    customEmptyContent: String,
    currentBoundaryName: String,
    canAdd: {
      type: Boolean,
      default: false,
    },
  },

  emits: ['scrolled-to-bottom', 'load-more'],

  data() {
    return {
      initialLoad: true,
    };
  },

  computed: {
    countResource() {
      if (this.totalCards === 1)
        return this.$str(
          this.fromLibrary ? 'itemscountone' : 'resourcecountone',
          'totara_engage',
          this.totalCards
        );
      return this.$str(
        this.fromLibrary ? 'itemscount' : 'resourcecount',
        'totara_engage',
        this.totalCards
      );
    },

    count() {
      // If a user can add an item, then the cards length includes the add card
      return this.canAdd ? this.cards.length - 1 : this.cards.length;
    },
  },

  watch: {
    cards() {
      this.initialLoad = false;
    },
  },

  methods: {
    scrolledToBottom() {
      this.$emit('scrolled-to-bottom');
    },

    loadMore() {
      this.$emit('load-more');
    },
  },
};
</script>

<style lang="scss">
.tui-contributionBaseContent {
  &__counterContainer {
    padding: var(--gap-4) 0;
  }

  &__header {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    gap: var(--gap-3);
    align-items: flex-start;
    margin: var(--gap-4) 0;

    > :last-child {
      align-self: center;
    }
  }

  &__title {
    @include font(h1);
    flex-basis: auto;
    flex-grow: 1;
  }

  &__filter {
    display: flex;
    flex-direction: column;
    margin-top: var(--gap-4);
    margin-bottom: var(--gap-4);
  }

  &__cards {
    margin-top: var(--gap-1);
    & > * + * {
      margin-top: var(--gap-2);
    }
  }

  &__loadMoreContainer {
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  &__viewedResources {
    display: flex;
    align-self: center;
    margin-bottom: var(--gap-1);
  }

  &__loadMore {
    display: flex;
    align-self: center;
  }

  &__counter {
    @include font(h4);
  }

  &__emptyText {
    @include font(body);
    margin-top: var(--gap-2);
  }

  &__header + &__counterContainer {
    margin-top: var(--gap-10);
    padding-bottom: 0;
  }
  &__header + .tui-contributionFilter {
    margin-top: var(--gap-12);
  }
  .tui-contributionFilter + &__counterContainer {
    padding-top: var(--gap-4);
  }
  .tui-contributionFilter--hasSortBy + &__counterContainer {
    margin-top: calc(var(--gap-7) * -1);
    padding-top: 0;
  }
  &__header + .tui-totara_engage-filterBarArea {
    margin-top: var(--gap-12);
  }
  .tui-totara_engage-filterBarArea + &__counterContainer {
    padding-top: var(--gap-4);
  }
  .tui-totara_engage-filterBarArea--hasSortBy + &__counterContainer {
    margin-top: calc(var(--gap-7) * -1);
    padding-top: 0;
  }

  &--small {
    .tui-contributionBaseContent__header {
      margin: var(--gap-4);
    }

    .tui-contributionBaseContent__counterContainer {
      padding: var(--gap-4);
    }

    .tui-contributionBaseContent__cards {
      padding: 0 var(--gap-4);
    }
  }
}
</style>
