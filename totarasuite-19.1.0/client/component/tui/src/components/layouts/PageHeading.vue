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

  @author Kevin Hottinger <kevin.hottinger@totaralearning.com>
  @module tui
-->

<template>
  <div class="tui-pageHeading">
    <h1
      v-if="title"
      class="tui-pageHeading__title"
      :class="{
        'tui-pageHeading__title--small': small,
        'tui-pageHeading__title--smallOnMob': smallOnMobile,
      }"
    >
      {{ title }}
    </h1>

    <template v-if="$slots.buttons">
      <div class="tui-pageHeading__buttons">
        <slot name="buttons" />
      </div>
    </template>
  </div>
</template>

<script>
export default {
  props: {
    // Smaller title
    small: {
      type: Boolean,
    },
    // Smaller title at sm mobile breakpoint
    smallOnMobile: {
      type: Boolean,
    },
    title: {
      required: true,
      type: String,
    },
  },
};
</script>

<style lang="scss">
.tui-pageHeading {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  gap: var(--gap-3);
  align-items: flex-start;

  &__title {
    @include font(h1);
    flex-grow: 1;
    min-width: 0;
    margin: 0;
    overflow-wrap: break-word;

    &--small {
      @include font(h2);
    }

    &--smallOnMob {
      @include font(h2);
    }
  }

  &__buttons {
    display: flex;
    flex-shrink: 0;
    flex-wrap: wrap;
    gap: var(--gap-3);
    max-width: 100%;
  }
}

@media (min-width: $tui-screen-sm) {
  .tui-pageHeading {
    &__buttons {
      & > * {
        margin-top: 0;
      }
    }
    &__title {
      &--smallOnMob {
        @include font(h1);
      }
    }
  }
}

@media (min-width: $tui-screen-md) {
  .tui-pageHeading {
    flex-wrap: nowrap;
  }
}
</style>
