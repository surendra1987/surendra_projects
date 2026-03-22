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
  <textarea
    class="tui-formTextarea"
    :class="[
      charLength ? 'tui-formTextarea--charLength-' + charLength : null,
      charLength ? 'tui-input--customSize' : null,
    ]"
    @input="handleInput"
  />
</template>

<script>
import { charLengthProp } from './form_common';

export default {
  props: {
    charLength: charLengthProp,
  },

  emits: ['input', 'update:value'],

  methods: {
    handleInput(e) {
      this.$emit('update:value', e.target.value);
      this.$emit('input', e.target.value);
    },
  },
};
</script>

<style lang="scss">
.tui-formTextarea {
  display: block;
  flex-grow: 1;
  width: 100%;
  min-width: 0;
  max-width: 100%;
  max-height: 100%;
  padding: tui-input-v-padding() var(--gap-3);
  overflow: auto;
  color: var(--form-input-text-color);
  font-size: var(--form-input-font-size);
  font-family: inherit;
  line-height: var(--form-input-line-height);
  border: var(--form-input-border-size) solid var(--form-input-border-color);
  border-radius: var(--form-input-border-radius);
  resize: none;

  @include tui-char-length-classes();

  &::placeholder {
    color: var(--form-input-text-placeholder-color);
  }

  .tui-contextInvalid & {
    border-color: var(--form-input-border-color-invalid);
    box-shadow: var(--form-input-shadow-invalid);
  }

  &:focus {
    background: var(--form-input-bg-color-focus);
    border: var(--form-input-border-size) solid
      var(--form-input-border-color-focus);
    box-shadow: var(--form-input-shadow-focus);
    @include tui-focus();

    .tui-contextInvalid & {
      background: var(--form-input-bg-color-invalid-focus);
      border-color: var(--form-input-border-color-invalid);
      outline-color: var(--form-input-border-color-invalid);
      box-shadow: var(--form-input-shadow-invalid-focus);
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
}
</style>
