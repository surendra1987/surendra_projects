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
  <input
    :id="id"
    class="tui-formInput"
    :class="[
      styleclass.preIcon ? 'tui-formInput--preIcon' : null,
      styleclass.postIcon ? 'tui-formInput--postIcon' : null,
      styleclass.transparent ? 'tui-formInput--transparent' : null,
      size ? 'tui-formInput--size-' + size : null,
      charLength ? 'tui-formInput--charLength-' + charLength : null,
      charLength ? 'tui-input--customSize' : null,
    ]"
    @input="handleInput"
    @blur="$emit('blur')"
    @keydown.enter="$emit('submit', $event.target.value)"
  />
</template>

<script>
import { charLengthProp } from './form_common';

export default {
  props: {
    autofocus: Boolean,
    id: String,
    charLength: charLengthProp,
    size: {
      type: String,
      validator: val => val == null || val === 'large',
    },
    styleclass: {
      default: () => ({
        preIcon: false,
        postIcon: false,
        transparent: false,
      }),
      type: Object,
    },
  },

  emits: ['input', 'blur', 'submit', 'update:value'],

  mounted() {
    if (this.autofocus && this.$el) {
      // Make the input element to be focused, when the prop autofocus is set.
      // We are moving away from the native attribute for element, because
      // different browser will treat autofocus different. Furthermore,
      // the slow performing browser will not make the element focused due
      // to the element is not rendered on time.
      this.$el.focus();
    }
  },

  methods: {
    handleInput(e) {
      this.$emit('update:value', e.target.value);
      this.$emit('input', e.target.value);
    },
  },
};
</script>

<style lang="scss">
.tui-formInput {
  display: block;
  flex-grow: 1;
  box-sizing: border-box;
  width: 100%;
  min-width: 0;
  height: var(--form-input-height);
  padding: tui-input-v-padding() var(--gap-3);
  color: var(--form-input-text-color);
  font-size: var(--form-input-font-size);
  line-height: var(--form-input-line-height);
  background: var(--form-input-bg-color);
  border: var(--form-input-border-size) solid var(--form-input-border-color);
  border-radius: var(--form-input-border-radius);

  &[type='search'] {
    border-radius: var(--form-input-border-radius);
    appearance: none;
  }

  @include tui-char-length-classes();

  &::placeholder {
    color: var(--form-input-text-placeholder-color);
  }

  .tui-contextInvalid & {
    border-color: var(--form-input-border-color-invalid);
    box-shadow: var(--form-input-shadow-invalid);
  }

  &--size-large {
    height: var(--form-input-height-large);
    /* prettier-ignore */
    padding: calc((var(--form-input-height-large) - 1em - (var(--form-input-border-size) * 2)) / 2);
    font-size: var(--form-input-font-size-lg);
  }

  &:focus {
    background: var(--form-input-bg-color-focus);
    border: var(--form-input-border-size) solid
      var(--form-input-border-color-focus);
    outline: none;
    box-shadow: var(--form-input-shadow-focus);
    @include tui-focus();

    .tui-contextInvalid & {
      background: var(--form-input-bg-color-invalid-focus);
      border-color: var(--form-input-border-color-invalid);
      outline-color: var(--form-input-border-color-invalid);
      box-shadow: var(--form-input-shadow-invalid-focus);
    }
  }

  &--preIcon {
    padding-left: var(--gap-8);
  }

  &--postIcon {
    padding-right: var(--gap-8);
  }

  &[readonly] {
    color: var(--form-input-text-color);
    background: var(--form-input-bg-color);
    border-color: var(--form-input-border-color);

    &::placeholder {
      color: var(--form-input-text-placeholder-color);
    }
  }

  &[disabled] {
    color: var(--form-input-text-color-disabled);
    background: var(--form-input-bg-color-disabled);
    border-color: var(--form-input-border-color-disabled);

    &::placeholder {
      color: var(--form-input-text-color-disabled);
    }
  }

  &--transparent,
  &--transparent:focus,
  &--transparent:focus-visible,
  &--transparent[readonly],
  &--transparent[disabled],
  .tui-contextInvalid &--transparent,
  .tui-contextInvalid &--transparent:focus {
    background-color: transparent;
    border: none;
    border-radius: 0;
    outline: none;
    box-shadow: none;
  }
}
</style>
