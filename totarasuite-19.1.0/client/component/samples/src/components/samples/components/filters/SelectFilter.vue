<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

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
    <h2>SelectFilter</h2>
    Use a dropdown menu to display options for users to select.
    <SamplesExample>
      <SelectFilter
        v-model:value="selected"
        label="Colour"
        :show-label="config.showLabel"
        :disabled="config.disabled"
        :multiple="config.multiple"
        :large="config.large"
        :options="[
          { id: '', label: 'Please select', disabled: true },
          {
            label: 'Warm',
            options: [
              { id: 'orange', label: 'Orange' },
              { id: 'darkRed', label: 'Dark red' },
              { id: 'gold', label: 'Gold' },
            ],
          },
          { label: 'Cold', options: [{ id: 'blue', label: 'Blue' }] },
        ]"
        :stacked="config.stacked"
      />

      <p>Selected value: {{ selected }}</p>
    </SamplesExample>

    <SamplesCtl>
      <FormRow
        v-slot="{ id }"
        label="v-model:value"
        required
        helpmsg="String. The two-way binding value of the select."
      >
        <InputText v-model:value="selected" placeholder="Selected value" />

        <FormRowDetails :id="id">
          <code>v-model:value</code>
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="options" required>
        Defines the available choices in a select list, including option groups
        if needed.

        <FormRowDetails :id="id">
          <code>options: Array&lt;Option|OptionGroup&gt;</code><br />
          <code
            >Option: string|{ id: string, dieabled: boolean, label: string
            }</code
          ><br />
          <code>OptionGroup: { label: string, options: Option[] }</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="disabled"
        helpmsg="Specifies that a select should be disabled."
      >
        <ToggleSwitch
          v-model:value="config.disabled"
          aria-label="disabled toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>disabled: boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="multiple"
        helpmsg="Specifies the select input is a mutiselect."
      >
        <ToggleSwitch
          v-model:value="config.multiple"
          aria-label="multiple toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>multiple: boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="stacked"
        helpmsg="Specifies to stack the label and the select input."
      >
        <ToggleSwitch
          v-model:value="config.stacked"
          aria-label="stacked toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>stacked: boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="large"
        helpmsg="Specifies the size of the select input."
      >
        <ToggleSwitch
          v-model:value="config.large"
          aria-label="large toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>large: boolean</code>
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Show label">
        <ToggleSwitch
          v-model:value="config.showLabel"
          aria-label="showLabel toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>showLabel: boolean</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Event Options">
      <FormRow label="input">
        Triggered when an option is selected. Returns the option's id value, or
        the text if no id specified.
        <FormRowDetails>
          <code>input: (value: string) => void</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="blur">
        Triggered when a select loses focus.
        <FormRowDetails>
          <code>blur: (event: FocusEvent) => void</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script setup>
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
import InputText from 'tui/components/form/InputText';

// Components
import SelectFilter from 'tui/components/filters/SelectFilter';
import { reactive, ref } from 'vue';

const selected = ref('');
const config = reactive({
  showLabel: true,
  stacked: false,
  disabled: false,
  multiple: false,
  large: false,
});
</script>
