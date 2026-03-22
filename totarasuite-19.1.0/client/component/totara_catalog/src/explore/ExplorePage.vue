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
  @module totara_catalog
-->

<script setup>
import { ref, toRef, watch } from 'vue';
import LayoutOneColumn from 'tui/components/layouts/LayoutOneColumn';
import Button from 'tui/components/buttons/Button';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import Loader from 'tui/components/loading/Loader';
import HeaderButtons from 'totara_catalog/explore/ui/HeaderButtons';
import FilterBar from 'totara_catalog/explore/filtering/FilterBar';
import SortBar from 'totara_catalog/explore/filtering/SortBar';
import CatalogGrid from 'totara_catalog/explore/items/CatalogGrid';
import CatalogItemCard from 'totara_catalog/explore/items/CatalogItemCard';
import CatalogItemModal from 'totara_catalog/explore/items/CatalogItemModal';
import useFilteredResults from './use_filtered_results';
import EmptyState from 'totara_catalog/explore/ui/EmptyState';
import SearchIcon from 'tui/components/icons/Search';
import QuestionIcon from 'tui/components/icons/Question';
import { getTabbableElements } from 'tui/dom/focus';

const props = defineProps({
  filters: Object,
  buttons: Object,
  config: Object,
  hideWhenShowingResults: String,
  hideHeaderButtons: Boolean,
  alwaysShowResults: Boolean,
  subtitle: String,
});

// UI Data
const {
  filterDefs,
  browseFilterDef,
  filterValue,
  showingResults,
  sortDef,
  sortValue,
  results,
  firstNewResultIndex,
  loadingResults,
  canLoadMore,
  loadMore,
  loadingMore,
  showEmptyState,
} = useFilteredResults(
  toRef(props, 'filters'),
  toRef(props, 'alwaysShowResults')
);

// Item modal
const itemModalOpen = ref(false);
const showingItem = ref(null);
const searchFilterRef = ref(null);

function handleItemClick(e, item) {
  if (props.config.enableDetails) {
    e.preventDefault();
    showingItem.value = item;
    itemModalOpen.value = true;
  }
}

function resetAllFilters() {
  sortValue.value = null;
}

function acceptSuggestion(suggestion) {
  const currentFilter = filterValue.value;
  currentFilter.search = suggestion;
  filterValue.value = currentFilter;
  searchFilterRef.value?.searchFilterFocus();
}

// Handle hiding/showing other area when results are visible
watch(
  showingResults,
  value => {
    const el = document.querySelector(props.hideWhenShowingResults);
    if (el) {
      el.style.display = value ? 'none' : '';
    }
  },
  { immediate: true }
);

// Restore focus to the first new item when loading more
const firstNewResultElement = ref(null);
const setFirstNewResultElement = inst => {
  firstNewResultElement.value = inst;
};
watch(firstNewResultElement, vm => {
  if (vm && document.contains(vm.$el)) {
    getTabbableElements(vm.$el)[0]?.focus();
  }
});
</script>

<template>
  <LayoutOneColumn
    class="tui-totara_catalog-explore"
    :title="$str('explore', 'totara_catalog')"
  >
    <template v-slot:header-buttons>
      <HeaderButtons v-if="!hideHeaderButtons" :buttons="buttons" />
    </template>
    <template v-slot:header-sub-content>
      <div class="tui-totara_catalog-explore__subtitle">{{ subtitle }}</div>
    </template>
    <template v-slot:content>
      <FilterBar
        ref="searchFilterRef"
        v-model:value="filterValue"
        :filters="filterDefs"
        :browse-filter="browseFilterDef"
        @reset-all-filters="resetAllFilters"
      />

      <div v-if="showingResults" class="tui-totara_catalog-explore__content">
        <SortBar
          v-model:sort-by="sortValue"
          :options="sortDef?.options"
          :total-count="results?.maxcount"
          :can-load-more="canLoadMore"
          :suggestion="results?.suggestion"
          @accept-suggestion="acceptSuggestion($event)"
        />

        <Loader :loading="loadingResults && !loadingMore">
          <CatalogGrid>
            <CatalogItemCard
              v-for="(item, index) in results?.items"
              :ref="
                index === firstNewResultIndex ? setFirstNewResultElement : null
              "
              :key="item.itemid"
              :item="item"
              :navigate="!config.enableDetails"
              @click="handleItemClick($event, item)"
            />
          </CatalogGrid>
        </Loader>

        <div class="tui-totara_catalog-explore__loadMoreContainer">
          <Button
            v-if="canLoadMore"
            :text="$str('loadmore', 'totara_core')"
            :loading="loadingMore"
            @click="loadMore"
          />
        </div>
        <EmptyState
          v-if="showEmptyState && !(loadingResults && !loadingMore)"
          :text="
            alwaysShowResults && !results?.is_filtered
              ? $str('no_items_found', 'totara_catalog')
              : $str('no_search_result', 'totara_catalog')
          "
          :hint="
            alwaysShowResults && !results?.is_filtered
              ? $str('no_items_found_hint', 'totara_catalog')
              : $str('no_search_result_hint', 'totara_catalog')
          "
        >
          <template v-slot:icon>
            <QuestionIcon
              v-if="alwaysShowResults && !results?.is_filtered"
              :size="700"
            />
            <SearchIcon v-else :size="700" />
          </template>
        </EmptyState>
      </div>
    </template>

    <template v-slot:modals>
      <ModalPresenter
        :open="itemModalOpen"
        @request-close="itemModalOpen = false"
      >
        <CatalogItemModal :item="showingItem" />
      </ModalPresenter>
    </template>
  </LayoutOneColumn>
</template>

<style lang="scss">
.tui-totara_catalog-explore {
  margin-bottom: var(--gap-6);

  &__subtitle {
    @include font(body-lg);
    margin: var(--gap-2) 0 var(--gap-6) 0;
    color: var(--color-neutral-6);
  }

  &__content {
    display: flex;
    flex-flow: column;
    gap: var(--gap-4);
    margin-top: var(--gap-3);
  }

  &__loadMoreContainer {
    display: flex;
    justify-content: space-around;
  }
}
</style>
