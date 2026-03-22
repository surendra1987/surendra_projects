<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Kevin Hottinger <kevin.hottinger@totaralearning.com>
  @module samples
-->

<template>
  <h2>FilterSidePanel</h2>
  A side panel designed for displaying filter options for a list content.
  <SamplesExample>
    <FilterSidePanel v-model:value="selection" v-bind="config">
      <MultiSelect
        v-model:value="selection.colours"
        :options="colourOptions"
        :title="'Contains colours'"
      />

      <MultiSelect
        v-model:value="selection.languages"
        :options="languages"
        :title="'In these languages'"
      />

      <MultiSelectCheckbox
        v-model:value="selection.countries"
        :has-columns="true"
        :options="countries"
        :title="'In these countries'"
      />

      <SearchFilter
        v-model:value="selection.search"
        label="Filter items by search"
        :show-label="true"
        :placeholder="'Filtering'"
        :stacked="true"
      />

      <SelectFilter
        v-model:value="selection.category"
        label="Within category"
        :show-label="true"
        :options="categories"
        :stacked="true"
      />
    </FilterSidePanel>
  </SamplesExample>
  <SamplesCtl id="sampleFilterSidePanelProps">
    <FormRow
      label="v-model:value"
      helpmsg="The two-way binding of a filter panel's selected values."
    >
      <pre style="height: 100px;">{{ selection }}</pre>
      <FormRowDetails>
        <code>v-model:value</code><br />
        <code>{[key: string]: string|string[]}</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="title" helpmsg="Specifies the title of a filter panel.">
      <InputText v-model:value="config.title" />
      <FormRowDetails>
        <code>title: string</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="skipContentId"
      helpmsg="Specifies the skip-to-content link's target element id."
    >
      <InputText v-model:value="config.skipContentId" />
      <FormRowDetails>
        <code>skipContentId: string</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="skipContentText"
      helpmsg="Specifies the skip-to-content link's text."
    >
      <InputText v-model:value="config.skipContentText" />
      <FormRowDetails>
        <code>skipContentText: string</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Slot options">
    <FormRow label="default">
      The content area for filter fields and options.
      <FormRowDetails>
        <code>default</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Event options">
    <FormRow label="input">
      Triggered when select change and emits the entire selected value.
      <FormRowDetails>
        <code>input: (selection) => void</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="active-count-change">
      Triggered when select change and emits the number of items selected.
      <FormRowDetails>
        <code>active-count-change: (count) => void</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>

<script setup>
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import InputText from 'tui/components/form/InputText';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
// Components
import FilterSidePanel from 'tui/components/filters/FilterSidePanel';
import MultiSelect from 'tui/components/filters/MultiSelectFilter';
import MultiSelectCheckbox from 'tui/components/filters/MultiSelectCheckboxFilter';
import SearchFilter from 'tui/components/filters/SearchFilter';
import SelectFilter from 'tui/components/filters/SelectFilter';
import { reactive, ref } from 'vue';

const colourOptions = [
  { id: 'orange', label: 'Orange' },
  { id: 'darkRed', label: 'Dark red' },
  { id: 'brightPink', label: 'Bright pink' },
  { id: 'gold', label: 'Gold' },
];
const languages = [
  { id: 'english', label: 'English' },
  { id: 'french', label: 'French' },
  { id: 'german', label: 'German' },
];
const countries = [
  { id: 'england', label: 'England' },
  { id: 'france', label: 'France' },
  { id: 'germany', label: 'Germany' },
  { id: 'italy', label: 'Italy' },
  { id: 'spain', label: 'Spain' },
  { id: 'belgium', label: 'Belgium' },
  { id: 'finland', label: 'Finland' },
];
const categories = [
  { id: '', label: 'No category selected' },
  { id: 'films', label: 'Films' },
  { id: 'comics', label: 'Comic books' },
  { id: 'music', label: 'Music' },
  { id: 'travel', label: 'Travel' },
];
const selection = ref({
  category: '',
  colours: [],
  countries: [],
  languages: [],
  search: '',
});

const config = reactive({
  skipContentId: 'sampleFilterSidePanelProps',
  skipContentText: 'Go to props',
  title: 'Filter results',
});
</script>
