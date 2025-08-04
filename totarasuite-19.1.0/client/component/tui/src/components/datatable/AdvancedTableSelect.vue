<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module tui
-->

<template>
  <div class="tui-advancedTableSelect">
    <Dropdown v-if="!loadingPreview">
      <template v-slot:trigger="{ toggle, isOpen }">
        <div
          class="tui-advancedTableSelect__dropdownTrigger"
          tabindex="0"
          :aria-label="$str('selectrows', 'totara_core')"
          :class="{
            'tui-advancedTableSelect__dropdownTrigger--isOpen': isOpen,
            'tui-advancedTableSelect__dropdownTrigger--large': largeCheckBox,
          }"
          @keydown="handleKeydown($event, toggle)"
        >
          <Checkbox
            class="tui-advancedTableSelect__dropdownTrigger-checkbox"
            :aria-label="$str('selectallrows', 'totara_core')"
            :large="largeCheckBox"
            :tabindex="-1"
            :checked="checkboxChecked"
            @change="$emit('checkbox-change', $event)"
          />
          <ButtonIcon
            class="tui-advancedTableSelect__dropdownTrigger-button"
            :class="{
              'tui-advancedTableSelect__dropdownTrigger-button--stacked': isStacked,
            }"
            tabindex="-1"
            :aria-label="$str('selectrows', 'totara_core')"
            :styleclass="{
              transparentNoPadding: true,
            }"
            :aria-expanded="isOpen.toString()"
            @click="toggle"
          >
            <ShowIcon
              class="tui-advancedTableSelect__dropdownTrigger-button-icon"
              :class="{
                'tui-advancedTableSelect__dropdownTrigger-button-icon--stacked': isStacked,
              }"
            />
          </ButtonIcon>
        </div>
      </template>

      <!-- 
        For this component to be fully accessible it must always be provided at least a "Select all" and "Deselect all" option
        This is because selecting the inner checkbox via the keyboard is impractical as it's nested inside the dropdown trigger
        Without access to the checkbox via the keyboard, we need the equivalent options in the dropdown which is keyboard accessible
       -->
      <slot name="advanced-select-options">
        <DropdownButton @click="$emit('select-all-visible')">
          {{ $str('all', 'totara_core') }}
        </DropdownButton>
        <DropdownButton @click="$emit('deselect-all-visible')">
          {{ $str('none', 'totara_core') }}
        </DropdownButton>
      </slot>
    </Dropdown>
    <div
      v-else
      class="tui-advancedTableSelect__loader"
      :class="{
        'tui-advancedTableSelect__loader--large': largeCheckBox,
      }"
    >
      <SkeletonContent :has-overlay="loadingOverlayActive" />
    </div>
  </div>
</template>

<script>
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import Checkbox from 'tui/components/form/Checkbox';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownButton from 'tui/components/dropdown/DropdownButton';
import ShowIcon from 'tui/components/icons/Show';
import SkeletonContent from 'tui/components/loading/SkeletonContent';

export default {
  components: {
    ButtonIcon,
    DropdownButton,
    Checkbox,
    Dropdown,
    ShowIcon,
    SkeletonContent,
  },

  props: {
    checkboxChecked: Boolean,
    largeCheckBox: Boolean,
    loadingPreview: Boolean,
    loadingOverlayActive: Boolean,
    isStacked: Boolean,
  },

  emits: ['checkbox-change', 'select-all-visible', 'deselect-all-visible'],

  methods: {
    handleKeydown(e, toggle) {
      if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
        e.preventDefault();
        toggle();
      }
    },
  },
};
</script>

<style lang="scss">
:root {
  --advanced-select-dropdown-trigger-width: 44px;
  --advanced-select-dropdown-trigger-width-large: 52px;

  --advanced-select-dropdown-trigger-height: 28px;
  --advanced-select-dropdown-trigger-height-large: 34px;

  --advanced-select-border-radius: var(--border-radius-small);

  --advanced-select-outline-width: var(--border-width-normal);

  --advanced-select-caret-top-offset: 1px;
  --advanced-select-caret-left-offset: calc(var(--gap-1) * -1);
  --advanced-select-caret-color: var(--color-neutral-7);

  --advanced-select-caret-icon-margin-left-stacked: 25px;
  --advanced-select-caret-left-stacked: -30px;

  --advanced-select-checkbox-padding-top: 6px;
  --advanced-select-checkbox-padding-bottom: 6px;
  --advanced-select-checkbox-padding-left: 5px;
}

.tui-advancedTableSelect {
  &__dropdownTrigger {
    display: flex;
    width: var(--advanced-select-dropdown-trigger-width);
    height: var(--advanced-select-dropdown-trigger-height);
    margin-right: var(--gap-1);

    &--large {
      width: var(--advanced-select-dropdown-trigger-width-large);
      height: var(--advanced-select-dropdown-trigger-height-large);
    }

    &-checkbox {
      padding: var(--advanced-select-checkbox-padding-top) 0
        var(--advanced-select-checkbox-padding-bottom)
        var(--advanced-select-checkbox-padding-left);
    }

    &-button {
      top: var(--advanced-select-caret-top-offset);
      left: var(--advanced-select-caret-left-offset);
      outline: none;

      &:focus,
      &:hover,
      &:active,
      &:active:hover,
      &:active:focus {
        outline: none;
      }

      &-icon {
        color: var(--advanced-select-caret-color);
        &--stacked {
          margin-left: var(--advanced-select-caret-icon-margin-left-stacked);
        }
      }

      &--stacked {
        left: var(--advanced-select-caret-left-stacked);
      }
    }

    // Grey background
    &:focus,
    &:active,
    &:hover,
    &--isOpen {
      background: var(--color-neutral-4);
      border-radius: var(--advanced-select-border-radius);
    }

    // The green focus outline
    &:focus,
    &:active {
      outline: var(--advanced-select-outline-width) solid
        var(--color-state-focus);
      outline-offset: var(--advanced-select-outline-width);
    }
  }

  &__loader {
    width: var(--form-checkbox-size);
    height: var(--form-checkbox-size);
    margin-right: var(--gap-5);
    margin-left: var(--gap-1);

    &--large {
      width: var(--form-checkbox-size-large);
      height: var(--form-checkbox-size-large);
    }
  }
}
</style>
