<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module totara_catalog
-->

<script setup>
import { computed, ref } from 'vue';
import { debounce } from 'tui/util';
import Button from 'tui/components/buttons/Button';
import ClearInputIcon from 'tui/components/icons/ClearInput';
import SearchIcon from 'tui/components/icons/Search';

const props = defineProps({
  id: [Number, String],
  label: {
    required: true,
    type: String,
  },
  value: String,
  small: Boolean,
});

const emit = defineEmits(['update:value', 'focus', 'blur']);

const search = ref(null);

const showClearButton = computed(() => props.value && props.value.length > 0);

const handleInput = debounce(e => {
  emit('update:value', e.target.value);
}, 500);

function clear() {
  search.value.focus();
  emit('update:value', '');
}
</script>

<template>
  <div
    class="tui-totara_catalog-searchFilter"
    :class="{ 'tui-totara_catalog-searchFilter--small': small }"
  >
    <div v-if="!small" class="tui-totara_catalog-searchFilter__icon">
      <SearchIcon />
    </div>
    <input
      ref="search"
      v-bind="$attrs"
      type="search"
      :aria-label="$attrs.ariaLabel || label"
      class="tui-totara_catalog-searchFilter__search"
      :value="value"
      @input="handleInput"
    />
    <div class="tui-totara_catalog-searchFilter__clearContainer">
      <Button
        v-if="showClearButton"
        :aria-label="$str('clear_search_term', 'totara_core')"
        variant="stealth"
        shape="circle"
        @click="clear"
      >
        <template v-slot:icon>
          <ClearInputIcon class="tui-totara_catalog-searchFilter__clearIcon" />
        </template>
      </Button>
    </div>
  </div>
</template>

<style lang="scss">
.tui-totara_catalog-searchFilter {
  position: relative;
  display: flex;
  flex-grow: 1;

  &__search {
    flex: 1;
    padding: 0 gap(10) 0 gap(14);
    font-size: var(--form-input-font-size);
    line-height: var(--form-input-line-height);
    background-color: transparent;
    border: none;

    &:focus {
      background-color: transparent;
      outline: none;
    }

    &::placeholder {
      color: #717171;
    }
  }

  &--small &__search {
    padding-left: gap(4);
  }

  &__icon {
    position: absolute;
    left: gap(6);
    display: flex;
    align-items: center;
    height: 100%;
    color: var(--filter-search-icon-color);
    pointer-events: none;
  }

  &__clearContainer {
    position: absolute;
    right: 0;
    height: 100%;
    @include flex-center;
  }

  &__clearIcon {
    color: var(--filter-search-clear-icon-color);
  }
}
</style>
