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
  <div class="tui-testButton">
    The button icon component is used when triggering an on page action.

    <SamplesExample>
      <ButtonIcon
        :aria-label="'Add'"
        :disabled="disabled"
        :caret="caret"
        :loading="loading"
        :styleclass="{
          alert: alertStyle,
          circle: circleStyle,
          primary: primaryStyle,
          small: smallStyle,
          textFirst: textFirstStyle,
          transparent: transparentStyle,
          stealth: stealthStyle,
          toolbar: toolbarStyle,
          selected: selectedStyle,
          xsmall: xsmallStyle,
          transparentNoPadding: paddingStyle,
        }"
        :text="text"
      >
        <AddIcon />
      </ButtonIcon>

      <h3>Form</h3>
      <Form>
        <FormRow label="Button">
          <ButtonIcon
            :aria-label="'Add'"
            :disabled="disabled"
            :caret="caret"
            :loading="loading"
            :styleclass="{
              alert: alertStyle,
              circle: circleStyle,
              primary: primaryStyle,
              small: smallStyle,
              textFirst: textFirstStyle,
              transparent: transparentStyle,
              stealth: stealthStyle,
              toolbar: toolbarStyle,
              selected: selectedStyle,
              xsmall: xsmallStyle,
              transparentNoPadding: paddingStyle,
            }"
            :text="text"
          >
            <AddIcon />
          </ButtonIcon>
        </FormRow>
      </Form>
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id, label }" label="Text">
        <InputText :id="id" v-model:value="text" :placeholder="label" />
        <FormRowDetails :id="id">
          text
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Primary">
        <RadioGroup v-model:value="primaryStyle" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          primary
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Alert">
        <RadioGroup v-model:value="alertStyle" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          alert
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Small">
        <RadioGroup v-model:value="smallStyle" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          small
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Extra small">
        <RadioGroup v-model:value="xsmallStyle" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          xsmall
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

      <FormRow v-slot="{ id }" label="Circle">
        <RadioGroup v-model:value="circleStyle" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          circle
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Transparent">
        <RadioGroup v-model:value="transparentStyle" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          transparent
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Stealth">
        <RadioGroup v-model:value="stealthStyle" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          stealth
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Caret (for dropdown)">
        <RadioGroup v-model:value="caret" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          caret
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Loading">
        <RadioGroup v-model:value="loading" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          loading
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Text first">
        <RadioGroup v-model:value="textFirstStyle" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          textFirst
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Toolbar">
        <RadioGroup v-model:value="toolbarStyle" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          toolbar
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Selected (for toolbar)">
        <RadioGroup v-model:value="selectedStyle" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          selected
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Transparent No padding">
        <RadioGroup v-model:value="paddingStyle" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          transparentNoPadding
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
// Components
import AddIcon from 'tui/components/icons/Add';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
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
    AddIcon,
    ButtonIcon,
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
      circleStyle: false,
      disabled: false,
      inputButtonStyle: false,
      alertStyle: false,
      primaryStyle: false,
      textFirstStyle: false,
      smallStyle: false,
      xsmallStyle: false,
      text: '',
      transparentStyle: false,
      paddingStyle: false,
      stealthStyle: false,
      toolbarStyle: false,
      selectedStyle: false,
      caret: false,
      loading: false,
    };
  },
};
</script>
