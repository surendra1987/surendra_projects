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

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module tui
-->

<template>
  <Popover
    class="tui-filterBarAreaPopover"
    position="bottom-left"
    size="lg"
    :has-content-padding="false"
    :triggers="['click']"
  >
    <template v-slot:trigger>
      <ButtonIcon
        :aria-label="
          $str('a11y_additional_filters', 'totara_core', activeFiltersCount)
        "
        variant="stealth"
        :text="
          activeFiltersCount
            ? $str('filters_count', 'totara_core', activeFiltersCount)
            : $str('filters', 'totara_core')
        "
        :title="false"
      >
        <SliderIcon />
      </ButtonIcon>
    </template>

    <div class="tui-filterBarAreaPopover__content">
      <slot name="content" />
    </div>

    <template v-slot:custom-buttons>
      <div class="tui-filterBarAreaPopover__buttons">
        <div class="tui-filterBarAreaPopover__buttons-reset">
          <Button
            :aria-label="$str('a11y_reset_all_filters', 'totara_core')"
            :styleclass="{ transparent: true }"
            :text="$str('reset_all', 'totara_core')"
            @click="$emit('reset')"
          />
        </div>
      </div>
    </template>
  </Popover>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import Popover from 'tui/components/popover/Popover';
import SliderIcon from 'tui/components/icons/Slider';

export default {
  components: {
    Button,
    ButtonIcon,
    Popover,
    SliderIcon,
  },

  props: {
    // The number of active filters
    activeFiltersCount: Number,
  },

  emits: ['reset'],
};
</script>

<lang-strings>
{
  "totara_core": [
    "a11y_additional_filters",
    "a11y_reset_all_filters",
    "filters",
    "filters_count",
    "reset_all"
  ]
}
</lang-strings>

<style lang="scss">
.tui-filterBarAreaPopover {
  white-space: nowrap;

  &__content {
    display: flex;
    flex-direction: column;
    gap: var(--gap-4);
    padding: 0 var(--gap-1) var(--gap-1) var(--gap-1);
  }

  &__buttons {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    padding-top: var(--gap-4);
    padding-bottom: var(--gap-1);

    &::before {
      position: absolute;
      left: 0;
      width: 100%;
      border-top: var(--border-width-thin) solid var(--filter-bar-border-color);
      content: '';
    }

    &-reset {
      display: flex;
      flex-grow: 1;
      padding: var(--gap-5) var(--gap-1) 0;
    }
  }
}
</style>
