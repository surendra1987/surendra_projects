<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Arshad Anwer <arshad.anwer@totara.com>
  @module core_auth
-->

<template>
  <Dropdown position="bottom-right" :separator="false">
    <template v-slot:trigger="{ toggle, isOpen }">
      <ButtonAria>
        <div
          class="tui-core_auth-languageSelect__button"
          :aria-expanded="isOpen.toString()"
          :aria-label="$str('language_options', 'totara_core')"
          @click="toggle"
        >
          <Language />
        </div>
      </ButtonAria>
    </template>
    <template v-for="(label, code) in langs" :key="code">
      <DropdownItem
        :selected="value === code"
        @click="() => handleItemSelect(code)"
      >
        {{ label }}
      </DropdownItem>
    </template>
  </Dropdown>
</template>

<script>
import ButtonAria from 'tui/components/buttons/ButtonAria';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import Language from 'tui/components/icons/Language';

export default {
  components: {
    ButtonAria,
    Dropdown,
    DropdownItem,
    Language,
  },

  props: {
    value: String,
    /**
     * Mapping of lang codes to labels
     * @type {{ [key: string]: string }}
     */
    langs: {
      type: Object,
      required: true,
    },
  },

  emits: ['input', 'update:value'],

  methods: {
    handleItemSelect(code) {
      this.$emit('update:value', code);
      this.$emit('input', code);
    },
  },
};
</script>

<style lang="scss">
.tui-core_auth-languageSelect {
  &__button {
    display: flex;
    align-items: center;
    padding: var(--gap-2);
    border-radius: var(--btn-radius);
    cursor: pointer;
    user-select: none;

    &:focus {
      @include tui-focus();
      outline-color: currentColor;
    }
  }
}
</style>
