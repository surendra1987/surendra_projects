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
    <h2>ToggleSet</h2>
    The ToggleSet component is used for switching between multiple states. It
    includes multiple ToggleButton as the options.

    <SamplesExample>
      <ToggleSet
        v-model:value="selectedVal"
        :disabled="configSet.disabled"
        :aria-label="configSet.ariaLabel"
        :large="configSet.large"
        @input="toggleChange"
      >
        <ToggleButton value="1" text="left" aria-label="left">
          <GridIcon />
        </ToggleButton>
        <ToggleButton value="2" text="middle" aria-label="middle">
          <ListIcon />
        </ToggleButton>
        <ToggleButton value="3" text="right" aria-label="right">
          <SliderIcon />
        </ToggleButton>
      </ToggleSet>
    </SamplesExample>

    <SamplesCtl>
      <FormRow
        v-slot="{ id }"
        label="v-model:value"
        required
        helpmsg="Boolean, String. The two-way binding value of the toggle set."
      >
        <InputText v-model:value="selectedVal" />

        <FormRowDetails :id="id">
          <code>v-model:value</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="disabled"
        helpmsg="Boolean. Specifies that a toggle set should be disabled"
      >
        <ToggleSwitch
          v-model:value="configSet.disabled"
          aria-label="set disabled toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>disabled: boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="large"
        helpmsg="Specifies the size of a toggle set."
      >
        <ToggleSwitch
          v-model:value="configSet.large"
          aria-label="set large toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>large: boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="ariaLabel"
        required
        helpmsg="Specifies the aria-label of a toggle set."
      >
        <InputText v-model:value="configSet.ariaLabel" />
        <FormRowDetails :id="id">
          <code>ariaLabel: string</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Event options">
      <FormRow label="input">
        Triggered when the selected option changed.
        <FormRowDetails>
          <code>input: (value: string|boolean) => void</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>

    <h2>ToggleButton</h2>
    The ToggleButton component is used for the options inside a ToggleSet.
    <SamplesExample>
      <ToggleSet
        v-model:value="selectedVal"
        :disabled="configSet.disabled"
        :aria-label="configSet.ariaLabel"
        :large="configSet.large"
        @input="toggleChange"
      >
        <ToggleButton
          :disabled="configBtn.disabled"
          :value="configBtn.value"
          :text="configBtn.text"
          :aria-label="configBtn.ariaLabel"
        >
          <GridIcon />
        </ToggleButton>
      </ToggleSet>
    </SamplesExample>
    <SamplesCtl>
      <FormRow
        v-slot="{ id }"
        label="value"
        required
        helpmsg="Specifies the value to be binded."
      >
        <InputText v-model:value="configBtn.value" />
        <FormRowDetails :id="id">
          <code>value: Boolean|String</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="ariaLabel"
        required
        helpmsg="Specifies the aria-label of a toggle button."
      >
        <InputText v-model:value="configBtn.ariaLabel" />
        <FormRowDetails :id="id">
          <code>ariaLabel: string</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="text"
        helpmsg="Specifies the text of a toggle button."
      >
        <InputText v-model:value="configBtn.text" />
        <FormRowDetails :id="id">
          <code>text: string</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="disabled"
        helpmsg="Specifies that a toggle button should be disabled."
      >
        <ToggleSwitch
          v-model:value="configBtn.disabled"
          aria-label="button disabled toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>disabled: boolean</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Slot Options">
      <FormRow label="default">
        The inline content area before text, designed for placing an icon.
        <FormRowDetails>
          <code>default</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Event options">
      <FormRow label="clicked">
        Triggered when the toggle button is clicked.
        <FormRowDetails>
          <code>clicked: (value: string|boolean) => void</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script setup>
// Components
import ListIcon from 'tui/components/icons/List';
import GridIcon from 'tui/components/icons/Grid';
import SliderIcon from 'tui/components/icons/Slider';
import ToggleSet from 'tui/components/toggle/ToggleSet';
import ToggleButton from 'tui/components/toggle/ToggleButton';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import InputText from 'tui/components/form/InputText';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
import { reactive, ref } from 'vue';

const selectedVal = ref('1');
const configSet = reactive({
  disabled: false,
  ariaLabel: '',
  large: true,
});

const configBtn = reactive({
  value: '1',
  ariaLabel: 'left',
  text: '',
  disabled: false,
});

/**
 * Toggle state change
 *
 */
const toggleChange = value => {
  console.log('toggle state changed to ' + value);
};
</script>
