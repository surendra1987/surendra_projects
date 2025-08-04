<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Arshad Anwer <arshad.anwer@totara.com>
  @module samples
-->

<template>
  <div class="tui-sampleTooltip">
    <SamplesExample>
      <div class="tui-tooltip__buttonRow">
        <Tooltip
          :position="config.position"
          :content="content"
          :context-mode="config.contextMode"
          :hidden="config.hidden"
        >
          <template v-slot:trigger>
            <Button text="Hover on me" />
          </template>
        </Tooltip>
      </div>
    </SamplesExample>

    <SamplesCtl>
      <FormRow
        v-slot="{ id }"
        label="Content"
        helpmsg="The text content in a tooltip"
      >
        <InputText :id="id" v-model:value="content" />
        <FormRowDetails :id="id">
          <code>content: string</code>
        </FormRowDetails>
      </FormRow>

      <FormRow
        v-slot="{ id }"
        label="Position"
        helpmsg="Specifies the position of a tooltip relative to its trigger."
      >
        <SelectFilter
          v-model:value="config.position"
          label=""
          :options="[
            { id: 'top', label: 'Top' },
            { id: 'bottom', label: 'Bottom' },
            { id: 'left', label: 'Left' },
            { id: 'right', label: 'Right' },
            { id: 'top-left', label: 'Top left' },
            { id: 'top-right', label: 'Top right' },
            { id: 'bottom-left', label: 'Bottom left' },
            { id: 'bottom-right', label: 'Bottom right' },
            { id: 'left-top', label: 'Left top' },
            { id: 'left-bottom', label: 'Left bottom' },
            { id: 'right-top', label: 'Right top' },
            { id: 'right-bottom', label: 'Right bottom' },
          ]"
        />
        <FormRowDetails :id="id">
          <code>
            position:
            "bottom"|"top"|"left"|"right"|"bottom-left"|"bottom-right"|"top-left"|"top-right"|"left-top"|"left-bottom"|"right-top"|"right-bottom"
          </code>
        </FormRowDetails>
        <FormRowDefaults>top</FormRowDefaults>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="hidden"
        helpmsg="Specifies whether to hide the tooltip."
      >
        <ToggleSwitch
          v-model:value="config.hidden"
          aria-label="hidden toggle"
          toggle-only
        />
        <FormRowDetails :id="id">
          <code>hidden: boolean</code>
        </FormRowDetails>
        <FormRowDefaults>false</FormRowDefaults>
      </FormRow>
      <FormRow
        v-slot="{ id }"
        label="contextMode"
        helpmsg="Specifies the context to inject the tooltip element. (Containde: injects after the trigger. Uncontained: injects on the root level)"
      >
        <RadioGroup v-model:value="config.contextMode">
          <Radio value="contained">Contained</Radio>
          <Radio value="uncontained">Uncontained</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          <code>contextMode: ContextType</code><br />
          <code>ContextType: 'contained'|'uncontained'</code>
        </FormRowDetails>
        <FormRowDefaults>contained</FormRowDefaults>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Slot options">
      <FormRow label="trigger" required>
        Customize the trigger of a tooltip.
        <FormRowDetails>
          <code>v-slot:trigger</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="content">
        Customize the content area in a tooltip.
        <FormRowDetails>
          <code>v-slot:content</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Event options">
      <FormRow label="open-changed">
        Triggered when a tooltip's open status changes.
        <FormRowDetails>
          <code>openChanged: (isOpen: boolean) => void</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script setup>
import Button from 'tui/components/buttons/Button';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
import InputText from 'tui/components/form/InputText';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SelectFilter from 'tui/components/filters/SelectFilter';
import Tooltip from 'tui/components/popover/Tooltip';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import { reactive, ref } from 'vue';

const content = ref('tooltip content');
const config = reactive({
  contextMode: 'contained',
  position: 'top',
  hidden: false,
});
</script>
