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
  @module totara_engage
-->

<template>
  <AccessPrivate
    :class="[
      'tui-engageIconPrivate',
      coloring && 'tui-engageIconPrivate--color',
    ]"
    :alt="alt"
    :size="size"
    :title="title"
  />
</template>

<script>
import AccessPrivate from 'tui/components/icons/AccessPrivate';

export default {
  components: {
    AccessPrivate,
  },

  props: {
    alt: String,
    size: [String, Number],
    title: String,
    coloring: {
      type: Boolean,
      default: true,
    },
  },
};
</script>

<style lang="scss">
.tui-engageIconPrivate {
  &--color {
    color: var(--color-prompt-alert);
  }
}
</style>
