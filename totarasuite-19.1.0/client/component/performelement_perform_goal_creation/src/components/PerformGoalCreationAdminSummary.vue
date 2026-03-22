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

  @author Matthias Bonk <matthias.bonk@totara.com>
  @module performelement_perform_goal_creation
-->

<template>
  <PerformAdminCustomElementSummary
    :identifier="identifier"
    :is-required="isRequired"
    :settings="settings"
    :title="title"
    @display="$emit('display')"
  />
</template>

<script>
import PerformAdminCustomElementSummary from 'mod_perform/components/element/PerformAdminCustomElementSummary';

export default {
  components: {
    PerformAdminCustomElementSummary,
  },

  inheritAttrs: false,

  props: {
    identifier: String,
    isRequired: Boolean,
    settings: Object,
    title: String,
  },

  emits: ['display'],
};
</script>
