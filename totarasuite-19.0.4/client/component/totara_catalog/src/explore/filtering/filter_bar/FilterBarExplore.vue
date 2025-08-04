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
import { produce } from 'tui/immutable';
import FiltersPopover from 'totara_catalog/explore/filtering/filter_bar/FiltersPopover';
import SearchFilter from 'totara_catalog/explore/filtering/filter_bar/SearchFilter';

const props = defineProps({
  // Accessibility title describing what is being filtered
  accessibilityTitle: { type: String, required: true },

  small: Boolean,

  // The current values of the provided filters
  value: {
    type: Object,
    required: true,
  },

  activeFiltersCount: Number,
});

const emit = defineEmits(['update:value', 'resetAllFilters']);

/**
 * Reset all the filters
 *
 * If reset values are provided, reset to that. Otherwise set all the values to empty.
 */
function resetAllFilters() {
  emit('update:value', {});
  emit('resetAllFilters');
}

/**
 * Update the model bar values from the slot filters
 *
 * @param {String} filter name of the filter to be updated
 * @param {String} value value to set the filter to
 */
function update(filter, value) {
  const updated = produce(props.value, draft => {
    draft[filter] = value;
  });

  emit('update:value', updated);
}
</script>

<template>
  <div
    class="tui-totara_catalog-filterBarExplore"
    :class="{ 'tui-totara_catalog-filterBarExplore--small': small }"
  >
    <span class="sr-only">
      {{ accessibilityTitle }}
    </span>

    <div class="tui-totara_catalog-filterBarExplore__bar">
      <div class="tui-totara_catalog-filterBarExplore__search">
        <!-- Search -->
        <SearchFilter
          :label="$str('search_the_catalogue', 'totara_catalog')"
          :placeholder="$str('search_the_catalogue', 'totara_catalog')"
          :value="value.search"
          :small="small"
          @update:value="update('search', $event)"
        />
      </div>

      <div
        v-if="$slots['browse-filter']"
        class="tui-totara_catalog-filterBarExplore__section"
      >
        <div class="tui-totara_catalog-filterBarExplore__sectionInner">
          <slot name="browse-filter" :update="update" :filters="value" />
        </div>
      </div>

      <div
        class="tui-totara_catalog-filterBarExplore__section"
        :class="{
          'tui-totara_catalog-filterBarExplore__section--only': !$slots[
            'browse-filter'
          ],
        }"
      >
        <div class="tui-totara_catalog-filterBarExplore__sectionInner">
          <!-- Filters -->
          <FiltersPopover
            :active-filters-count="activeFiltersCount"
            :show-text="!small"
            @reset="resetAllFilters"
          >
            <template v-slot:content>
              <slot
                name="filters"
                :filters="value"
                :stacked="false"
                :update="update"
              />
            </template>
          </FiltersPopover>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.tui-totara_catalog-filterBarExplore {
  display: flex;
  flex-direction: column;

  &__bar {
    display: flex;
    flex-direction: row;
    height: rem-px(64);
    background-color: var(--color-neutral-3);
    border-radius: var(--border-radius-curved);
  }

  &:has(&__search input:focus) &__bar {
    @include tui-focus;
    background-color: var(--color-background);
    outline-offset: 0;
  }

  &--small &__bar {
    height: rem-px(52);
  }

  &__search {
    display: flex;
    flex: 1;
    gap: gap(4);
    padding-right: gap(2);
  }

  &__section {
    display: flex;
    flex: 0 0 auto;
    align-items: center;
    padding-block: gap(3);
  }

  &__sectionInner {
    display: flex;
    flex: 1;
    border-left: var(--border-width-thin) solid var(--color-neutral-4);
    padding-inline: gap(3);
  }

  &--small &__sectionInner {
    padding-inline: gap(2);
  }

  &__section--only &__sectionInner {
    border-left: none;
  }
}
</style>
