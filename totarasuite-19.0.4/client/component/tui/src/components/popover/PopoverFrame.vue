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
    class="tui-popoverFrame"
    :class="[
      side ? 'tui-popoverFrame--' + side : null,
      size ? 'tui-popoverFrame--size-' + size : null,
      slim ? 'tui-popoverFrame--slim' : null,
    ]"
  >
    <Arrow :relative-side="arrowSide" :distance="arrowDistance" />
    <CloseButton
      v-if="closeable || srCloseable"
      class="tui-popoverFrame__close"
      :class="{ 'sr-only': !closeable }"
      :tabindex="!closeable ? -1 : 0"
      @click="$emit('close')"
    />
    <div v-if="title" class="tui-popoverFrame__title">
      {{ title }}
    </div>
    <div
      role="tooltip"
      :class="{
        'tui-popoverFrame__content': true,
        'tui-popoverFrame__content--nonClosable': !closeable,
        'tui-popoverFrame__content--slim': slim,
        'tui-popoverFrame__content--noPadding': !hasContentPadding,
      }"
    >
      <slot />
    </div>
    <div v-if="$slots.buttons" class="tui-popoverFrame__buttons">
      <slot name="buttons" />
    </div>
    <slot v-else-if="$slots['custom-buttons']" name="custom-buttons" />
  </div>
</template>

<script>
import Arrow from 'tui/components/decor/Arrow';
import CloseButton from 'tui/components/buttons/CloseIcon';
import { langSide } from 'tui/i18n';

export default {
  components: {
    Arrow,
    CloseButton,
  },

  props: {
    title: String,
    side: String,
    size: {
      type: String,
      validator(value) {
        const allowedOptions = ['sm', 'md', 'lg'];
        return allowedOptions.indexOf(value) !== -1;
      },
    },
    slim: Boolean,
    srCloseable: Boolean,
    arrowDistance: Number,
    closeable: {
      type: Boolean,
      default: true,
    },
    // Setting this to false will remove the extra padding around the content
    hasContentPadding: {
      type: Boolean,
      default: true,
    },
  },

  emits: ['close'],

  computed: {
    arrowSide() {
      return langSide(this.side);
    },
  },
};
</script>

<style lang="scss">
.tui-popoverFrame {
  @include font(body);
  position: relative;
  max-width: 300px;
  // margin must be equal on all 4 sides, and must not change with position
  margin: 10px;
  padding: var(--gap-4);
  background: var(--color-background);
  background-clip: padding-box;
  box-shadow: var(--shadow-3);

  &::before {
    position: absolute;
    top: 0;
    left: 0;
    z-index: -1;
    width: 100%;
    height: 100%;
    box-shadow: 0 0 0 1px var(--color-neutral-5);
    content: '';
  }

  &--size-sm {
    width: 250px;
    max-width: none;
  }

  &--size-md {
    width: 300px;
    max-width: none;
  }

  &--size-lg {
    width: 95vw;
    max-width: none;

    @media (min-width: 600px) {
      width: 560px;
    }
  }

  &--slim {
    padding: var(--gap-2);
  }

  &__close {
    position: absolute;
    top: 0;
    right: 0;
    display: flex;
    padding: calc(#{rem-px(14)} - 0.1em) calc(#{rem-px(14)} - 0.3em);
  }

  &__title {
    @include font(h4);
    padding-top: var(--gap-2);
    padding-right: var(--gap-4);
  }

  &__content {
    max-height: 80vh;
    margin-top: var(--gap-4);
    padding: 0 var(--gap-4) var(--gap-2) 0;
    overflow: auto;

    & img {
      max-width: 100%;
    }

    &--slim,
    &--noPadding {
      padding: 0;
    }
  }

  &__title + &__content,
  &__content--nonClosable {
    padding-right: 0;
  }

  &__buttons {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    padding-top: var(--gap-1);
    padding-bottom: var(--gap-3);

    > * {
      margin-top: var(--gap-2);
    }

    > * + * {
      margin-left: var(--gap-4);
    }
  }
}
</style>
