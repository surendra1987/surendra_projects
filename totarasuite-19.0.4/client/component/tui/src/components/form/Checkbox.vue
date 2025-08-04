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
    class="tui-checkbox"
    :class="[{ 'tui-checkbox--large': large }, $props.class]"
  >
    <input
      :id="id"
      class="tui-checkbox__input"
      type="checkbox"
      v-bind="$attrs"
      :aria-label="ariaLabel"
      :aria-labelledby="ariaLabelledby"
      :checked="checked"
      @change="handleChange"
      @blur="$emit('blur', $event)"
    />
    <label
      class="tui-checkbox__label"
      :class="{
        'tui-checkbox__label--noOffset': noLabelOffset,
        'tui-checkbox__label--disabledReadable': disabledReadable,
      }"
      :for="id"
      :aria-hidden="ariaLabel || ariaLabelledby ? 'true' : null"
    >
      <slot />
    </label>
  </div>
</template>

<script>
import { uniqueId } from 'tui/util';

export default {
  inheritAttrs: false,

  props: {
    ariaLabel: String,
    ariaLabelledby: String,
    class: [String, Array, Object],
    checked: Boolean,
    // Disabled label is still fully readable
    disabledReadable: Boolean,
    id: {
      type: String,
      default: () => 'uid-' + uniqueId(),
    },
    large: Boolean,
    noLabelOffset: Boolean,
  },

  emits: ['blur', 'change', 'update:checked'],

  methods: {
    handleChange(e) {
      this.$emit('update:checked', e.target.checked);
      this.$emit('change', e.target.checked);
    },
  },
};
</script>

<style lang="scss">
:root {
  // Size of checkbox
  --form-checkbox-size: var(--form-input-font-size);
  --form-checkbox-size-large: calc(var(--form-input-font-size) * 1.333);
  --checkbox-check-width: #{rem-px(2)};
}

.tui-checkbox {
  position: relative;
  display: flex;
  min-height: calc(var(--form-checkbox-size) + 2px);

  &--large {
    min-height: calc(var(--form-checkbox-size-large) + 2px);
  }

  &__input {
    position: absolute;
    opacity: 0;
  }

  &__label {
    position: relative;
    margin: 0;
    padding-left: calc(var(--form-checkbox-size) * 1.5);
    font-weight: normal;
    font-size: var(--form-input-font-size);
    line-height: 1;
    overflow-wrap: break-word;

    .tui-checkbox--large & {
      padding-left: calc(var(--form-checkbox-size-large) * 1.5);
    }

    &--noOffset {
      padding-left: var(--form-checkbox-size);
      .tui-checkbox--large & {
        padding-left: var(--form-checkbox-size-large);
      }
    }

    &::before {
      position: absolute;
      top: 0;
      left: 0;
      display: block;
      width: var(--form-checkbox-size);
      height: var(--form-checkbox-size);
      margin-top: 1px;
      background: var(--form-checkbox-bg-color);
      border: var(--form-input-border-size) solid
        var(--form-checkbox-border-color);
      border-radius: var(--form-input-border-radius);
      transition: border var(--transition-form-function)
          var(--transition-form-duration),
        box-shadow var(--transition-form-function)
          var(--transition-form-duration);
      content: '';
      pointer-events: none;

      .tui-checkbox--large & {
        width: var(--form-checkbox-size-large);
        height: var(--form-checkbox-size-large);
      }

      .tui-contextInvalid & {
        border-color: var(--form-input-border-color-invalid);
        box-shadow: var(--shadow-none), var(--form-input-shadow-invalid);
      }
    }
  }

  &__input:disabled ~ &__label {
    color: var(--form-input-text-color-disabled);
  }

  &__input:disabled ~ &__label--disabledReadable {
    color: var(--form-input-text-color);
  }

  &__input:focus-visible ~ &__label::before {
    @include tui-focus();
    border: var(--form-input-border-size) solid
      var(--form-checkbox-border-color-focus);
    box-shadow: var(--form-input-shadow-focus);

    .tui-contextInvalid & {
      border-color: var(--form-input-border-color-invalid);
      outline-color: var(--form-input-border-color-invalid);
      box-shadow: var(--shadow-none), var(--form-input-shadow-invalid-focus);
    }
  }

  &__input:disabled:checked ~ &__label::before,
  &__input:disabled ~ &__label::before {
    background: var(--form-checkbox-bg-color-disabled);
    border: var(--form-input-border-size) solid;
    border-color: var(--form-checkbox-border-color-disabled);
    box-shadow: none;
  }

  &__input ~ &__label::after {
    // construct a check mark out of two sides of a rotated box
    position: absolute;
    top: calc(0.35 * var(--form-checkbox-size));
    left: calc(0.21 * var(--form-checkbox-size));
    display: block;
    width: calc(0.6 * var(--form-checkbox-size));
    height: calc(0.35 * var(--form-checkbox-size));
    border-color: var(--form-checkbox-check-color);
    border-style: solid;
    /*!rtl:ignore*/
    border-width: 0 0 var(--checkbox-check-width) var(--checkbox-check-width);
    transform: rotate(-45deg);
    opacity: 0;
    transition: opacity var(--transition-form-function)
      var(--transition-form-duration);
    content: '';
    pointer-events: none;

    .tui-checkbox--large & {
      top: calc(0.3 * var(--form-checkbox-size-large));
      left: calc(0.22 * var(--form-checkbox-size-large));
      width: calc(0.6 * var(--form-checkbox-size-large));
      height: calc(0.32 * var(--form-checkbox-size-large));
    }
  }

  &__input:disabled:checked ~ &__label::after,
  &__input:disabled ~ &__label::after {
    border-color: var(--form-checkbox-check-color-disabled);
  }

  &__input:checked ~ &__label::before {
    background: var(--form-checkbox-bg-color-active);
    border-color: var(--form-checkbox-border-color-active);
  }

  &__input:checked ~ &__label::after {
    opacity: 1;
  }
}

@media print {
  .tui-checkbox {
    &__input:checked ~ &__label::before {
      print-color-adjust: exact;
    }
  }

  // IE11 & Edge support
  .ie,
  .msedge {
    .tui-checkbox {
      &__input:checked ~ .tui-checkbox__label::before {
        border: solid var(--form-checkbox-bg-color-active);
        border-width: 0 var(--form-checkbox-size) 0 0;
      }
    }
  }
}
</style>
