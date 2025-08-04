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
        <Tooltip :position="position">
          <template v-slot:trigger>
            <Button text="Hover on me" />
          </template>
          <template v-slot:content>
            {{ content }}
          </template>
        </Tooltip>
      </div>
    </SamplesExample>

    <SamplesPropCtl>
      <FormRow v-slot="{ id, label }" label="Content">
        <InputText :id="id" v-model:value="content" :placeholder="label" />
      </FormRow>

      <FormRow label="Position">
        <RadioGroup v-model:value="position">
          <Radio value="bottom">Bottom</Radio>
          <Radio value="top">Top</Radio>
          <Radio value="left">Left</Radio>
          <Radio value="right">Right</Radio>
          <Radio value="bottom-left">Bottom left</Radio>
          <Radio value="bottom-right">Bottom right</Radio>
          <Radio value="top-left">Top left</Radio>
          <Radio value="top-right">Top right</Radio>
          <Radio value="left-top">Left top</Radio>
          <Radio value="left-bottom">Left bottom</Radio>
          <Radio value="right-top">Right top</Radio>
          <Radio value="right-bottom">Right bottom</Radio>
        </RadioGroup>
      </FormRow>
    </SamplesPropCtl>

    <SamplesCode>
      <template v-slot:template>{{
        sampleCode && sampleCode.template
      }}</template>
      <template v-slot:script>{{ sampleCode && sampleCode.script }}</template>
    </SamplesCode>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import FormRow from 'tui/components/form/FormRow';
import InputText from 'tui/components/form/InputText';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import Tooltip from 'tui/components/popover/Tooltip';
import SamplesCode from 'samples/components/sample_parts/misc/SamplesCode';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesPropCtl from 'samples/components/sample_parts/misc/SamplesPropCtl';

export default {
  components: {
    Button,
    FormRow,
    InputText,
    Radio,
    RadioGroup,
    Tooltip,
    SamplesCode,
    SamplesExample,
    SamplesPropCtl,
  },

  data() {
    return {
      content: 'tooltip content',
      position: 'top',
    };
  },
};
</script>

<sample-template>
<Tooltip
    :position="'bottom'"
    :content="'Tooltip content'"
  >
  <template v-slot:trigger>
    <Button text="Hover on me" />
  </template>
</Tooltip>
</sample-template>

<sample-script>
import Button from 'tui/components/buttons/Button';
import Tooltip from 'tui/components/popover/Tooltip';

export default {
  components: {
    Button,
    Tooltip,
  },
}
</sample-script>
