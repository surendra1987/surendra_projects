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

  @author Simon Chester <simon.chester@totara.com>
  @module core_mfa
-->

<template>
  <div class="tui-core_mfa-factorChooser">
    <component
      :is="href ? 'passthrough' : 'ButtonAria'"
      v-for="factor in factors"
      :key="factor.id"
    >
      <component
        :is="href ? 'a' : 'div'"
        v-bind="href ? { href: href(factor) } : {}"
        class="tui-core_mfa-factorChooser__option"
        @click="$emit('select', factor)"
      >
        <div class="tui-core_mfa-factorChooser__label">
          <div class="tui-core_mfa-factorChooser__heading">
            {{ factor.name }}
          </div>
          <div class="tui-core_mfa-factorChooser__description">
            {{ factor.description }}
          </div>
        </div>
        <Loading
          v-if="loadingFactor === factor.id"
          class="tui-core_mfa-factorChooser__icon"
          state="dimmed"
          size="300"
        />
        <ForwardArrow
          v-else
          class="tui-core_mfa-factorChooser__icon"
          state="success"
          size="300"
        />
      </component>
    </component>
  </div>
</template>

<script>
import ButtonAria from 'tui/components/buttons/ButtonAria';
import ForwardArrow from 'tui/components/icons/ForwardArrow';
import Loading from 'tui/components/icons/Loading';

export default {
  components: {
    ButtonAria,
    ForwardArrow,
    Loading,
  },

  props: {
    factors: { required: true, type: Array },
    loadingFactor: String,
    href: Function,
  },

  emits: ['select'],
};
</script>

<style lang="scss">
.tui-core_mfa-factorChooser {
  $border-width: var(--border-width-thin);
  display: flex;
  flex-direction: column;
  padding-bottom: $border-width;
  isolation: isolate; // create a new stacking context for our z-index on focus

  &__option {
    display: flex;
    gap: var(--gap-4);
    align-items: center;
    justify-content: space-between;
    margin-top: calc(#{$border-width} * -1);
    padding: var(--gap-4);
    color: unset;
    background: var(--color-neutral-1);
    border: $border-width solid var(--card-border-color);
    user-select: none;

    &:first-child {
      margin-top: 0;
      border-top-left-radius: var(--card-border-radius);
      border-top-right-radius: var(--card-border-radius);
    }

    &:last-child {
      border-bottom-right-radius: var(--card-border-radius);
      border-bottom-left-radius: var(--card-border-radius);
    }

    &:hover,
    &:active,
    &:focus {
      color: unset;
      text-decoration: none;
    }

    &:focus {
      @include tui-focus();
      z-index: 1;
    }
  }

  &__label {
    display: flex;
    flex-direction: column;
    gap: var(--gap-2);
  }

  &__heading {
    @include font(h4);
  }

  &__description {
    @include font(body-sm);
    color: var(--color-text-hint);
  }
}
</style>
