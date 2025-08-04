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

  @author Anthony van Beek <anthony@pixelfusion.co.nz>
  @module tui
-->
<template>
  <div class="tui-gradientOutline">
    <div class="tui-gradientOutline__content">
      <slot />
    </div>
  </div>
</template>

<style lang="scss">
.tui-gradientOutline {
  padding: var(--border-width-thin);
  background: linear-gradient(89.7deg, #912ffb 0%, #fa21dc 50%, #ff9081 100%);
  border-radius: var(--card-border-radius);

  &__content {
    display: block;
    padding: var(--gap-4);
    color: var(--color-text);
    background: var(--color-background)
      linear-gradient(90deg, #f4fafd 0%, #fff7fe 100%);
    border-radius: calc(var(--card-border-radius) - var(--border-width-thin));
  }
}
</style>
