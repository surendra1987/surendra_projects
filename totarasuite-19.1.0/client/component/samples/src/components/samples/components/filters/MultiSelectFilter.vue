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

<script setup>
import { ref } from 'vue';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import InputText from 'tui/components/form/InputText';
import MultiSelectFilter from 'tui/components/filters/MultiSelectFilter';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';

const hiddenTitle = ref(false);
const limitItems = ref(null);
const options = ref([
  {
    id: 'course',
    label: 'Include courses',
  },
  {
    id: 'program',
    label: 'Include programs',
  },
  {
    id: 'user',
    label: 'Include users',
  },
  {
    id: 'video',
    label: 'Include Videos',
  },
  {
    id: 'conference',
    label: 'Include conferences',
  },
]);
const selection = ref(['program']);
const title = ref('Title');
</script>

<template>
  <SamplesExample>
    <div>
      <MultiSelectFilter
        v-model:value="selection"
        :options="options"
        :title="title"
        :hidden-title="hiddenTitle"
        :visible-item-limit="limitItems"
      />
      <br />
      {{ selection }}
    </div>
  </SamplesExample>

  <SamplesCtl>
    <FormRow
      v-slot="{ id }"
      label="Visible item limit"
      helpmsg="Extra items are added to the collapsible area"
    >
      <RadioGroup v-model:value="limitItems" :horizontal="true">
        <Radio :value="null">No limit</Radio>
        <Radio :value="1">1</Radio>
        <Radio :value="3">3</Radio>
        <Radio :value="4">4</Radio>
        <Radio :value="5">5</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>visibleItemLimit: number</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Hidden title">
      <RadioGroup v-model:value="hiddenTitle" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>hiddenTitle: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Title">
      <InputText :id="id" v-model:value="title" />
      <FormRowDetails :id="id">
        <code>title: string</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>
