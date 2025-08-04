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
    A set of standard input field components. Each type (text, number, password,
    etc) has its own stand-alone component.

    <SamplesExample>
      <Form>
        <FormRow v-slot="{ id, label }" :label="'InputText'">
          <InputText
            :id="id"
            v-model:value="inputText"
            :disabled="disabled"
            :placeholder="label"
            :size="size"
          />
        </FormRow>

        <FormRow v-slot="{ id, label }" :label="'InputEmail'">
          <InputEmail
            :id="id"
            v-model:value="inputEmail"
            :disabled="disabled"
            :placeholder="label"
            :size="size"
          />
        </FormRow>

        <FormRow v-slot="{ id, label }" :label="'InputNumber'">
          <InputNumber
            :id="id"
            v-model:value="inputNumber"
            :disabled="disabled"
            :placeholder="label"
            :size="size"
          />
        </FormRow>

        <FormRow v-slot="{ id, label }" :label="'InputPassword'">
          <InputPassword
            :id="id"
            v-model:value="inputPassword"
            :disabled="disabled"
            :placeholder="label"
            :size="size"
          />
        </FormRow>

        <FormRow v-slot="{ id, label }" :label="'InputSearch'">
          <InputSearch
            :id="id"
            v-model:value="inputSearch"
            :disabled="disabled"
            :placeholder="label"
            :size="size"
          />
        </FormRow>

        <FormRow v-slot="{ id, label }" :label="'InputTel'">
          <InputTel
            :id="id"
            v-model:value="inputTel"
            :disabled="disabled"
            :placeholder="label"
            :size="size"
          />
        </FormRow>

        <FormRow v-slot="{ id, label }" :label="'InputURL'">
          <InputUrl
            :id="id"
            v-model:value="inputUrl"
            :disabled="disabled"
            :placeholder="label"
            :size="size"
          />
        </FormRow>

        <FormRow v-slot="{ id, label }" :label="'InputCurrency'">
          <InputCurrency
            :id="id"
            v-model:value="inputCurrency"
            :disabled="disabled"
            :placeholder="label"
            currency="USD"
            :aria-label="label + ' in USD'"
            :size="size"
          />
        </FormRow>
      </Form>
    </SamplesExample>

    <SamplesPropCtl>
      <FormRow label="Disabled">
        <RadioGroup v-model:value="disabled" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
      </FormRow>
      <FormRow label="Size">
        <RadioGroup v-model:value="size" :horizontal="true">
          <Radio :value="null">Normal</Radio>
          <Radio :value="'large'">Large</Radio>
        </RadioGroup>
      </FormRow>
    </SamplesPropCtl>

    <SamplesCode>
      <template v-slot:template>{{ codeTemplate }}</template>
      <template v-slot:script>{{ codeScript }}</template>
    </SamplesCode>
  </div>
</template>

<script>
import Form from 'tui/components/form/Form';
import FormRow from 'tui/components/form/FormRow';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesCode from 'samples/components/sample_parts/misc/SamplesCode';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesPropCtl from 'samples/components/sample_parts/misc/SamplesPropCtl';

import InputCurrency from 'tui/components/form/InputCurrency';
import InputEmail from 'tui/components/form/InputEmail';
import InputNumber from 'tui/components/form/InputNumber';
import InputPassword from 'tui/components/form/InputPassword';
import InputSearch from 'tui/components/form/InputSearch';
import InputTel from 'tui/components/form/InputTel';
import InputText from 'tui/components/form/InputText';
import InputUrl from 'tui/components/form/InputUrl';

export default {
  components: {
    Form,
    FormRow,
    Radio,
    RadioGroup,
    SamplesCode,
    SamplesExample,
    SamplesPropCtl,

    InputCurrency,
    InputEmail,
    InputNumber,
    InputPassword,
    InputSearch,
    InputTel,
    InputText,
    InputUrl,
  },

  data() {
    return {
      inputCurrency: '',
      inputEmail: '',
      inputHidden: '',
      inputNumber: '',
      inputPassword: '',
      inputSearch: '',
      inputTel: '',
      inputText: '',
      inputUrl: '',
      disabled: false,
      size: null,

      codeTemplate: `<Form>
  <FormRow v-slot="{ id, label }" label="First name">
    <InputText
      :id="id"
      v-model:value="enteredText"
      disabled="false"
      :placeholder="label"
    />
  </FormRow>
</Form>`,
      codeScript: `import Form from 'tui/components/form/Form';
import FormRow from 'tui/components/form/FormRow';
import InputText from 'tui/components/form/InputText';

export default {
  components: {
    Form,
    FormRow,
    InputText
  }

  data() {
    return {
      enteredText: '',
    }
  },
}`,
    };
  },
};
</script>
