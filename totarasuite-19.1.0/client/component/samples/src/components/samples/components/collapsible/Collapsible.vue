<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

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
    <h2>Collapsible</h2>
    collapsible component for expanding or collapsing content

    <SamplesExample>
      <Collapsible v-bind="config" v-model:value="isOpen">
        {{ content }}
        <template v-slot:collapsible-side-content>
          <More />
        </template>
      </Collapsible>
    </SamplesExample>

    <SamplesCtl>
      <FormRow
        v-slot="{ id }"
        label="Label"
        required
        helpmsg="A collapsible block's title."
      >
        <InputText :id="id" v-model:value="config.label" />
        <FormRowDetails :id="id">
          <code>label: string</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="v-model:value"
        helpmsg="The two-way binding value of a collapsible's open/close state."
      >
        <ToggleSwitch
          v-model:value="isOpen"
          aria-label="set isOpen toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>v-model:value</code><br />
          <code>boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="id" helpmsg="A collapsible block's id.">
        <InputText :id="id" v-model:value="config.id" />
        <FormRowDetails :id="id">
          <code>id: string|number</code>
        </FormRowDetails>
        <FormRowDefaults>generatedId()</FormRowDefaults>
      </FormRow>
      <FormRow
        label="Initial State"
        helpmsg="Specifies a collapsible block's initial state to be open or not."
      >
        <ToggleSwitch
          v-model:value="config.initialState"
          aria-label="set initialState toggle"
          toggle-only
        />
        <FormRowDetails>
          <code>initialState: boolean</code>
        </FormRowDetails>
        <FormRowDefaults>false</FormRowDefaults>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="Indent contents"
        helpmsg="Specifies whether to indent the content."
      >
        <ToggleSwitch
          v-model:value="config.indentContents"
          aria-label="set indentContents toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>indentContents: boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="Variant"
        helpmsg="Specifies a collapsible's style."
      >
        <RadioGroup v-model:value="config.variant" :horizontal="true">
          <Radio :value="null">Default</Radio>
          <Radio :value="'minimal'">Minimal</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          <code>variant: undefined|'minimal'</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="Padding"
        helpmsg="Specifies the padding size of a collapsible's heading block."
      >
        <RadioGroup v-model:value="config.padding" :horizontal="true">
          <Radio :value="null">Default</Radio>
          <Radio :value="'large'">Large</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          <code>variant: undefined|'large'</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        label="Exclude Header Padding"
        helpmsg="Remove a collapsible's header padding."
      >
        <ToggleSwitch
          v-model:value="config.excludeHeaderPadding"
          aria-label="set excludeHeaderPadding toggle"
          toggle-only
        />
        <FormRowDetails>
          <code>excludeHeaderPadding: boolean</code>
        </FormRowDetails>
        <FormRowDefaults>false</FormRowDefaults>
      </FormRow>
      <FormRow
        label="Always Render"
        helpmsg="Specifies whether the collapsible content element is rendered when hidden."
      >
        <ToggleSwitch
          v-model:value="config.alwaysRender"
          aria-label="set alwaysRender toggle"
          toggle-only
        />
        <FormRowDetails>
          <code>alwaysRender: boolean</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Slot options">
      <FormRow label="Default">
        The main content area of a collapsible block.
        <FormRowDetails>
          <code>default</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="Label Extra">
        The inline area after a collapsible's heading text.
        <FormRowDetails>
          <code>label-extra</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="Collapsible Side Content">
        The content area at the end of a collapsible's heading for extra
        information or icon.
        <FormRowDetails>
          <code>collapsible-side-content</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Event options">
      <FormRow label="input">
        Triggered when a collapsible's open/close state changes.
        <FormRowDetails>
          <code>input: (value: boolean) => void</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script setup>
import Collapsible from 'tui/components/collapsible/Collapsible';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import InputText from 'tui/components/form/InputText';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import More from 'tui/components/buttons/MoreIcon';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
import { reactive, ref } from 'vue';

const config = reactive({
  expanded: false,
  label: 'Heading',
  initialState: true,
  indentContents: false,
  variant: null,
  padding: null,
  alwaysRender: false,
});

const isOpen = ref(false);
const content = ref('...');
</script>
