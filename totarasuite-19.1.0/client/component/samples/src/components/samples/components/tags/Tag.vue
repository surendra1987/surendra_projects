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
  <div>
    <SamplesExample>
      <Tag
        :bold="bold"
        :label="label"
        :large="large"
        :no-border="noBorder"
        :text="text1"
      >
        <template v-slot:button>
          <ButtonIcon
            :styleclass="{
              transparent: true,
              primary: true,
              small: true,
            }"
            aria-label="tag-remove"
            @click="$emit('remove')"
          >
            <Close size="100" />
          </ButtonIcon>
        </template>
      </Tag>

      <Tag
        :bold="bold"
        :label="label"
        :large="large"
        :no-border="noBorder"
        :text="text2"
      />
      <Tag
        :bold="bold"
        :label="label"
        :large="large"
        :no-border="noBorder"
        :primary="true"
        :text="text2"
      />
      <Tag
        :bold="bold"
        :label="label"
        :large="large"
        :no-border="noBorder"
        :text="text3"
        href="https://www.totaralearning.com/"
      />
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id }" label="Large">
        <RadioGroup :id="id" v-model:value="large" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          large
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Bold">
        <RadioGroup :id="id" v-model:value="bold" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          bold
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="No border">
        <RadioGroup :id="id" v-model:value="noBorder" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          noBorder
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Label">
        <InputText :id="id" v-model:value="label" :placeholder="'Label'" />
        <FormRowDetails :id="id">
          label
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import Close from 'tui/components/icons/Close';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import InputText from 'tui/components/form/InputText';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import Tag from 'tui/components/tag/Tag';

export default {
  components: {
    ButtonIcon,
    Close,
    FormRow,
    FormRowDetails,
    InputText,
    Radio,
    RadioGroup,
    SamplesExample,
    SamplesCtl,
    Tag,
  },

  emits: ['remove'],

  data() {
    return {
      bold: false,
      label: '',
      large: false,
      noBorder: false,
      text1: 'Wellington',
      text2: 'Lorem Ipsum',
      text3: 'Totara',
    };
  },
};
</script>
