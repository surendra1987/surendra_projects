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
  <SamplesExample>
    <Avatar :src="src" :alt="alt" :size="size" />
  </SamplesExample>

  <SamplesCtl>
    <!-- alt -->
    <FormRow v-slot="{ id, label }" label="Alt tag" required>
      <InputText :id="id" v-model:value="alt" :placeholder="label" />
      <FormRowDetails :id="id">
        alt
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Size">
      <RadioGroup v-model:value="size" :horizontal="true">
        <Radio value="large">Large</Radio>
        <Radio value="medium">Medium</Radio>
        <Radio value="small">Small</Radio>
        <Radio value="xsmall">Extra small</Radio>
        <Radio value="xxsmall">Extra extra small</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        size
      </FormRowDetails>
    </FormRow>

    <!-- src -->
    <FormRow v-slot="{ id, label }" label="Image source" required>
      <InputText :id="id" v-model:value="src" :placeholder="label" />
      <FormRowDetails :id="id">
        src
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>

<script>
import Avatar from 'tui/components/avatar/Avatar';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import InputText from 'tui/components/form/InputText';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';

import { createSilhouetteImage } from 'samples/components/sample_parts/misc/placeholder_generator';

export default {
  components: {
    Avatar,
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
      alt: 'alt',
      size: 'medium',
      src: createSilhouetteImage('#00f'),
    };
  },
};
</script>
