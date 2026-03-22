<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Cody Finegan <cody.finegan@totara.com>
  @module engage_course
-->

<template>
  <BaseCard
    component-name="engage_course"
    :bookmarked="innerBookmarked"
    :show-bookmark="showBookmark"
    :extra="extraData"
    :instance-id="instanceId"
    :footnotes="footnotes"
    :show-footnotes="showFootnotes"
  >
    <template v-slot="{ actionList }">
      <LearningCard
        class="tui-engageCourseCard__cardWrapper"
        :title="name"
        :href="url"
        :image="extraData.image"
        :actions="actionList"
      >
        <template v-slot:hero="{ popFront }">
          <DragHandleIcon
            v-if="itemDraggable"
            :class="['tui-engageCourseCard__drag', popFront]"
          />
        </template>
        <template v-slot:body>
          <div class="tui-engageCourseCard__subtitle">
            {{ $str('card_label', 'engage_course') }}
          </div>
        </template>
      </LearningCard>
    </template>
  </BaseCard>
</template>

<script>
import BaseCard from 'totara_engage/components/card/BaseCard';
import { cardMixin } from 'totara_engage/index';
import DragHandleIcon from 'tui/components/icons/DragHandle';
import LearningCard from 'tui/components/card/LearningCard';

export default {
  components: {
    BaseCard,
    DragHandleIcon,
    LearningCard,
  },

  mixins: [cardMixin],

  data() {
    return {
      extraData: JSON.parse(this.extra),
      // Assign the value to the inner child, as we do not want to mutate the prop.
      innerBookmarked: this.bookmarked,
    };
  },
};
</script>

<style lang="scss">
.tui-engageCourseCard {
  &__cardWrapper {
    flex-grow: 1;

    &:hover {
      .tui-engageCourseCard__drag {
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

  &__subtitle {
    @include font(body-sm);
  }
}
</style>
