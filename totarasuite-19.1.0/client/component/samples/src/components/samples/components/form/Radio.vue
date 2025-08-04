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
    A stand-alone radio buttons, to group radios together use the radio group
    component

    <SamplesExample>
      <Radio
        :name="name"
        :value="value"
        :disabled="disabled"
        :checked="checked"
      >
        {{ label }}
      </Radio>
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id }" label="Label">
        <InputText :id="id" v-model:value="label" />
      </FormRow>

      <FormRow v-slot="{ id }" label="Name">
        <InputText :id="id" v-model:value="name" />
        <FormRowDetails :id="id">
          name
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Value" required>
        <InputText :id="id" v-model:value="value" />
        <FormRowDetails :id="id">
          value
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Checked">
        <RadioGroup v-model:value="checked" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          checked
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Disabled">
        <RadioGroup v-model:value="disabled" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          disabled
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import InputText from 'tui/components/form/InputText';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';

export default {
  components: {
    FormRow,
    FormRowDetails,
    InputText,
    Radio,
    RadioGroup,
    SamplesExample,
    SamplesCtl,
  },

  data() {
    return {
      disabled: false,
      checked: false,
      label: 'Red',
      name: 'Red',
      value: 'red',
    };
  },
};
</script>
