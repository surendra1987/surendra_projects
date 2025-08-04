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
  <div class="tui-checkboxButton">
    <input
      :id="id"
      class="tui-checkboxButton__input"
      type="checkbox"
      :checked="checked"
      :disabled="disabled"
      :value="value"
      @change="handleChange"
    />
    <label class="tui-checkboxButton__label" :for="id">
      <slot />
      <CloseIcon custom-class="tui-checkboxButton__deselectIcon" />
    </label>
  </div>
</template>

<script>
import { uniqueId } from 'tui/util';
import CloseIcon from 'tui/components/icons/Close';

export default {
  components: {
    CloseIcon,
  },

  props: {
    checked: Boolean,
    disabled: Boolean,
    id: {
      type: String,
      default: () => 'uid-' + uniqueId(),
    },
    value: String,
  },

  emits: ['change', 'update:checked'],

  methods: {
    handleChange(e) {
      this.$emit('update:checked', e.target.checked);
      this.$emit('change', e.target.checked);
    },
  },
};
</script>

<style lang="scss">
.tui-checkboxButton {
  $block: #{&};
  position: relative;
  display: flex;

  &__input {
    position: absolute;
    opacity: 0;
  }

  &__label {
    display: flex;
    flex-grow: 1;
    margin: 0;
    padding: var(--gap-1);
    color: var(--btn-checkbox-text-color);
    font-weight: normal;
    cursor: pointer;

    &:hover {
      color: var(--btn-checkbox-text-color-focus);
      background: var(--btn-checkbox-bg-color-hover);
    }

    &:active:hover,
    &:active {
      color: var(--btn-checkbox-text-color-active);
    }
  }

  &__deselectIcon {
    margin: auto 0 auto auto;
    color: var(--btn-checkbox-text-color-selected);
    visibility: hidden;
  }

  &__input:checked ~ &__label {
    color: var(--btn-checkbox-text-color-selected);
    background: var(--btn-checkbox-bg-color-selected);

    #{$block}__deselectIcon {
      visibility: visible;
    }

    &:hover {
      color: var(--btn-checkbox-text-color-focus);
      background: var(--btn-checkbox-bg-color-hover);

      #{$block}__deselectIcon {
        color: var(--btn-checkbox-text-color-focus);
        visibility: visible;
      }
    }

    &:active:hover,
    &:active {
      color: var(--btn-checkbox-text-color-active);
      background: var(--btn-checkbox-bg-color-hover);

      #{$block}__deselectIcon {
        color: var(--btn-checkbox-text-color-active);
        visibility: visible;
      }
    }
  }

  &__input:focus-visible ~ &__label {
    @include tui-focus();
  }

  &__input:focus-visible:checked ~ &__label {
    @include tui-focus();

    #{$block}__deselectIcon {
      color: var(--btn-checkbox-text-color-selected);
      visibility: visible;
    }
  }
}
</style>
