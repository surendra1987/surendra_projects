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

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module tui
-->

<template>
  <fieldset class="tui-filterFieldset">
    <Label :hidden="hidden" :legend="true" :label="legend" />
    <div class="tui-filterFieldset__content">
      <slot />
    </div>
  </fieldset>
</template>

<script>
import Label from 'tui/components/form/Label';

export default {
  components: {
    Label,
  },

  props: {
    hidden: Boolean,
    legend: { type: String, required: true },
  },
};
</script>

<style lang="scss">
.tui-filterFieldset {
  min-width: 0;
  margin: 0;
  padding: 0;
  border: 0;

  &__content {
    margin-top: var(--gap-2);
  }
}
</style>
