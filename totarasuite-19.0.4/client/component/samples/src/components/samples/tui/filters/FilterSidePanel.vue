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
  <div>
    <SamplesExample>
      <FilterSidePanel v-model:value="selection" title="Filter results">
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

      <div>
        <h1>Filter values</h1>
        {{ selection }}
      </div>
    </SamplesExample>

    <SamplesCode>
      <template v-slot:template>{{ codeTemplate }}</template>
      <template v-slot:script>{{ codeScript }}</template>
    </SamplesCode>
  </div>
</template>

<script>
import SamplesCode from 'samples/components/sample_parts/misc/SamplesCode';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
// Components
import FilterSidePanel from 'tui/components/filters/FilterSidePanel';
import MultiSelect from 'tui/components/filters/MultiSelectFilter';
import MultiSelectCheckbox from 'tui/components/filters/MultiSelectCheckboxFilter';
import SearchFilter from 'tui/components/filters/SearchFilter';
import SelectFilter from 'tui/components/filters/SelectFilter';

export default {
  components: {
    SamplesCode,
    SamplesExample,
    FilterSidePanel,
    MultiSelect,
    MultiSelectCheckbox,
    SearchFilter,
    SelectFilter,
  },

  data() {
    return {
      colourOptions: [
        {
          id: 'orange',
          label: 'Orange',
        },
        {
          id: 'darkRed',
          label: 'Dark red',
        },
        {
          id: 'brightPink',
          label: 'Bright pink',
        },
        {
          id: 'gold',
          label: 'Gold',
        },
      ],
      languages: [
        {
          id: 'english',
          label: 'English',
        },
        {
          id: 'french',
          label: 'French',
        },
        {
          id: 'german',
          label: 'German',
        },
      ],
      countries: [
        {
          id: 'england',
          label: 'England',
        },
        {
          id: 'france',
          label: 'France',
        },
        {
          id: 'germany',
          label: 'Germany',
        },
        {
          id: 'italy',
          label: 'Italy',
        },
        {
          id: 'spain',
          label: 'Spain',
        },
        {
          id: 'belgium',
          label: 'Belgium',
        },
        {
          id: 'finland',
          label: 'Finland',
        },
      ],
      categories: [
        {
          id: '',
          label: 'No category selected',
        },
        {
          id: 'films',
          label: 'Films',
        },
        {
          id: 'comics',
          label: 'Comic books',
        },
        {
          id: 'music',
          label: 'Music',
        },
        {
          id: 'travel',
          label: 'Travel',
        },
      ],
      selection: {
        category: '',
        colours: [],
        countries: [],
        languages: [],
        search: '',
      },

      codeTemplate: `<FilterSidePanel v-model:value="selection" :title="'Filter results'">

  <!-- Filters -->
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

</FilterSidePanel>`,
      codeScript: `import FilterSidePanel from 'tui/components/filters/FilterSidePanel';
import MultiSelect from 'tui/components/filters/MultiSelectFilter';
import SearchFilter from 'tui/components/filters/SearchFilter';
import SelectFilter from 'tui/components/filters/SelectFilter';

export default {
  components: {
    FilterSidePanel,
    MultiSelect,
    SearchFilter,
    SelectFilter,
  },

  data() {
    return {
      colourOptions: [
        {
          id: 'orange',
          label: 'Orange',
        },
        {
          id: 'darkRed',
          label: 'Dark red',
        },
        {
          id: 'brightPink',
          label: 'Bright pink',
        },
        {
          id: 'gold',
          label: 'Gold',
        },
      ],
      languages: [
        {
          id: 'english',
          label: 'English',
        },
        {
          id: 'french',
          label: 'French',
        },
        {
          id: 'german',
          label: 'German',
        },
      ],
      categories: [
        {
          id: '',
          label: 'No category selected',
        },
        {
          id: 'films',
          label: 'Films',
        },
        {
          id: 'comics',
          label: 'Comic books',
        },
        {
          id: 'music',
          label: 'Music',
        },
        {
          id: 'travel',
          label: 'Travel',
        },
      ],
      selection: {
        category: '',
        colours: [],
        languages: [],
        search: '',
      },
    }
  }
}`,
    };
  },
};
</script>
