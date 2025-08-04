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
  <PerformAdminCustomElementEdit
    :initial-values="initialValues"
    :settings="settings"
    @cancel="$emit('display')"
    @update="$emit('update', $event)"
  />
</template>

<script>
import PerformAdminCustomElementEdit from 'mod_perform/components/element/PerformAdminCustomElementEdit';

export default {
  components: {
    PerformAdminCustomElementEdit,
  },

  inheritAttrs: false,

  props: {
    identifier: String,
    isRequired: Boolean,
    rawTitle: String,
    settings: Object,
  },

  emits: ['display', 'update'],

  data() {
    return {
      initialValues: {
        rawTitle: this.rawTitle,
        identifier: this.identifier,
        responseRequired: this.isRequired,
      },
    };
  },
};
</script>
