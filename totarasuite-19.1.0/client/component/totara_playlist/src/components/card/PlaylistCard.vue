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
  @module totara_playlist
-->

<template>
  <BaseCard
    component-name="totara_playlist"
    :bookmarked="innerBookmarked"
    :show-bookmark="showBookmark"
    :extra="extraData"
    :instance-id="instanceId"
    :footnotes="footnotes"
    :show-footnotes="showFootnotes"
  >
    <template v-slot="{ actionList }">
      <LearningCard
        class="tui-playlistCard__cardWrapper"
        variant="grey-body"
        :title="name"
        :href="url"
        image-background-color="default"
        :image="extraData.image"
        :actions="actionList"
      >
        <template v-slot:media-centre-overlay>
          <div class="tui-playlistCard__numberOfResourcesContainer">
            <div class="tui-playlistCard__numberOfResources">
              <p>{{ extraData.resources }}</p>
            </div>
          </div>
        </template>
        <template v-slot:body>
          <div class="tui-playlistCard__subtitleRow">
            <div class="tui-playlistCard__subtitle">
              {{ $str('playlist', 'totara_engage') }}
            </div>
            <div>
              <AccessIcon
                v-if="access != 'PUBLIC'"
                :access="access"
                custom-class="tui-playlistCard__visibilityIcon"
              />
            </div>
          </div>
        </template>
        <template v-slot:footer>
          <div class="tui-playlistCard__footer">
            <div class="tui-playlistCard__statusIcons">
              <StatIcon
                v-for="statIcon in statIcons"
                :key="statIcon.type"
                :title="statIcon.title"
                :stat-number="statIcon.statNumber"
              >
                <component
                  :is="statIcon.icon"
                  :title="statIcon.title"
                  size="100"
                />
              </StatIcon>
            </div>
          </div>
        </template>
      </LearningCard>
    </template>
  </BaseCard>
</template>

<script>
import BaseCard from 'totara_engage/components/card/BaseCard';
import LearningCard from 'tui/components/card/LearningCard';
import StatIcon from 'totara_engage/components/icons/StatIcon';
import StarRating from 'totara_engage/components/icons/StarRating';
import ShareIcon from 'tui/components/icons/Share';
import CommentIcon from 'tui/components/icons/Comment';
import { cardMixin, AccessManager } from 'totara_engage/index';
import AccessIcon from 'totara_engage/components/icons/access/computed/AccessIcon';
import RatingIcon from 'tui/components/icons/Rating';

// GraphQL
import updateBookmark from 'totara_engage/graphql/update_bookmark';

export default {
  components: {
    AccessIcon,
    BaseCard,
    LearningCard,
    CommentIcon,
    ShareIcon,
    StarRating,
    StatIcon,
  },

  mixins: [cardMixin],

  emits: ['remove-resource'],

  data() {
    return {
      // Assign to the inner property, so that we don't have to mutate the parent.
      innerBookmarked: this.bookmarked,
      hovered: false,
      extraData: JSON.parse(this.extra),
      statIcons: [],
    };
  },

  computed: {
    showRating() {
      return this.rating;
    },
    starTitle() {
      if (this.extraData.ratingCount <= 1) {
        return this.$str(
          'numberofpersonrating',
          'totara_engage',
          this.extraData.ratingCount
        );
      }

      return this.$str(
        'numberofpeoplerating',
        'totara_engage',
        this.extraData.ratingCount
      );
    },
    imageStyle() {
      return {
        backgroundImage: `url(${this.extraData.image}})`,
      };
    },
  },

  created() {
    this.$_setStatIcons();
  },

  methods: {
    /**
     * Changing the state of image hovering.
     * @param {boolean} value
     */
    $_handleHovered(value) {
      this.hovered = value;
    },

    $_setStatIcons() {
      if (AccessManager.isPrivate(this.access)) {
        return;
      }

      const restrictedStatIcons = [
        {
          type: 'stars',
          icon: RatingIcon,
          title: this.$str(
            'ratingsforscreenreader',
            'totara_engage',
            this.rating?.toFixed(1)
          ),
          statNumber: this.rating,
        },
        {
          type: 'comment',
          icon: CommentIcon,
          title: this.$str(
            'numberofcomments',
            'totara_engage',
            this.totalComments
          ),
          statNumber: this.totalComments,
        },
      ];

      if (AccessManager.isRestricted(this.access)) {
        this.statIcons = restrictedStatIcons;
        return;
      }

      if (AccessManager.isPublic(this.access)) {
        this.statIcons = restrictedStatIcons.concat({
          type: 'share',
          icon: ShareIcon,
          title: this.$str(
            'numberofshares',
            'totara_engage',
            this.sharedbycount
          ),
          statNumber: this.sharedbycount,
        });
        return;
      }
    },

    updateBookmark() {
      this.innerBookmarked = !this.innerBookmarked;
      this.$apollo.mutate({
        mutation: updateBookmark,
        refetchAll: false,
        refetchQueries: [
          'totara_playlist_playlist_links',
          'totara_engage_contribution_cards',
        ],
        variables: {
          itemid: this.instanceId,
          component: 'totara_playlist',
          bookmarked: this.innerBookmarked,
        },
      });
    },
  },
};
</script>

<style lang="scss">
.tui-playlistCard {
  &__cardWrapper {
    flex-grow: 1;
  }

  &__numberOfResourcesContainer {
    position: absolute;
    top: 0;
    left: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
  }

  &__numberOfResources {
    width: rem-px(28);
    height: rem-px(28);
    background-color: var(--color-neutral-1);
    border-radius: 50%;

    p {
      margin: 0 auto;
      padding: 0;
      font-weight: 700;
      font-size: font-size-px(12);
      line-height: rem-px(28);
      text-align: center;
    }
  }

  &__subtitleRow {
    display: flex;
    flex-flow: row wrap;
    gap: gap(1);
  }

  &__subtitle {
    @include font(body-sm);
    flex: 1;
  }

  &__footer {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: rem-px(32);
  }

  &__statusIcons {
    display: flex;
    gap: gap(2);
  }

  // hide status icons until hover if the current pointing device can hover
  @media (hover: hover) {
    &__statusIcons {
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
    }

    &__cardWrapper:focus-within &__statusIcons,
    &__cardWrapper:hover &__statusIcons {
      opacity: 1;
    }
  }
}
</style>
