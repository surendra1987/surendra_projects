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

  @author Jaron Steenson <jaron.steenson@totaralearning.com>
  @module samples
-->

<template>
  <div>
    A series of simple notepad style lines for printing unfilled text inputs and
    areas.

    <SamplesExample>
      <NotepadLines :lines="Number(lines)" :char-length="charLength" />
    </SamplesExample>

    <SamplesCtl>
      <h4 class="tui-samplesCtl__optional">Optional</h4>

      <FormRow v-slot="{ id }" label="Lines">
        <InputNumber :id="id" v-model:value="lines" :min="0" />
        <FormRowDetails :id="id">
          lines
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Char length">
        <Select :id="id" v-model:value="charLength" :options="options" />
        <FormRowDetails :id="id">
          charLength
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import { charLengthScale } from 'tui/components/form/form_common';
import Select from 'tui/components/form/Select';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import InputNumber from 'tui/components/form/InputNumber';
import NotepadLines from 'tui/components/form/NotepadLines';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';

export default {
  components: {
    Select,
    FormRow,
    FormRowDetails,
    InputNumber,
    NotepadLines,
    SamplesExample,
    SamplesCtl,
  },

  data() {
    return {
      charLength: 20,
      lines: 6,
    };
  },

  computed: {
    options() {
      return charLengthScale
        ? charLengthScale.map(length => {
            return {
              id: parseInt(length),
              label: length,
            };
          })
        : [];
    },
  },
};
</script>
