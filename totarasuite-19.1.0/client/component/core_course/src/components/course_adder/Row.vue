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

  @author Brian Barnes <brian.barnes@totara.com>
  @module core_course
-->

<template>
  <div class="tui-core_course-adderRow">
    <div class="tui-coreCourse-adderRow__image" :style="imageStyle" />
    <div class="tui-coreCourse-adderRow__name">
      {{ fullName }}
    </div>
    <div class="tui-coreCourse-adderRow__category">
      {{ categoryString }}
    </div>
  </div>
</template>
<script>
export default {
  props: {
    imageUrl: {
      required: true,
      type: String,
    },

    fullName: {
      required: true,
      type: String,
    },

    categoryString: {
      required: true,
      type: String,
    },
  },

  computed: {
    imageStyle() {
      return { backgroundImage: 'url(' + this.$url(this.imageUrl) + ')' };
    },
  },
};
</script>
<style lang="scss">
.tui-coreCourseAdderRow {
  &__image {
    float: left;
    width: rem-px(60);
    height: rem-px(40);
    margin-right: var(--gap-2);
    background-position: 50%;
    background-size: cover;
  }

  &__name {
    height: var(--font-body-line-height);
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }

  &__category {
    @include font(body-xs);
    height: 1.1em;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }
}
</style>
