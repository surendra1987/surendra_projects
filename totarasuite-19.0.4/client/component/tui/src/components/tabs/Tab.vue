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
  <div
    v-if="alwaysRender || (active && $slots.default)"
    v-show="!alwaysRender || (active && $slots.default)"
    :id="htmlId + '-tabpanel'"
    class="tui-tabContent"
    role="tabpanel"
    :aria-labelledby="htmlId"
  >
    <slot />
  </div>
</template>

<script>
export default {
  props: {
    id: {
      type: [String, Number],
      required: true,
    },
    disabled: Boolean,
    active: Boolean,
    hidden: Boolean,
    name: {
      type: String,
      required: true,
    },
    alwaysRender: Boolean,
    // Text to append to name in dropdown mode, where label-extra slot is not rendered
    labelExtraText: String,
    htmlId: String,
  },
};
</script>
