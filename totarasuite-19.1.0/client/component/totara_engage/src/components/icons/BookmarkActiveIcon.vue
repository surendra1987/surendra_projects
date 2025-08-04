<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Navjeet Singh <navjeet.singh@totara.com>
  @module totara_engage
-->

<template>
  <BookmarkActive
    class="tui-engageIconBookmarkActive"
    :alt="alt"
    :size="size"
    :title="title"
  />
</template>

<script>
import BookmarkActive from 'tui/components/icons/BookmarkActive';

export default {
  components: {
    BookmarkActive,
  },

  props: {
    alt: String,
    size: [String, Number],
    title: String,
  },
};
</script>

<style lang="scss">
.tui-engageIconBookmarkActive {
  color: var(--color-state);
  &:hover {
    color: var(--color-state-hover);
  }
}
</style>
