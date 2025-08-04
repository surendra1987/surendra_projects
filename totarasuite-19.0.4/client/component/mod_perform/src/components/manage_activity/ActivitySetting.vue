<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2022 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @module mod_perform
-->

<template>
  <div class="tui-performManageActivitySetting">
    <!-- heading bar -->
    <div class="tui-performManageActivitySetting__heading">
      <h3 class="tui-performManageActivitySetting__heading-header">
        {{ title }}
      </h3>

      <div class="tui-performManageActivitySetting__heading-side">
        <slot name="heading-side" />
      </div>
    </div>

    <!-- form description -->
    <template v-if="$slots['description']">
      <div class="tui-performManageActivitySetting__description">
        <slot name="description" />
      </div>
    </template>

    <!-- form elements -->
    <template v-if="$slots['form']">
      <div
        class="tui-performManageActivitySetting__form"
        :class="{
          'tui-performManageActivitySetting__form--noMargin': noFormMargin,
        }"
      >
        <slot name="form" />
      </div>
    </template>

    <!-- Modal content -->
    <template v-if="$slots['modal']">
      <div class="tui-performManageActivitySetting__modal">
        <slot name="modal" />
      </div>
    </template>
  </div>
</template>

<script>
export default {
  components: {},

  props: {
    noFormMargin: {
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
.tui-performManageActivitySetting {
  padding: var(--gap-8);
  background: var(--color-neutral-3);

  & > * + * {
    margin-top: var(--gap-4);
  }

  &__heading {
    display: flex;
    align-items: center;
    padding-bottom: var(--gap-2);
    border-bottom: var(--border-width-thin) solid var(--color-neutral-5);

    &-header {
      @include font(h4);
      margin: 0;
    }

    &-side {
      margin-left: auto;
      padding-left: var(--gap-2);
    }
  }

  &__description {
    max-width: 800px;
  }

  &__form {
    margin-top: var(--gap-8);

    &--noMargin {
      margin-top: 0;
    }
  }

  &__modal {
    margin-top: 0;
  }
}
</style>
