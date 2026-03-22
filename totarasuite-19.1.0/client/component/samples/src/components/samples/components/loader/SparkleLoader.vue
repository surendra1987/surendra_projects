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

  @author Anthony van Beek <anthony@pixelfusion.co.nz>
  @module samples
-->

<template>
  <div>
    <SamplesExample>
      <SparkleLoader :title="title" :subtitle="subtitle" />
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id }" label="Title">
        <InputText v-model:value="title" :name="id" />
      </FormRow>
      <FormRow v-slot="{ id }" label="Subtitle">
        <InputText v-model:value="subtitle" :name="id" />
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import SparkleLoader from 'tui/components/loading/SparkleLoader';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import FormRow from 'tui/components/form/FormRow';
import InputText from 'tui/components/form/InputText';

export default {
  components: {
    SparkleLoader,
    SamplesExample,
    SamplesCtl,
    FormRow,
    InputText,
  },

  data() {
    return {
      title: 'Processing your request...',
      subtitle: 'This may take a few moments.',
    };
  },
};
</script>
