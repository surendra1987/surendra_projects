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
  @module tui
-->

<template>
  <div>
    <SamplesExample>
      <FilterBar
        :title="'Filter results'"
        :has-top-bar="filterHasTopBar"
        :has-bottom-bar="filterHasBottomBar"
        :show-reset="!filtersInactive"
        @reset="resetFilters"
      >
        <template v-slot:filters-left="{ stacked }">
          <SelectFilter
            v-model:value="selection.category"
            label="Within category"
            :show-label="true"
            :options="categories"
            :stacked="stacked"
          />

          <SelectFilter
            v-model:value="selection.colours"
            label="Colour"
            :show-label="true"
            :options="colourOptions"
            :stacked="stacked"
          />
        </template>

        <template v-slot:filters-right="{ stacked }">
          <SearchFilter
            v-model:value="selection.search"
            label="Filter items by search"
            :show-label="false"
            :placeholder="'Search'"
            :stacked="stacked"
            :debounce-input="false"
          />
        </template>
      </FilterBar>

      <div>
        <h1>Filter values</h1>
        {{ selection }}
      </div>
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id }" label="hasTopBar">
        <RadioGroup v-model:value="filterHasTopBar" :horizontal="true">
          <Radio :value="true">true</Radio>
          <Radio :value="false">false</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          hasTopBar
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="hasBottomBar">
        <RadioGroup v-model:value="filterHasBottomBar" :horizontal="true">
          <Radio :value="true">true</Radio>
          <Radio :value="false">false</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          hasBottomBar
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';

// Components
import FilterBar from 'tui/components/filters/FilterBar';
import SearchFilter from 'tui/components/filters/SearchFilter';
import SelectFilter from 'tui/components/filters/SelectFilter';

export default {
  components: {
    FormRow,
    FormRowDetails,
    Radio,
    RadioGroup,
    SamplesCtl,
    SamplesExample,
    FilterBar,
    SearchFilter,
    SelectFilter,
  },

  data() {
    return {
      colourOptions: [
        {
          id: '',
          label: 'Please select',
        },
        {
          id: 'orange',
          label: 'Orange',
        },
        {
          id: 'darkRed',
          label: 'Dark red',
        },
      ],
      categories: [
        {
          id: '',
          label: 'No category selected',
        },
        {
          id: 'films',
          label: 'films',
        },
        {
          id: 'comics',
          label: 'Comic books',
        },
      ],
      filterHasTopBar: true,
      filterHasBottomBar: true,
      selection: {
        category: '',
        colours: '',
        search: '',
      },
    };
  },

  computed: {
    filtersInactive() {
      return Object.keys(this.selection).every(
        key => this.selection[key] === ''
      );
    },
  },

  methods: {
    resetFilters() {
      this.selection = {
        category: '',
        colours: '',
        search: '',
      };
    },
  },
};
</script>
