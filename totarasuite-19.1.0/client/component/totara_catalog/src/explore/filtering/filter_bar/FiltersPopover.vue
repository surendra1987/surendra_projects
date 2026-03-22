<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module totara_catalog
-->

<script setup>
import Button from 'tui/components/buttons/Button';
import Popover from 'tui/components/popover/Popover';
import SliderIcon from 'tui/components/icons/Slider';
import FilterBarButton from 'totara_catalog/explore/ui/FilterBarButton';
import { getString } from 'tui/i18n';
import { computed } from 'vue';

const props = defineProps({
  activeFiltersCount: Number,
  showText: Boolean,
});

const emit = defineEmits(['reset']);

const buttonText = computed(() => {
  const count = props.activeFiltersCount;
  if (props.showText) {
    return count
      ? getString('filters_count', 'totara_core', count)
      : getString('filters', 'totara_core');
  } else {
    return count ? String(count) : '';
  }
});
</script>

<template>
  <Popover
    class="tui-totara_catalog-filterBarAreaPopover"
    :has-content-padding="false"
    :triggers="['click']"
    position="bottom-left"
    size="lg"
  >
    <template v-slot:trigger>
      <FilterBarButton
        :aria-label="
          $str('a11y_additional_filters', 'totara_core', activeFiltersCount)
        "
      >
        <template v-slot:icon>
          <SliderIcon />
        </template>

        <template v-if="buttonText" v-slot:default>
          {{ buttonText }}
        </template>
      </FilterBarButton>
    </template>

    <div class="tui-totara_catalog-filterBarAreaPopover__content">
      <slot name="content" />
    </div>

    <template v-slot:custom-buttons>
      <div class="tui-totara_catalog-filterBarAreaPopover__buttons">
        <div class="tui-totara_catalog-filterBarAreaPopover__buttons-reset">
          <Button
            :aria-label="$str('a11y_reset_all_filters', 'totara_core')"
            :styleclass="{ transparent: true }"
            :text="$str('reset_all', 'totara_core')"
            @click="emit('reset')"
          />
        </div>
      </div>
    </template>
  </Popover>
</template>

<style lang="scss">
.tui-totara_catalog-filterBarAreaPopover {
  white-space: nowrap;

  &__content {
    display: flex;
    flex-direction: column;
    gap: var(--gap-6);
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
