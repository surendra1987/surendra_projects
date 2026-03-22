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
  <div class="tui-loader">
    ...

    <SamplesExample>
      <h3>Wrapping Content</h3>
      <Loader :loading="loading" :fullpage="fullpage">
        <h1>content</h1>
        ....
      </Loader>

      <h3>Without Content</h3>
      <Loader :loading="loading" />
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id }" label="Loading">
        <RadioGroup v-model:value="loading" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          loading
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Full page">
        <RadioGroup v-model:value="fullpage" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          fullpage
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import Loader from 'tui/components/loading/Loader';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';

export default {
  components: {
    Loader,
    FormRow,
    FormRowDetails,
    Radio,
    RadioGroup,
    SamplesExample,
    SamplesCtl,
  },

  data() {
    return {
      fullpage: false,
      loading: true,
    };
  },
};
</script>
