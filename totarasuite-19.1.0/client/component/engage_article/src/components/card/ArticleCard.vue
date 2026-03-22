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
  @module engage_article
-->

<template>
  <BaseCard
    component-name="engage_article"
    :bookmarked="innerBookmarked"
    :show-bookmark="showBookmark"
    :extra="extraData"
    :instance-id="instanceId"
    :footnotes="footnotes"
    :show-footnotes="showFootnotes"
  >
    <template v-slot="{ actionList }">
      <LearningCard
        class="tui-engageArticleCard__cardWrapper"
        variant="grey-body"
        :title="name"
        :href="url"
        :image="extraData.image"
        :actions="actionList"
      >
        <template v-slot:hero="{ popFront }">
          <DragHandleIcon
            v-if="itemDraggable"
            :class="['tui-engageArticleCard__drag', popFront]"
          />
        </template>
        <template v-slot:body>
          <div class="tui-engageArticleCard__content">
            <div class="tui-engageArticleCard__subtitle">
              {{ $str('resource', 'totara_engage') }}
            </div>
            <div class="tui-engageArticleCard__subtitleRight">
              <div
                v-if="extraData.timeview"
                class="tui-engageArticleCard__time"
              >
                <TimeIcon
                  size="100"
                  :alt="$str('time', 'totara_engage')"
                  custom-class="tui-icon--dimmed"
                />
                <span class="tui-engageArticleCard__time-text">
                  {{ getTimeView }}
                </span>
              </div>
              <AccessIcon
                v-if="access != 'PUBLIC'"
                :access="access"
                custom-class="tui-engageArticleCard__visibilityIcon"
              />
            </div>
          </div>
        </template>
        <template v-slot:footer>
          <div class="tui-engageArticleCard__footer">
            <div class="tui-engageArticleCard__statusIcons">
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
import AccessIcon from 'totara_engage/components/icons/access/computed/AccessIcon';
import AddToListIcon from 'tui/components/icons/AddToList';
import BaseCard from 'totara_engage/components/card/BaseCard';
import { cardMixin, AccessManager, TimeViewType } from 'totara_engage/index';
import CommentIcon from 'tui/components/icons/Comment';
import DragHandleIcon from 'tui/components/icons/DragHandle';
import LearningCard from 'tui/components/card/LearningCard';
import LikeIcon from 'tui/components/icons/Like';
import MoreIcon from 'tui/components/icons/More';
import StatIcon from 'totara_engage/components/icons/StatIcon';
import ShareIcon from 'tui/components/icons/Share';
import TimeIcon from 'tui/components/icons/Time';

export default {
  components: {
    AccessIcon,
    AddToListIcon,
    BaseCard,
    DragHandleIcon,
    LearningCard,
    MoreIcon,
    ShareIcon,
    StatIcon,
    TimeIcon,
  },

  mixins: [cardMixin],

  data() {
    return {
      extraData: JSON.parse(this.extra),
      // Assign the value to the inner child, as we do not want to mutate the prop.
      innerBookmarked: this.bookmarked,
      statIcons: [],
    };
  },

  computed: {
    getTimeView() {
      if (TimeViewType.isLessThanFive(this.extraData.timeview)) {
        return this.$str('timelessthanfive', 'engage_article');
      } else if (TimeViewType.isFiveToTen(this.extraData.timeview)) {
        return this.$str('timefivetoten', 'engage_article');
      } else if (TimeViewType.isMoreThanTen(this.extraData.timeview)) {
        return this.$str('timemorethanten', 'engage_article');
      }
      return null;
    },
  },

  created() {
    this.$_setStatIcons();
  },

  methods: {
    $_setStatIcons() {
      if (AccessManager.isPrivate(this.access)) {
        return;
      }

      const restrictedStatIcons = [
        {
          type: 'reaction',
          title: this.$str(
            'numberoflikes',
            'totara_engage',
            this.totalReactions
          ),
          icon: LikeIcon,
          statNumber: this.totalReactions,
        },
        {
          type: 'comment',
          title: this.$str(
            'numberofcomments',
            'totara_engage',
            this.totalComments
          ),
          icon: CommentIcon,
          statNumber: this.totalComments,
        },
      ];

      if (AccessManager.isRestricted(this.access)) {
        this.statIcons = restrictedStatIcons;
        return;
      }

      if (AccessManager.isPublic(this.access)) {
        this.statIcons = restrictedStatIcons.concat([
          {
            type: 'share',
            title: this.$str(
              'numberofshares',
              'totara_engage',
              this.sharedbycount
            ),
            icon: ShareIcon,
            statNumber: this.sharedbycount,
          },
          {
            type: 'playlistUsage',
            title: this.$str(
              'numberwithinplaylist',
              'engage_article',
              this.extraData.usage
            ),
            icon: AddToListIcon,
            statNumber: this.extraData.usage,
          },
        ]);
        return;
      }
    },
  },
};
</script>

<style lang="scss">
.tui-engageArticleCard {
  &__cardWrapper {
    flex-grow: 1;

    &:hover {
      .tui-engageArticleCard__drag {
        display: block;
      }
    }
  }

  &__drag {
    position: absolute;
    top: var(--gap-2);
    left: var(--gap-2);
    display: none;
  }

  &__content {
    display: flex;
    flex-flow: row wrap;
    gap: gap(1);
  }

  &__subtitle {
    @include font(body-sm);
    flex: 1;
  }

  &__subtitleRight {
    display: flex;
    gap: gap(2);
    align-items: center;
  }

  &__time {
    @include font(body-sm);
    display: flex;
    align-items: center;

    &-text {
      margin-left: var(--gap-1);
    }
  }

  &__footer {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: rem-px(32);
  }

  &__statusIcons {
    display: flex;
    gap: var(--gap-2);
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
