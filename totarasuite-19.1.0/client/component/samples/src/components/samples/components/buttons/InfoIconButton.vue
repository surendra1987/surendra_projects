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
      <InfoIconButton
        :is-help-for="'example control name'"
        :disabled="disabled"
        :size="size"
      >
        Some content here...
      </InfoIconButton>
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id }" label="Disabled">
        <RadioGroup v-model:value="disabled" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          disabled
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Set width">
        <RadioGroup v-model:value="size" :horizontal="true">
          <Radio :value="null">None</Radio>
          <Radio value="sm">Small</Radio>
          <Radio value="md">Medium</Radio>
          <Radio value="lg">Large</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          size
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
// Components
import InfoIconButton from 'tui/components/buttons/InfoIconButton';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';

export default {
  components: {
    InfoIconButton,
    FormRow,
    FormRowDetails,
    Radio,
    RadioGroup,
    SamplesExample,
    SamplesCtl,
  },

  data() {
    return {
      disabled: false,
      size: null,
    };
  },
};
</script>
