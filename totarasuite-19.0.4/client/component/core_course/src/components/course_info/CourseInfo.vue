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

  @author Brian Barnes <brian.barnes@totara.com>
  @module core_course
-->
<template>
  <div class="tui-core_course-courseInfo">
    <img class="tui-core_course-courseInfo__image" :src="image" alt="" />
    <div>
      <div v-html="summary" />
      <ul
        v-if="summaryFiles.length > 0 || summaryImages.length > 0"
        class="tui-core_course-courseInfo__summaryFiles"
      >
        <li v-for="{ url, alt } in summaryImages" :key="url">
          <img
            :src="url"
            :alt="alt"
            class="tui-core_course-courseInfo__summaryFilesImage"
          />
        </li>
        <li v-for="{ icon, url, name } in summaryFiles" :key="url">
          <a :href="url">
            <span v-html="icon" />
            {{ name }}
          </a>
        </li>
      </ul>
      <ul
        v-if="contacts.length > 0"
        class="tui-core_course-courseInfo__contacts"
      >
        <li v-for="{ id, name, role } in contacts" :key="id">
          {{ role }}:
          <a :href="$url('/user/profile.php?', { id, course: siteId })">
            {{ name }}
          </a>
        </li>
      </ul>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    image: {
      type: String,
      required: true,
    },
    summary: {
      type: String,
      default: 'Summary',
    },
    summaryImages: Array,
    summaryFiles: Array,
    contacts: Array,
  },

  data() {
    return {
      siteId: 1, // to match SITEID
    };
  },
};
</script>
<style lang="scss">
.tui-core_course-courseInfo {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--gap-4);
  margin-bottom: var(--gap-5);
  @media (min-width: $tui-screen-xs) {
    grid-template-columns: auto 1fr;
  }

  &__image {
    width: rem-px(240);
    height: fit-content;
    aspect-ratio: 16 / 9;
    object-fit: cover;
    border-radius: var(--border-radius-normal);
  }

  &__summaryFiles,
  &__contacts {
    margin: var(--gap-2) 0 0;
    list-style-type: none;

    > li + li {
      margin-top: var(--gap-2);
    }
  }

  &__summaryFilesImage {
    max-width: 100px;
    max-height: 100px;
  }
}
</style>
