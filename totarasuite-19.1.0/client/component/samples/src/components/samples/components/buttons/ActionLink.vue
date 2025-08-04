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
  <div class="tui-testActionLink">
    The action link component is used when styling a link to suggest additional
    actions other than just navigating will be triggered by clicking this.

    <SamplesExample>
      <ActionLink
        :href="href"
        :text="text"
        :disabled="disabled"
        :styleclass="{
          primary: primaryStyle,
          small: smallStyle,
        }"
      />

      <h3>Form</h3>
      <Form :class="'tui-testForm'">
        <FormRow label="Link">
          <ActionLink
            :href="href"
            :text="text"
            :disabled="disabled"
            :styleclass="{
              primary: primaryStyle,
              small: smallStyle,
            }"
          />
        </FormRow>
      </Form>
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id, label }" label="Text" required>
        <InputText
          :id="id"
          v-model:value="text"
          :disabled="disabled"
          :placeholder="label"
        />
        <FormRowDetails :id="id">
          text
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id, label }" label="Link" required>
        <InputText
          :id="id"
          v-model:value="href"
          disabled
          :placeholder="label"
        />
        <FormRowDetails :id="id">
          href
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Primary">
        <RadioGroup v-model:value="primaryStyle" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          styleclass.primary
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Small">
        <RadioGroup v-model:value="smallStyle" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          styleclass.small
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
// Components
import ActionLink from 'tui/components/links/ActionLink';
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
    FormRowDetails,
    ActionLink,
    Form,
    FormRow,
    InputText,
    Radio,
    RadioGroup,
    SamplesExample,
    SamplesCtl,
  },

  data() {
    return {
      disabled: false,
      primaryStyle: false,
      smallStyle: false,
      text: 'Action Link',
      href: '#',
    };
  },
};
</script>

<style lang="scss">
.tui-testActionLink {
  h3 {
    margin-top: var(--gap-6);
  }
}
</style>
