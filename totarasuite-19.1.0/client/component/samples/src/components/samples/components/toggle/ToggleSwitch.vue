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

  @author Steve Barnett <steve.barnett@totaralearning.com>
  @module samples
-->

<template>
  <div>
    <h2>ToggleSwitch</h2>
    <p>
      This toggle switch is used to immediately update parts of the UI. It's an
      on/off switch for a state, and can be used as part of a form to reveal
      more related form elements, or used outside of a form to reveal non-form
      elements.
    </p>

    <p>
      On <code>click</code> we change only the button's
      (<code>aria-pressed</code>) state, not its label.
    </p>

    <SamplesExample>
      <ToggleSwitch
        id="toggle"
        v-model:value="isToggled"
        :text="text"
        :disabled="disabled"
        :toggle-first="toggleFirst"
        :aria-describedby="ariaDescribedby"
        :aria-label="ariaLabel"
        :toggle-only="toggleOnly"
      />
      <br /><br />
      <ToggleSwitch
        id="toggle"
        v-model:value="isToggled"
        :disabled="disabled"
        :text="text"
        :toggle-first="toggleFirst"
        :aria-describedby="ariaDescribedby"
      >
        <template v-slot:icon>
          <InfoIconButton :aria-label="'Help for the toggle switch'">
            Some content here...
          </InfoIconButton>
        </template>
      </ToggleSwitch>
    </SamplesExample>

    <SamplesCtl>
      <FormRow
        v-slot="{ id }"
        label="v-model:value"
        required
        helpmsg="Boolean. The two-way binding value of the switch."
      >
        <pre>{{ isToggled }}</pre>
        <FormRowDetails :id="id">
          <code>v-model:value</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="disabled"
        helpmsg="Boolean. If true, the switch is unclickable and shows as inactive."
      >
        <ToggleSwitch
          v-model:value="disabled"
          aria-label="set disabled toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>disabled</code>
        </FormRowDetails>
        <FormRowDefaults>false</FormRowDefaults>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="text"
        helpmsg="String. The label of the switch."
      >
        <InputText v-model:value="text" />
        <FormRowDetails :id="id">
          <code>text</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="toggleFirst"
        helpmsg="Boolean. Determines if the switch button is placed before (true) or after (false) the text."
      >
        <ToggleSwitch
          v-model:value="toggleFirst"
          aria-label="set toggleFirst toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>toggleFirst</code>
        </FormRowDetails>
        <FormRowDefaults>false</FormRowDefaults>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="toggleOnly"
        helpmsg="Boolean. Removes the margin between the toggle button and the text when set to true."
      >
        <ToggleSwitch
          v-model:value="toggleOnly"
          aria-label="set toggleOnly toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>toggleOnly</code>
        </FormRowDetails>
        <FormRowDefaults>false</FormRowDefaults>
      </FormRow>

      <FormRow
        v-slot="{ id }"
        label="ariaLabel"
        helpmsg="String. The value of aria-label attribute of the switch."
      >
        <InputText v-model:value="ariaLabel" />
        <FormRowDetails :id="id">
          <code>ariaLabel</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="ariaDescribedby"
        helpmsg="String. The value of aria-describedby attribute of the switch."
      >
        <InputText v-model:value="ariaDescribedby" />
        <FormRowDetails :id="id">
          <code>ariaDescribedby</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Slot options">
      <FormRow label="icon">
        The inline content area after text, designed for placing an icon or
        icon-only button.
        <FormRowDetails>
          <code>icon</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Event options">
      <FormRow label="input">
        Triggered when the switch button is clicked.
        <FormRowDetails>
          <code>input</code><br />
          <code>(value: boolean) => void</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="blur">
        Triggered when the switch button loses focus.
        <FormRowDetails>
          <code>blur</code><br />
          <code>(event: FocusEvent) => void</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script setup>
import { ref } from 'vue';
// Components
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';
import InfoIconButton from 'tui/components/buttons/InfoIconButton';
import InputText from 'tui/components/form/InputText';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';

const text = ref('Toggle something in the UI');
const toggleFirst = ref(false);
const toggleOnly = ref(false);
const ariaLabel = ref('The toggle button text');
const isToggled = ref(false);
const disabled = ref(false);
const ariaDescribedby = ref('');
</script>
