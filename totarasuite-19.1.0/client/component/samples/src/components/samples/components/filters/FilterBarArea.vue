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
  @module samples
-->

<template>
  <div>
    <SamplesExample>
      <FilterBarArea
        v-model:value="filterSelection"
        accessibility-title="Sample filter bar area"
        :has-bottom-bar="filterHasBottomBar"
        :has-top-bar="filterHasTopBar"
        :reset-values="resetValues"
      >
        <template v-slot:bar-filters="{ filters, update, stacked }">
          <SearchFilter
            label="Filter by search"
            :bar-filter="true"
            :stacked="stacked"
            :placeholder="'Search'"
            :value="filters.search"
            @input="update('search', $event)"
          />
          <SelectFilter
            label="Category"
            :bar-filter="true"
            :stacked="stacked"
            :show-label="showLabel"
            :options="categoryOptions"
            :value="filters.category"
            @input="update('category', $event)"
          />
        </template>

        <template v-slot:extra-filters="{ filters, update, stacked }">
          <SelectFilter
            label="Type"
            :bar-filter="true"
            :stacked="true"
            :show-label="showLabel"
            :options="typeOptions"
            :value="filters.type"
            @input="update('type', $event)"
          />

          <MultiSelectCheckbox
            :has-columns="!stacked"
            :value="filters.progress"
            :options="progressOptions"
            :title="'Your progress'"
            @input="update('progress', $event)"
          />

          <MultiSelectCheckbox
            :has-columns="!stacked"
            :value="filters.status"
            :options="statusOptions"
            :title="'Activity status'"
            @input="update('status', $event)"
          />
        </template>
      </FilterBarArea>

      <div>
        <h1>Filter values</h1>
        {{ filterSelection }}
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

      <FormRow label="showLabel">
        <RadioGroup v-model:value="showLabel" :horizontal="true">
          <Radio :value="true">true</Radio>
          <Radio :value="false">false</Radio>
        </RadioGroup>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import FormRowDetails from 'tui/components/form/FormRowDetails';
import FormRow from 'tui/components/form/FormRow';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';

// Components
import FilterBarArea from 'tui/components/filters/FilterBarArea';
import MultiSelectCheckbox from 'tui/components/filters/MultiSelectCheckboxFilter';
import SearchFilter from 'tui/components/filters/SearchFilter';
import SelectFilter from 'tui/components/filters/SelectFilter';

export default {
  components: {
    FormRowDetails,
    FilterBarArea,
    FormRow,
    MultiSelectCheckbox,
    SamplesExample,
    SamplesCtl,
    SearchFilter,
    SelectFilter,
    Radio,
    RadioGroup,
  },

  data() {
    return {
      resetValues: {
        bar: {
          search: '',
          category: '',
        },
        extra: {
          type: '',
          progress: [],
          status: [],
        },
      },
      filterHasTopBar: true,
      filterHasBottomBar: true,
      typeOptions: [
        { id: '', label: 'All' },
        { id: 'personal', label: 'Personal' },
        { id: 'company', label: 'Company' },
      ],
      progressOptions: [
        {
          id: 'not_started',
          label: 'Not started',
        },
        { id: 'in_progress', label: 'In progress' },
        { id: 'complete', label: 'Complete' },
        { id: 'not_submitted', label: 'Not submitted' },
        { id: 'na', label: 'n/a (view only)' },
      ],
      statusOptions: [
        { id: 'overdue', label: 'Overdue' },
        { id: 'open', label: 'Open' },
        { id: 'complete', label: 'Complete' },
        { id: 'incomplete', label: 'Incomplete' },
      ],
      showLabel: false,
      categoryOptions: [
        { id: '', label: 'Overdue' },
        { id: 'open', label: 'Open' },
        { id: 'complete', label: 'Complete' },
        { id: 'incomplete', label: 'Incomplete' },
      ],
      filterSelection: {
        bar: {
          search: '',
          category: '',
        },
        extra: {
          type: '',
          progress: [],
          status: [],
        },
      },
    };
  },
};
</script>
