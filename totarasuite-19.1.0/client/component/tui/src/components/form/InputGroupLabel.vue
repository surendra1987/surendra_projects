<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
  @module tui
-->

<template>
  <div class="tui-inputGroupLabel">
    {{ text }}
  </div>
</template>

<script>
export default {
  props: {
    text: String,
  },
};
</script>

<style lang="scss">
.tui-inputGroupLabel {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  padding: 0 0 0 var(--gap-2);
  color: var(--color-text-hint);
  font-size: var(--form-input-font-size);
  cursor: pointer;
  user-select: none;
}
</style>
