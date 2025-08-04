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
  <h2>SearchBox</h2>
  <SamplesExample>
    <h3>Standalone</h3>
    <SearchBox
      v-model:value="valSearch"
      v-bind="config"
      @submit="searchSubmit"
    />
    <br />
    <h3>In form</h3>
    <Form :class="'tui-testForm'">
      <FormRow v-slot="{ id }" :label="'Search data'">
        <SearchBox
          :id="id"
          :drop-label="true"
          aria-label="search field"
          @input="searchInput"
          @submit="searchSubmit"
        />
      </FormRow>
    </Form>
  </SamplesExample>
  <SamplesCtl>
    <FormRow label="v-model:value" required>
      The two-way binding value of a search box input
      <FormRowDetails>
        <code>v-model:value</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="ariaLabel"
      helpmsg="Specifies the aria-label of a search input."
      required
    >
      <InputText v-model:value="config.ariaLabel" />
      <FormRowDetails>
        <code>ariaLabel: string</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="disabled"
      helpmsg="Specifies whether disable a search bar input."
    >
      <ToggleSwitch
        v-model:value="config.disabled"
        aria-label="set disabled toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>disabled: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="placeholder"
      helpmsg="Specifies the placeholder of a search input."
    >
      <InputText v-model:value="config.placeholder" />
      <FormRowDetails>
        <code>placeholder: string</code>
      </FormRowDetails>
      <FormRowDefaults>$str('search', 'core')</FormRowDefaults>
    </FormRow>
    <FormRow
      label="charLength"
      helpmsg="Specifies the length of the search bar."
    >
      <InputNumber v-model:value.number="config.charLength" />
      <FormRowDetails>
        <code>charLength: $tui-char-length-scale</code> <br />
        $tui-char-length-scale: 2, 3, 4, 5, 10, 15, 20, 25, 30, 50, 75, 100;
      </FormRowDetails>
    </FormRow>
    <FormRow label="class" helpmsg="Add classes to a search box component.">
      <InputText v-model:value="config.class" />
      <FormRowDetails>
        <code>class: string</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="labelVisible"
      helpmsg="Specifies whether to hide a search box's label."
    >
      <ToggleSwitch
        v-model:value="config.labelVisible"
        aria-label="set labelVisible toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>labelVisible: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="dropLabel"
      helpmsg="Specifies whether to remove a search box's label."
    >
      <ToggleSwitch
        v-model:value="config.dropLabel"
        aria-label="set dropLabel toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>dropLabel: boolean</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Event options">
    <FormRow label="submit">
      The event is triggered when a search box input is submitted.
      <FormRowDetails>
        <code>submit: () => void</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="input">
      The event is triggered when user inputs in a search box input.
      <FormRowDetails>
        <code>input: (value) => void</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="clear">
      The event is triggered when a search box's clear button is clicked.
      <FormRowDetails>
        <code>clear: (value) => void</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>

<script setup>
// Components
import Form from 'tui/components/form/Form';
import FormRow from 'tui/components/form/FormRow';
import SearchBox from 'tui/components/form/SearchBox';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';
import InputNumber from 'tui/components/form/InputNumber';
import InputText from 'tui/components/form/InputText';
import { reactive, ref } from 'vue';

const config = reactive({
  disabled: false,
  ariaLabel: 'search',
  charLength: '',
  class: '',
  dropLabel: false,
  labelVisible: false,
  placeholder: 'keywords...',
});

const valSearch = ref('');
const valFormSearch = ref('');

/**
 * Search input change
 *
 */
const searchInput = () => {
  console.log(valFormSearch.value);
};

/**
 * Triggered search submit
 *
 */
const searchSubmit = () => {
  this.disableFormInputs();
};
</script>
