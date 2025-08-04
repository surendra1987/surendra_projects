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
import { computed, ref } from 'vue';
import { unique } from 'tui/util';
import useParamState from 'tui/state/use_param_state';
import FilterSidePanel from 'tui/components/filters/FilterSidePanel';
import Layout from 'tui/components/layouts/LayoutTwoColumn';
import SearchFilter from 'tui/components/filters/SearchFilter';
import SelectFilter from 'tui/components/filters/SelectFilter';
import useFilteredResults from './use_filtered_results';
import { getSamples, wrapSampleComponent } from './samples';

const state = useParamState({
  fromParams: ({ parsed }) => ({
    tuiComponent: parsed.tc || null,
    sample: parsed.component || null,
  }),
  toParams: value => ({ tc: value.tuiComponent, component: value.sample }),
});

const samples = getSamples();

const filter = ref('');

const vueComponent = computed(() => {
  const component = samples.find(x => x.key === state.value.sample);
  return component ? wrapSampleComponent(component) : null;
});

const tuiComponentOptions = [{ id: null, label: 'All' }].concat(
  unique(samples.map(x => x.tuiComponent))
);

const { resultGroups } = useFilteredResults({ samples, state, filter });

function select(result) {
  state.push({
    ...state.value,
    sample: result.key,
  });
}

function sampleUrl(result) {
  return state.urlFor({
    ...state.value,
    sample: result.key,
  });
}
</script>

<template>
  <div class="tui-samples">
    <Layout>
      <template v-slot:page-title>
        {{ $str('pluginname', 'totara_tui') }}
      </template>
      <template v-slot:left>
        <FilterSidePanel title="Filter results">
          <SearchFilter
            v-model:value="filter"
            label="Filter components"
            :show-label="true"
            :stacked="true"
            :debounce-input="false"
          />

          <SelectFilter
            v-model:value="state.tuiComponent"
            label="Within Tui component"
            :show-label="true"
            :stacked="true"
            :options="tuiComponentOptions"
          />

          <div class="tui-samples__filter">
            <div class="tui-samples__results">
              <div v-for="(group, i) in resultGroups" :key="i">
                <div class="tui-samples__resultGroupHeader">
                  {{ group.name }}
                </div>
                <a
                  v-for="result in group.results"
                  :key="result.component"
                  class="tui-samples__result"
                  :class="{
                    'tui-samples__result--selected': state.sample == result.key,
                  }"
                  :href="sampleUrl(result)"
                  @click.prevent="select(result)"
                >
                  {{ result.text }}
                </a>
              </div>
            </div>
          </div>
        </FilterSidePanel>
      </template>
      <template v-slot:right>
        <component :is="vueComponent" />
      </template>
    </Layout>
  </div>
</template>

<style lang="scss">
.tui-samples {
  &__filter {
    display: flex;
    flex-direction: column;
    flex-grow: 1;

    > * + * {
      margin-top: var(--gap-4);
    }
  }

  &__results {
    display: flex;
    flex-direction: column;
    max-height: 50vh;
    overflow-x: hidden;
    overflow-y: auto;
  }

  &__result {
    display: block;
    padding: var(--gap-1) var(--gap-2);
    color: var(--color-state);
    border-radius: 3px;

    &--selected,
    &--selected:focus {
      color: var(--color-neutral-1);
      background-color: var(--color-state);
    }

    &:hover,
    &:focus {
      text-decoration: none;
    }

    &:hover,
    &:focus-visible {
      color: var(--color-neutral-1);
      background-color: var(--color-state-hover);
    }
  }

  &__resultGroupHeader {
    margin-top: var(--gap-1);
    color: var(--color-neutral-6);
    font-weight: bold;
    font-size: font-size-px(11);
    text-transform: uppercase;
  }

  &--highlight {
    color: pink;
    background: pink;
  }
}
</style>
