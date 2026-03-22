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

  @author Alvin Smith <alvin.smith@totaralearning.com>
  @module samples
-->

<template>
  <SamplesExample>
    <Progress
      class="tui-sampleProgressBar"
      :small="small"
      :x-small="xSmall"
      :value="parseFloat(value)"
      :min="parseFloat(min)"
      :max="parseFloat(max)"
      :hide-value="hideValue"
      :format="format"
      :completed-text="completedText"
      :hide-background="hideBackground"
      :show-empty-state="showEmptyState"
      :chromeless="chromeless"
    />
  </SamplesExample>

  <SamplesCtl>
    <!-- small -->
    <FormRow v-slot="{ id }" label="Small">
      <RadioGroup v-model:value="small" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        small
      </FormRowDetails>
    </FormRow>

    <!-- xsmall -->
    <FormRow v-slot="{ id }" label="Extra small">
      <RadioGroup v-model:value="xSmall" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        xSmall
      </FormRowDetails>
    </FormRow>

    <!-- value -->
    <FormRow v-slot="{ id, label }" label="Current value">
      <InputNumber :id="id" v-model:value="value" :placeholder="label" />
      <FormRowDetails :id="id">
        value
      </FormRowDetails>
    </FormRow>

    <!-- min -->
    <FormRow v-slot="{ id, label }" label="Minimum value">
      <InputNumber :id="id" v-model:value="min" :placeholder="label" />
      <FormRowDetails :id="id">
        min
      </FormRowDetails>
    </FormRow>

    <!-- max -->
    <FormRow v-slot="{ id, label }" label="Maximum value">
      <InputNumber :id="id" v-model:value="max" :placeholder="label" />
      <FormRowDetails :id="id">
        max
      </FormRowDetails>
    </FormRow>

    <!-- hideValue -->
    <FormRow v-slot="{ id }" label="Hide value">
      <RadioGroup v-model:value="hideValue" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        hideValue
      </FormRowDetails>
    </FormRow>

    <!-- format -->
    <FormRow v-slot="{ id }" label="Format">
      <RadioGroup v-model:value="format" :horizontal="true">
        <Radio value="percent">Percent</Radio>
        <Radio value="number">Number</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        format
      </FormRowDetails>
    </FormRow>

    <!-- completedText -->
    <FormRow v-slot="{ id, label }" label="Completed Text">
      <InputText :id="id" v-model:value="completedText" :placeholder="label" />
      <FormRowDetails :id="id">
        completedText
      </FormRowDetails>
    </FormRow>

    <!-- hideBackground -->
    <FormRow v-slot="{ id }" label="Hide background">
      <RadioGroup v-model:value="hideBackground" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        hideBackground
      </FormRowDetails>
    </FormRow>

    <!-- showEmptyState -->
    <FormRow v-slot="{ id }" label="Show empty state">
      <RadioGroup v-model:value="showEmptyState" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        showEmptyState
      </FormRowDetails>
    </FormRow>

    <!-- chromeless -->
    <FormRow v-slot="{ id }" label="Chromeless">
      <RadioGroup v-model:value="chromeless" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        chromeless
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>

<script>
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import InputNumber from 'tui/components/form/InputNumber';
import InputText from 'tui/components/form/InputText';
import Progress from 'tui/components/progress/Progress';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';

export default {
  components: {
    FormRow,
    FormRowDetails,
    InputNumber,
    InputText,
    Progress,
    Radio,
    RadioGroup,
    SamplesExample,
    SamplesCtl,
  },

  data() {
    return {
      small: false,
      xSmall: false,
      value: 0,
      min: 0,
      max: 100,
      hideValue: false,
      format: 'percent',
      completedText: 'completed',
      hideBackground: false,
      showEmptyState: false,
      chromeless: false,
    };
  },
};
</script>

<style lang="scss">
.tui-sampleProgressBar {
  max-width: 400px;
}
</style>
