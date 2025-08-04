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

  @author Simon Chester <simon.chester@totaralearning.com>
  @module tui
-->

<template>
  <Button v-bind="$props" @click="$emit('click')">
    <slot />
  </Button>
</template>

<script>
import Button from 'tui/components/buttons/Button';

export default {
  components: {
    Button,
  },

  props: {
    styleclass: {
      type: Object,
    },
    ariaLabel: String,
    disabled: Boolean,
    href: {
      required: true,
      type: String,
    },
    text: {
      required: true,
      type: String,
    },
  },

  emits: ['click'],
};
</script>
