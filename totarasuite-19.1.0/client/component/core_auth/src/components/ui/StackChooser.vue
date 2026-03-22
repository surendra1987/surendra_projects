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
  @module core_auth
-->

<template>
  <div class="tui-core_auth-stackChooser">
    <component
      :is="option.url ? 'passthrough' : 'ButtonAria'"
      v-for="option in options"
      :key="option.id"
    >
      <component
        :is="option.url ? 'a' : 'div'"
        v-bind="option.url ? { href: option.url } : {}"
        class="tui-core_auth-stackChooser__option"
        @click="$emit('select', option)"
      >
        <div class="tui-core_auth-stackChooser__label">
          {{ option.name }}
        </div>
        <Loading
          v-if="loadingOption === option.id"
          class="tui-core_auth-stackChooser__icon"
          state="dimmed"
        />
        <ForwardArrow
          v-else
          class="tui-core_auth-stackChooser__icon"
          state="success"
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
    options: { required: true, type: Array },
    loadingOption: String,
  },

  emits: ['select'],
};
</script>

<style lang="scss">
.tui-core_auth-stackChooser {
  display: flex;
  flex-direction: column;
  gap: var(--gap-2);

  &__option {
    @include font(h4);
    display: flex;
    gap: var(--gap-4);
    align-items: center;
    justify-content: space-between;
    padding: rem-px(10);
    color: unset;
    background: var(--color-neutral-1);
    border: var(--border-width-thin) solid var(--card-border-color);
    border-radius: var(--card-border-radius);
    user-select: none;

    &:hover,
    &:active,
    &:focus {
      color: unset;
      text-decoration: none;
    }

    &:focus {
      @include tui-focus();
    }
  }

  &__label {
    display: flex;
    flex-direction: column;
    gap: var(--gap-2);
  }
}
</style>
