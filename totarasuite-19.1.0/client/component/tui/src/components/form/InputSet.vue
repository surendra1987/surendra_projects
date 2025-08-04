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
    class="tui-inputSet"
    :role="labelId ? 'group' : null"
    :class="[
      'tui-inputSet--' + direction,
      charLength ? 'tui-inputSet--charLength-' + charLength : null,
      charLength ? 'tui-input--customSize' : null,
      split ? 'tui-inputSet--split' : null,
      split && stackBelow ? 'tui-inputSet--stackBelow-' + stackBelow : null,
    ]"
    :aria-labelledby="labelId"
  >
    <slot />
  </div>
</template>

<script>
import { charLengthProp, isValidCharLength } from './form_common';

export default {
  inject: {
    reformFieldContext: { default: null },
  },

  provide() {
    return {
      // prevent field context from being passed down
      reformFieldContext: null,
    };
  },

  props: {
    ariaLabelledby: String,
    vertical: Boolean,
    split: Boolean,
    charLength: charLengthProp,
    stackBelow: {
      type: [String, Number],
      validator: isValidCharLength,
    },
  },

  computed: {
    direction() {
      return this.vertical ? 'v' : 'h';
    },

    labelId() {
      return (
        this.ariaLabelledby ||
        (this.reformFieldContext && this.reformFieldContext.getLabelId())
      );
    },
  },
};
</script>

<style lang="scss">
@mixin tui-input-set-stack-below($name, $size) {
  &--stackBelow-#{$name} > *,
  // need to specify .tui-formInput here too for specificity reasons
  &--stackBelow-#{$name} > .tui-formInput {
    // This triggers the children to switch to being vertically stacked below a
    // certain width.
    // It works like this:
    // Above the specified width, (width - 100%) evaluates to a large
    // negative flex basis, and is therefore ignored.
    // Below the specified width, (width - 100%) evaluates to a large
    // positve flex basis, and forces each item to take up its own line.
    // Magic!
    // prettier-ignore
    flex-basis: calc((#{tui-char-length($size)} - 100%) * 999);
  }
}
.tui-inputSet {
  display: flex;
  flex: 1;
  gap: var(--input-set-spacing);

  @include tui-char-length-classes();

  &--v {
    flex-direction: column;
  }

  &--h {
    flex-direction: row;
    flex-wrap: wrap;
  }

  & > .tui-formLabel {
    padding: 0;
  }

  & > {
    // replaced input elements have their width set to 100% normally as
    // `width: auto` doesn't fill the container like it does on divs
    #{$tui-input-replaced-selectors} {
      width: auto;
    }
  }

  &--split {
    & > * {
      flex-basis: 0;
      flex-grow: 1;
      width: auto;
    }
  }

  @each $size in $tui-char-length-scale {
    @include tui-input-set-stack-below($size, $size);
  }
}
</style>
