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
  @module tui
-->

<script setup>
import { ref } from 'vue';
import SortBar from 'tui/components/filters/SortBar';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import InputText from 'tui/components/form/InputText';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';

const sortBy = ref('name');

const options = [
  { id: 'name', label: 'Alphabetical' },
  { id: 'updated_at', label: 'Latest' },
];
</script>

<template>
  <div>
    <h2>SortBar</h2>
    <p>
      A component typically used above listings to control ordering and show the
      number of available items.
    </p>
    <SamplesExample>
      <SortBar v-model:sort-by="sortBy" :options="options">
        <template v-slot:start>
          {{ $str('listitemplural', 'totara_core', 9001) }}
        </template>
      </SortBar>
    </SamplesExample>
    <SamplesCtl>
      <FormRow label="options" required>
        Defines the options displayed in a sort select.
        <FormRowDetails>
          <code>options: Array&lt;Option|OptionGroup&gt;</code> Refer to
          SelectFilter for the interface of an option.
        </FormRowDetails>
        <pre>
// The example above is using:
{{ options }}
        </pre>
      </FormRow>
      <FormRow
        label="sortBy"
        helpmsg="Two-way binding the value of the selected option id on the sort select."
        required
      >
        <InputText v-model:value="sortBy" />

        <FormRowDetails>
          <code>v-model:sortBy</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Slot Options">
      <FormRow label="start">
        The content area on the start of the sort bar.
        <FormRowDetails>
          <code>#start</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>
