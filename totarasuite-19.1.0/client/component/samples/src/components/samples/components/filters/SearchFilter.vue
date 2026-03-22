<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

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

<script setup>
import { ref } from 'vue';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import InputText from 'tui/components/form/InputText';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';

// Components
import SearchFilter from 'tui/components/filters/SearchFilter';

const barFilter = ref(false);
const debounceInput = ref(true);
const disabled = ref(false);
const dropLabel = ref(false);
const label = ref('');
const searchFilter = ref('');
const showLabel = ref(true);
const stacked = ref(false);
</script>

<template>
  <SamplesExample>
    <SearchFilter
      v-model:value="searchFilter"
      :bar-filter="barFilter"
      :debounce-input="debounceInput"
      :disabled="disabled"
      :drop-label="dropLabel"
      :label="label"
      :show-label="showLabel"
      :stacked="stacked"
    />
  </SamplesExample>

  <SamplesCtl>
    <FormRow v-slot="{ id }" label="Bar filter">
      <RadioGroup v-model:value="barFilter" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>barFilter: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Debounce input">
      <RadioGroup v-model:value="debounceInput" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>debounceInput: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Disabled">
      <RadioGroup v-model:value="disabled" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>disabled</code>
      </FormRowDetails>
    </FormRow>

    <FormRow
      v-slot="{ id }"
      label="Drop label"
      helpmsg="Removes the label from the dom"
    >
      <RadioGroup v-model:value="dropLabel" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>dropLabel: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Label">
      <InputText :id="id" v-model:value="label" />
      <FormRowDetails :id="id">
        <code>label: string</code>
      </FormRowDetails>
    </FormRow>

    <FormRow
      v-slot="{ id }"
      label="Show label"
      helpmsg="Setting this to false hides the label (but it remains in the dom)"
    >
      <RadioGroup v-model:value="showLabel" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>showLabel: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Stacked">
      <RadioGroup v-model:value="stacked" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>stacked</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>
