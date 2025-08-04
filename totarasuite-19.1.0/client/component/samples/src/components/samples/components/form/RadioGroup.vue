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
    A group of radio buttons

    <SamplesExample>
      <RadioGroup
        v-model:value="colour"
        :horizontal="horizontal"
        :disabled="disabled"
        :required="required"
        :aria-labelledby="ariaLabelledby"
      >
        <Radio value="red">
          Red
        </Radio>
        <Radio value="green">
          Green
        </Radio>
        <Radio value="blue">
          Blue
        </Radio>
        <Radio value="yellow">
          Yellow
        </Radio>
      </RadioGroup>

      <h3>Form</h3>
      <Form>
        <FormRow label="Radio group">
          <RadioGroup
            v-model:value="food"
            :horizontal="horizontal"
            :disabled="disabled"
            :aria-labelledby="ariaLabelledby"
          >
            <Radio value="eggs">
              Eggs
            </Radio>
            <Radio value="ham">
              Ham
            </Radio>
            <Radio value="cheese">
              Cheese
            </Radio>
            <Radio value="onion">
              Onion
            </Radio>
          </RadioGroup>
        </FormRow>
      </Form>
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id }" label="Horizontal">
        <RadioGroup v-model:value="horizontal" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          horizontal
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
      <FormRow v-slot="{ id }" label="Required">
        <RadioGroup v-model:value="required" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          required
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="ariaLabelledby">
        <InputText :id="id" v-model:value="ariaLabelledby" />
        <FormRowDetails :id="id">
          ariaLabelledby
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import Form from 'tui/components/form/Form';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import InputText from 'tui/components/form/InputText';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';

export default {
  components: {
    Form,
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
      colour: 'green',
      food: 'eggs',
      disabled: false,
      horizontal: false,
      required: false,
      ariaLabelledby: '',
    };
  },
};
</script>
