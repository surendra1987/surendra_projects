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
  <div :class="['tui-searchBox', $props.class]">
    <Label
      v-if="!dropLabel"
      :for-id="generatedId"
      :hidden="!labelVisible"
      :label="ariaLabel || placeholder"
    />
    <div
      class="tui-searchBox__inputWrapper"
      :class="[
        charLength
          ? 'tui-searchBox__inputWrapper--charLength-' + charLength
          : null,
        charLength ? 'tui-input--customSize' : null,
      ]"
    >
      <InputSearch
        :id="generatedId"
        ref="search"
        v-bind="$attrs"
        :aria-label="ariaLabel || placeholder"
        char-length="full"
        :disabled="disabled"
        :placeholder="placeholder || $str('search', 'core')"
        :styleclass="{ postIcon: true }"
        class="tui-searchBox__search"
        @input="input"
        @submit="submit"
      />

      <ButtonIcon
        v-if="isClearIconVisible"
        class="tui-searchBox__clearContainer"
        :aria-label="$str('clear_search_term', 'totara_core')"
        :disabled="disabled"
        :styleclass="{ small: true, transparent: true }"
        @click="clear"
      >
        <RemoveIcon class="tui-searchBox__removeIcon" />
      </ButtonIcon>
    </div>
    <ButtonIcon
      :aria-label="ariaLabel || placeholder"
      :disabled="disabled"
      :styleclass="{ small: true }"
      class="tui-searchBox__button"
      @click="submit"
    >
      <SearchIcon />
    </ButtonIcon>
  </div>
</template>

<script>
// Components
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import InputSearch from 'tui/components/form/InputSearch';
import Label from 'tui/components/form/Label';
import SearchIcon from 'tui/components/icons/Search';
import RemoveIcon from 'tui/components/icons/Remove';

export default {
  components: {
    ButtonIcon,
    InputSearch,
    Label,
    SearchIcon,
    RemoveIcon,
  },

  inheritAttrs: false,

  /* eslint-disable vue/require-prop-types */
  props: [
    'ariaLabel',
    'charLength',
    'class',
    'disabled',
    'dropLabel',
    'id',
    'labelVisible',
    'placeholder',
    'value',
  ],

  emits: ['submit', 'input', 'clear', 'update:value'],

  computed: {
    generatedId() {
      return this.id || this.$id();
    },

    isClearIconVisible() {
      return this.value && this.value.length > 0;
    },
  },

  methods: {
    input(value) {
      this.$emit('update:value', value);
      this.$emit('input', value);
    },

    clear() {
      this.$refs.search.$el.focus();
      this.$emit('clear');
      this.$emit('update:value', '');
      this.$emit('input', '');
    },

    submit() {
      this.$emit('submit');
    },
  },
};
</script>

<style lang="scss">
.tui-searchBox {
  position: relative;
  display: flex;
  border-radius: var(--form-input-border-radius);
  isolation: isolate; // contain z indexes

  &__inputWrapper {
    position: relative;
    display: flex;
    flex-grow: 1;
    @include tui-char-length-classes();
  }

  &__search[type='search'] {
    border-radius: 0;
    border-top-left-radius: var(--form-input-border-radius);
    border-bottom-left-radius: var(--form-input-border-radius);

    &:focus {
      z-index: 1;
    }
    // disable the default clear (x) action in IE
    &::-ms-clear {
      display: none;
    }
  }

  .tui-formLabel {
    margin-right: var(--gap-2);
  }

  &__clearContainer {
    position: absolute;
    right: 0;
    height: 100%;
  }

  &__removeIcon {
    color: var(--filter-search-clear-icon-color);
  }

  // So that the search button matches the format of the input that is next to it
  &__button {
    margin-left: calc(var(--form-input-border-size) * -1);
    border-radius: 0;
    border-top-right-radius: var(--form-input-border-radius);
    border-bottom-right-radius: var(--form-input-border-radius);

    &:focus {
      z-index: 1;
    }
  }
}
</style>
