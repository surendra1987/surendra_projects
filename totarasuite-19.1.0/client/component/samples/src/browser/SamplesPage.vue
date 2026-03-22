<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Simon Chester <simon.chester@totara.com>
  @module samples
-->

<script setup>
import { computed, nextTick, ref, useTemplateRef, watch } from 'vue';
import useParamState from 'tui/state/use_param_state';
import Layout from 'tui/components/layouts/LayoutTwoColumn';
import TreeView from 'tui/components/treeview/TreeView';
import { getSamples, wrapSampleComponent } from './samples';
import useSearchResults from './use_search_results';
import { getTabbableElements } from 'tui/dom/focus';

const state = useParamState({
  fromParams: ({ parsed }) => ({
    sample: parsed.sample || null,
    chromeless: parsed.chromeless || false,
  }),
  toParams: value => ({ sample: value.sample }),
});

const isChromeless = state.value.chromeless === 'true';
if (isChromeless) document.body.classList.add('tui-samples--chromeless');

const samples = getSamples();

const filter = ref('');

const expandedItems = ref([]);

const selectedItems = computed({
  get: () => (state.value.sample ? [state.value.sample] : [null]),
  set: val => {
    const selected = val?.[0];
    state.value.sample = selected;
    if (!selected) {
      nextTick(() => {
        searchRef.value?.focus();
      });
    }
  },
});

watch(
  () => state.value.sample,
  val => {
    if (val) {
      const folders = val.split('/').slice(0, -1);
      for (let i = 0; i < folders.length; i++) {
        const folder = folders.slice(0, i + 1).join('/');
        if (!expandedItems.value.includes(folder)) {
          expandedItems.value.push(folder);
        }
      }
    }
  },
  { immediate: true }
);

const vueComponent = computed(() => {
  const component = state.value.sample
    ? samples.list.find(x => x.id === state.value.sample)
    : null;
  return component ? wrapSampleComponent(component) : null;
});

const tree = computed(() => {
  return [
    { label: 'Search', id: null, children: [], selectable: true },
    ...samples.tree,
  ];
});

const { results } = useSearchResults({ samples, state, filter });

const searchRef = useTemplateRef('search-el');
const resultsRef = useTemplateRef('results-el');

function select(result) {
  state.push({
    sample: result.id,
  });
}

function sampleUrl(result) {
  return state.urlFor({
    sample: result.id,
  });
}

function focusFirstResult() {
  if (resultsRef.value) {
    getTabbableElements(resultsRef.value)?.[0]?.focus();
  }
}

function focusResult(dir) {
  if (!resultsRef.value) {
    return;
  }
  const current = document.activeElement;
  const els = getTabbableElements(resultsRef.value);
  let index = els.indexOf(current);
  if (index < 0) {
    return;
  }
  index += dir;
  if (index < 0) {
    searchRef.value?.focus();
  } else if (index >= els.length) {
    // nop
  } else {
    els[index].focus();
  }
}
</script>

<template>
  <div class="tui-samples">
    <Layout v-if="!isChromeless">
      <template v-slot:left>
        <div class="tui-samples__sidebar">
          <h1 class="tui-samples__title">Tui</h1>

          <TreeView
            v-model:selectedItems="selectedItems"
            v-model:expandedItems="expandedItems"
            :items="tree"
          />
        </div>
      </template>
      <template v-slot:right>
        <component :is="vueComponent" v-if="vueComponent" />
        <div v-else class="tui-samples__searchView">
          <input
            ref="search-el"
            v-model="filter"
            class="tui-samples__searchInput"
            type="search"
            aria-label="Search"
            placeholder="Search..."
            @keydown.down.prevent="focusFirstResult"
          />

          <div
            ref="results-el"
            class="tui-samples__searchResults"
            @keydown.down.prevent="focusResult(1)"
            @keydown.up.prevent="focusResult(-1)"
          >
            <a
              v-for="result in results"
              :key="result.id"
              class="tui-samples__searchResult"
              :href="sampleUrl(result)"
              @click.prevent="select(result)"
            >
              <div class="tui-samples__searchResult-label">
                {{ result.label }}
              </div>
              <div class="tui-samples__searchResult-id">
                {{ result.id }}
              </div>
            </a>
          </div>
        </div>
      </template>
    </Layout>
    <component :is="vueComponent" v-else-if="vueComponent" />
  </div>
</template>

<style lang="scss">
.tui-samples {
  &__sidebar {
    display: flex;
    flex-flow: column;
    gap: gap(4);
  }

  &__title {
    margin: 0;
  }

  &--highlight {
    color: pink;
    background: pink;
  }

  &__searchView {
    display: flex;
    flex-flow: column;
    gap: gap(4);
  }

  &__searchInput {
    display: flex;
    height: 3rem;
    padding: gap(3) gap(5);
    background-color: var(--color-neutral-3);
    border: none;
    border-radius: var(--border-radius-curved);

    &:focus {
      @include tui-focus;
      background-color: var(--color-background);
      outline-offset: 0;
    }
  }

  &__searchResults {
    display: flex;
    flex-flow: column;
  }

  &__searchResult {
    padding: gap(2) gap(3);
    border-radius: var(--btn-radius);
    transition: tui-transitions('button', background-color);

    &,
    &:hover,
    &:focus {
      color: var(--color-text);
      text-decoration: none;
    }

    &:hover {
      background-color: rgba(0, 0, 0, 0.05);
    }
    &:active {
      background-color: rgba(0, 0, 0, 0.07);
    }
    &:focus-visible {
      @include tui-focus;
    }

    &-label {
      @include font(h4);
    }

    &-id {
      color: var(--color-neutral-6);
    }
  }
}

.tui-samples--chromeless {
  .theme_inspire__nav,
  .breadcrumb-container,
  .userToolbar,
  .page-footer {
    display: none;
  }
  #page,
  #region-main {
    padding: 0;
  }
  .row {
    margin: 0;
  }
}
</style>
