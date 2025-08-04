<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

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
  <div class="tui-sparkleLoader">
    <div class="tui-sparkleLoader__content">
      <Stars class="tui-sparkleLoader__icon" :size="null" animated />
      <h3 v-if="title" class="tui-sparkleLoader__title">{{ title }}</h3>
      <p v-if="subtitle" class="tui-sparkleLoader__subtitle">{{ subtitle }}</p>
    </div>
  </div>
</template>

<script>
import Stars from 'tui/components/icons/Stars';

export default {
  components: {
    Stars,
  },

  props: {
    title: {
      type: String,
      required: false,
    },
    subtitle: {
      type: String,
      required: false,
    },
  },
};
</script>

<style lang="scss">
.tui-sparkleLoader {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
  align-items: center;
  justify-content: center;
  padding: var(--gap-8);
  text-align: center;

  &__content {
    display: flex;
    flex-direction: column;
    gap: var(--gap-4);
    align-items: center;
  }

  &__icon {
    font-size: rem-px(40);
  }

  &__title {
    @include tui-font-heading-small();
    margin: 0;
  }

  &__subtitle {
    @include tui-font-body();
    margin: 0;
  }
}
</style>
