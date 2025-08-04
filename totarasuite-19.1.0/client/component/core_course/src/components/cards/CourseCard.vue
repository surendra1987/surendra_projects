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

  @author Brian Barnes <brian.barnes@totara.com>
  @module core_course
-->
<template>
  <Card :clickable="hasAction">
    <div class="tui-core_course-courseCard">
      <div
        class="tui-core_course-courseCard__image"
        :style="{ backgroundImage: `url(` + image + `)` }"
      />
      <div class="tui-core_course-courseCard__content">
        <div
          class="tui-core_course-courseCard__title"
          :class="{
            'tui-core_course-courseCard__title--limitHeight': limitHeight,
          }"
          :title="limitHeight ? title : ''"
        >
          {{ title }}
        </div>
        <slot />
      </div>
    </div>
  </Card>
</template>
<script>
import Card from 'tui/components/card/Card';

export default {
  components: {
    Card,
  },

  props: {
    title: String,
    image: String,
    hasAction: Boolean,
    limitHeight: Boolean,
  },
};
</script>
<style lang="scss">
.tui-core_course-courseCard {
  display: flex;
  flex-direction: column;
  width: 100%;

  &__content {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    padding: var(--gap-4);
    padding-top: 0;

    > * {
      margin-top: var(--gap-2);
    }
  }

  &__title {
    @include font(h4);

    &--limitHeight {
      display: -webkit-box;
      height: rem-px(63);
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 3;
      overflow: hidden;
    }
  }

  &__image {
    width: 100%;
    height: rem-px(120);
    background-position: 50%;
    background-size: cover;
    border-top-left-radius: var(--card-border-radius);
    border-top-right-radius: var(--card-border-radius);
  }
}
</style>
