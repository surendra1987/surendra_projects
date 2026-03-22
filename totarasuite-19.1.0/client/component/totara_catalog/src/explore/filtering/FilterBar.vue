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

  @author Simon Chester <simon.chester@totara.com>
  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module totara_catalog
-->

<script setup>
import { computed, ref } from 'vue';
import { useResizeObserver } from 'tui/dom/composables';
import Button from 'tui/components/buttons/Button';
import Filter from 'totara_catalog/explore/filtering/Filter';
import FilterBarExplore from 'totara_catalog/explore/filtering/filter_bar/FilterBarExplore';
import FilterBarButton from 'totara_catalog/explore/ui/FilterBarButton';
import Popover from 'tui/components/popover/Popover';
import TreeFilter from 'totara_catalog/explore/filtering/TreeFilter';
import { getTabbableElements } from 'tui/dom/focus';

const props = defineProps({
  filters: Array,
  browseFilter: Object,
  value: Object,
});

const emit = defineEmits(['update:value', 'resetAllFilters']);

const smallWidth = 759;

const root = ref(null);
const browseTree = ref(null);
const small = ref(false);
const searchFilterRef = ref(null);

useResizeObserver(root, size => {
  small.value = size.width < smallWidth;
});

// The filters to show in the filter bar popover
const compFilters = computed(() => {
  if (small.value) {
    // browse filter moves to popover on small screens
    return props.browseFilter
      ? [props.browseFilter, ...props.filters]
      : props.filters;
  }
  return props.filters;
});

const activeFiltersCount = computed(() => {
  let count = 0;

  for (const [key, value] of Object.entries(props.value)) {
    if (key === 'search' || (!small.value && props.browseFilter?.key === key)) {
      continue;
    }

    // Some filters allow multiselect so we need to count how many are selected
    count += Array.isArray(value) ? value.length : 1;
  }
  return count;
});

/**
 * Whether to show the filter bar popover button
 */
const showFilters = computed(() => {
  return compFilters.value !== null && compFilters.value.length > 0;
});

/**
 * The text to show on the browse filter button
 * Either the currently selected filter, or the name of the filter type
 */
const selectedBrowseFilterLabel = computed(
  () =>
    props.browseFilter?.options.find(
      option => option.id === props.value[props.browseFilter.key]
    )?.label ?? props.browseFilter?.title
);

function handleBrowseOpenChanged(open) {
  if (open) {
    setTimeout(() => {
      if (browseTree.value) {
        getTabbableElements(browseTree.value)[0]?.focus();
      }
    }, 0);
  }
}

function searchFilterFocus() {
  searchFilterRef.value?.searchFilterFocus();
}

defineExpose({ searchFilterFocus });
</script>

<template>
  <div ref="root">
    <FilterBarExplore
      ref="searchFilterRef"
      :value="props.value || {}"
      class="tui-totara_catalog-filterBar"
      :small="small"
      :accessibility-title="$str('filters', 'totara_catalog')"
      :active-filters-count="activeFiltersCount"
      :show-filters="showFilters"
      @reset-all-filters="emit('resetAllFilters')"
      @update:value="emit('update:value', $event)"
    >
      <template
        v-if="browseFilter && !small"
        v-slot:browse-filter="{ update, filters }"
      >
        <Popover
          :closeable="false"
          :slim="true"
          :triggers="['click']"
          :has-content-padding="false"
          position="bottom-right"
          size="md"
          @open-changed="handleBrowseOpenChanged"
        >
          <template v-slot:trigger>
            <FilterBarButton caret>
              <span class="tui-totara_catalog-filterBar__browseText">
                {{ selectedBrowseFilterLabel }}
              </span>
            </FilterBarButton>
          </template>

          <template v-slot:default="{ close }">
            <div ref="browseTree" @keydown.esc="close">
              <TreeFilter
                :title="browseFilter.title"
                :options="browseFilter.options"
                :value="filters[browseFilter.key]"
                :hide-legend="true"
                @update:value="update(browseFilter.key, $event)"
              />
            </div>
          </template>
          <template v-slot:custom-buttons>
            <div
              v-if="filters[browseFilter.key]"
              class="tui-totara_catalog-filterBar__browseButtons"
            >
              <Button
                class="tui-totara_catalog-filterBar__browseButtons-reset"
                :text="$str('reset', 'totara_core')"
                variant="link"
                @click="update(browseFilter.key, null)"
              />
            </div>
          </template>
        </Popover>
      </template>

      <template v-slot:filters="{ filters, update, stacked }">
        <template v-for="filter in compFilters" :key="filter.key">
          <Filter
            :type="filter.type"
            :title="filter.title"
            :stacked="stacked"
            :options="filter.options"
            :value="filters[filter.key]"
            @update:value="update(filter.key, $event)"
          />
        </template>
      </template>
    </FilterBarExplore>
  </div>
</template>

<style lang="scss">
.tui-totara_catalog-filterBar {
  &__browseButtons {
    display: flex;
    padding: calc(var(--gap-base) * 1);

    &::before {
      position: absolute;
      left: 0;
      width: 100%;
      border-top: var(--border-width-thin) solid var(--filter-bar-border-color);
      content: '';
    }

    &-reset {
      margin: var(--gap-4) 0 var(--gap-1) var(--gap-1);
    }
  }
}
</style>
