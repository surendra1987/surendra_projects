<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2021 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Brian Barnes <brian.barnes@totaralearning.com>
  @module samples
-->

<template>
  <div>
    File card

    <SamplesExample>
      <FileCard :file-size="fileSize" :filename="fileName" />
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id }" label="File size" required>
        <InputNumber v-model:value="fileSize" />
        <FormRowDetails :id="id">
          <code>fileSize: string, number</code>
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="File name" required>
        <InputText v-model:value="fileName" />
        <FormRowDetails :id="id">
          <code>fileName: string</code>
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Download url">
        <InputText v-model:value="downloadUrl" disabled />
        <FormRowDetails :id="id">
          <code>downloadUrl: string</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import InputNumber from 'tui/components/form/InputNumber';
import InputText from 'tui/components/form/InputText';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import FileCard from 'tui/components/file/FileCard';

export default {
  components: {
    FormRow,
    FormRowDetails,
    InputNumber,
    InputText,
    SamplesExample,
    SamplesCtl,
    FileCard,
  },

  data() {
    return {
      fileSize: 123546,
      fileName: 'monkey123.jpg',
      downloadable: false,
      downloadUrl: '#',
    };
  },
};
</script>
