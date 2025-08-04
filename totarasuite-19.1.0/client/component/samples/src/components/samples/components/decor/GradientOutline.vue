<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

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
      <GradientOutline
        :border-radius="borderRadius"
        :content-padding="contentPadding"
        :width="width"
      >
        <div v-html="slotContent" />
      </GradientOutline>
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id }" label="Border Radius">
        <InputText
          v-model:value="borderRadius"
          :name="id"
          placeholder="e.g., 8px, 1em, 50%"
        />
      </FormRow>
      <FormRow v-slot="{ id }" label="Content Padding">
        <InputText
          v-model:value="contentPadding"
          :name="id"
          placeholder="e.g., 16px, 2em"
        />
      </FormRow>
      <FormRow v-slot="{ id }" label="Width">
        <InputText
          v-model:value="width"
          :name="id"
          placeholder="e.g., 100%, 300px"
        />
      </FormRow>
      <FormRow v-slot="{ id }" label="Slot Content (HTML allowed)">
        <Textarea v-model:value="slotContent" :name="id" :rows="4" />
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import GradientOutline from 'tui/components/decor/GradientOutline';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import FormRow from 'tui/components/form/FormRow';
import InputText from 'tui/components/form/InputText';
import Textarea from 'tui/components/form/Textarea';

export default {
  components: {
    GradientOutline,
    SamplesExample,
    SamplesCtl,
    FormRow,
    InputText,
    Textarea,
  },

  data() {
    return {
      borderRadius: '8px',
      contentPadding: '16px',
      width: '100%',
      slotContent:
        '<p>This is some <em>editable</em> content.</p><p>Try changing it below!</p>',
    };
  },
};
</script>
