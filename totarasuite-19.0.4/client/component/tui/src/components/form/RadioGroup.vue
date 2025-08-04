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
    class="tui-radioGroup"
    :class="{
      'tui-radioGroup--horizontal': horizontal,
      'tui-radioGroup--inputSizedOptions': inputSizedOptions,
      'tui-radioGroup--noPaddingTop': noPaddingTop,
    }"
    role="radiogroup"
    :aria-labelledby="ariaLabelledby"
  >
    <PropsProvider :provide="provide">
      <slot />
    </PropsProvider>
  </div>
</template>

<script>
import { uniqueId } from 'tui/util';
import PropsProvider from 'tui/components/util/PropsProvider';

export default {
  components: {
    PropsProvider,
  },

  props: {
    ariaLabelledby: String,
    disabled: Boolean,
    horizontal: Boolean,
    inputSizedOptions: Boolean,
    name: {
      type: String,
      default: () => 'uid-' + uniqueId(),
    },
    noPaddingTop: Boolean,
    required: Boolean,
    value: [Array, Boolean, Number, Object, String],
  },

  emits: ['blur', 'input', 'update:value'],

  methods: {
    provide({ props }) {
      return {
        props: {
          groupName: this.name,
          name: this.name,
          checked: props.value == this.value ? true : null,
          disabled: this.disabled ? true : null,
          required: this.required ? true : null,
        },
        listeners: {
          select: this.$_handleSelect,
          blur: () => this.$emit('blur'),
        },
      };
    },

    $_handleSelect(value) {
      this.$emit('update:value', value);
      this.$emit('input', value);
    },
  },
};
</script>

<style lang="scss">
.tui-radioGroup {
  display: flex;
  flex-direction: column;
  gap: var(--gap-2) var(--gap-4);
  padding: tui-input-v-padding-borderless() 0;

  &--inputSizedOptions {
    & > * {
      align-items: center;
      min-height: var(--form-input-height);
    }
  }

  &--noPaddingTop {
    padding-top: 0;
  }
}

@media screen and (min-width: $tui-screen-sm) {
  .tui-radioGroup--horizontal {
    flex-direction: row;
    flex-wrap: wrap;
  }
}
</style>
