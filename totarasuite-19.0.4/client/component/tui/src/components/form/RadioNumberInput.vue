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
  @module totara_core
-->

<template>
  <div class="tui-radioNumberInput">
    <div class="tui-radioNumberInput__number">
      <InputNumber
        :id="$id('numberValue')"
        v-model:value="numberValue"
        :aria-label="$str('number', 'totara_core')"
        :disabled="disabled"
        :name="name + '[value]'"
        @input="update"
      />
    </div>
  </div>
</template>

<script>
// Components
import InputNumber from 'tui/components/form/InputNumber';

export default {
  components: {
    InputNumber,
  },

  props: {
    disabled: Boolean,
    name: String,
    value: [Number, String],
  },

  emits: ['accessible-change', 'input', 'update:value'],

  data() {
    return {
      numberValue: 1,
    };
  },

  computed: {
    selectedValue() {
      return this.numberValue;
    },
  },

  watch: {
    value: {
      handler() {
        this.numberValue = this.value !== undefined ? this.value : 1;
        this.update();
      },
    },
  },

  mounted() {
    // If no value emit the default
    if (this.value === undefined) {
      this.numberValue = 1;
      this.update();
    } else {
      this.numberValue = this.value;
    }
  },

  methods: {
    /**
     * Update the selected number
     *
     */
    update() {
      this.$emit('accessible-change', this.numberValue);
      this.$emit('update:value', this.selectedValue);
      this.$emit('input', this.selectedValue);
    },
  },
};
</script>

<style lang="scss">
.tui-radioNumberInput {
  display: flex;
  // Use same indentation as radio for nicer wrapping
  margin-left: var(--radio-label-offset);

  &__number {
    display: flex;
    width: rem-px(60);
  }
}
</style>
