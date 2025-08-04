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
  @module auth_ssosaml
-->

<template>
  <div v-focus-within class="tui-auth_ssosaml-listingCard">
    <a v-if="href" class="tui-auth_ssosaml-listingCard__title" :href="href">
      <div>{{ title }}</div>
      <slot name="title-extra" />
    </a>
    <ButtonAria v-else>
      <div class="tui-auth_ssosaml-listingCard__title" @click="$emit('click')">
        <div>{{ title }}</div>
        <slot name="title-extra" />
      </div>
    </ButtonAria>

    <div class="tui-auth_ssosaml-listingCard__side">
      <slot name="side" />
    </div>

    <slot name="modals" />
  </div>
</template>

<script>
import ButtonAria from 'tui/components/buttons/ButtonAria';

export default {
  components: {
    ButtonAria,
  },

  props: {
    title: String,
    href: String,
  },

  emits: ['click'],
};
</script>

<style lang="scss">
.tui-auth_ssosaml-listingCard {
  display: flex;
  align-items: center;
  border: 1px solid var(--card-border-color);
  border-radius: var(--card-border-radius);

  &:hover,
  &.tui-focusWithin {
    box-shadow: var(--shadow-2);
  }

  &__title {
    @include tui-stack-horizontal(var(--gap-2));
    display: flex;
    flex-grow: 1;
    align-items: center;
    padding: var(--gap-5) var(--gap-4);
    font-weight: bold;
    a#{&} {
      text-decoration: none;
    }
  }

  &__side {
    display: flex;
    align-items: center;
    padding-right: var(--gap-4);
  }
}
</style>
