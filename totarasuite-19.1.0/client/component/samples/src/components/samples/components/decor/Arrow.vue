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
  <div class="tui-testArrow">
    The arrow component is a visual component used with overlay or expanded
    content.

    <SamplesExample>
      <div class="tui-testArrow--box">
        <Arrow :size="size" :side="side" :distance="distance" />
      </div>
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id }" label="Arrow side">
        <RadioGroup v-model:value="side" :horizontal="true">
          <Radio value="top">Top</Radio>
          <Radio value="right">Right</Radio>
          <Radio value="bottom">Bottom</Radio>
          <Radio value="left">Left</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          side
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Arrow size">
        <RadioGroup v-model:value="size" :horizontal="true">
          <Radio value="normal">Normal</Radio>
          <Radio value="large">Large</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          size
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Distance (offset)">
        <RadioGroup v-model:value="distance" :horizontal="true">
          <Radio value="16">16</Radio>
          <Radio value="32">32</Radio>
          <Radio value="64">64</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          distance
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import Arrow from 'tui/components/decor/Arrow';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';

export default {
  components: {
    Arrow,
    FormRow,
    FormRowDetails,
    Radio,
    RadioGroup,
    SamplesExample,
    SamplesCtl,
  },

  data() {
    return {
      distance: '32',
      side: 'top',
      size: 'normal',
    };
  },
};
</script>

<style lang="scss">
.tui-testArrow {
  &--box {
    position: relative;
    width: 50%;
    height: 100px;
    background: var(--color-neutral-1);
    border: 1px solid var(--color-neutral-5);
  }
}
</style>
