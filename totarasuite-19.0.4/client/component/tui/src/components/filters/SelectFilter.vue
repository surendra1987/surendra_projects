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
  <div
    class="tui-selectFilter"
    :class="[
      {
        'tui-selectFilter--stacked': stacked,
        'tui-selectFilter--barFilter': barFilter,
      },
      $props.class,
    ]"
  >
    <Label
      v-if="!dropLabel"
      :for-id="generatedId"
      :hidden="!showLabel"
      :label="label"
    />
    <Select
      v-bind="$attrs"
      :id="generatedId"
      :aria-label="ariaLabel"
      :value="value"
      @update:value="input"
    />
  </div>
</template>

<script>
// Components
import Label from 'tui/components/form/Label';
import Select from 'tui/components/form/Select';

export default {
  components: {
    Label,
    Select,
  },

  inheritAttrs: false,

  /* eslint-disable vue/require-prop-types */
  props: {
    barFilter: Boolean,
    class: {},
    dropLabel: {
      required: false,
      type: Boolean,
    },
    id: {},
    label: {
      required: true,
      type: String,
    },
    showLabel: Boolean,
    stacked: {},
    value: {},
  },

  emits: ['input', 'update:value'],

  computed: {
    generatedId() {
      return this.id || this.$id();
    },
    ariaLabel() {
      if (this.dropLabel) return this.label;
      return null;
    },
  },

  methods: {
    input(e) {
      this.$emit('update:value', e);
      this.$emit('input', e);
    },
  },
};
</script>

<style lang="scss">
.tui-selectFilter {
  position: relative;
  display: flex;
  flex-direction: row;
  align-items: center;

  .tui-formLabel {
    margin: auto var(--gap-3) auto 0;
  }

  .tui-select {
    width: auto;
    max-width: 250px;
  }

  &--stacked {
    flex-direction: column;
    align-items: stretch;

    .tui-formLabel {
      margin: var(--gap-1) 0 0;
    }

    .tui-select {
      max-width: initial;
      margin-top: var(--gap-1);
    }
  }

  &--barFilter {
    flex-direction: column;
    gap: var(--gap-1);
    align-items: stretch;
    max-width: 250px;
    margin-top: auto;
  }

  &--barFilter&--stacked {
    max-width: initial;
  }
}
</style>
