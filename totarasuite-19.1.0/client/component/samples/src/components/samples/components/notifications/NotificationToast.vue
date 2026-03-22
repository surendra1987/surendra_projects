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
  <div>
    <h2>Notification Toast</h2>
    A toast notification used for providing the user instant feedback on the
    result of an action or query. To use, import the
    <code>tui/notifications</code> and use the <code>notify</code> method.

    <SamplesExample>
      <Button text="Trigger Notification" @click="triggerNotification" />
      <Button text="Redirect + Trigger" @click="redirect" />
    </SamplesExample>

    <SamplesCtl>
      <FormRow label="Type">
        <RadioGroup v-model:value="type" :horizontal="true">
          <Radio value="success">Success</Radio>
          <Radio value="error">Error</Radio>
        </RadioGroup>
        <FormRowDetails>
          <code>type: 'success' | 'error'</code>
        </FormRowDetails>
        <FormRowDefaults>success</FormRowDefaults>
      </FormRow>

      <FormRow
        label="Duration"
        helpmsg="Specifies how many milliseconds a notification lasts."
      >
        <InputNumber v-model:value.number="duration" />
        <FormRowDetails>
          <code>duration: number</code>
        </FormRowDetails>
        <FormRowDefaults>5000</FormRowDefaults>
      </FormRow>

      <FormRow v-slot="{ id, label }" label="Message">
        <InputText :id="id" v-model:value="message" :placeholder="label" />
        <FormRowDetails>
          <code>message: string</code>
        </FormRowDetails>
        <FormRowDefaults>...</FormRowDefaults>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import { notify, notifyParams } from 'tui/notifications';
import Button from 'tui/components/buttons/Button';
import FormRow from 'tui/components/form/FormRow';
import InputText from 'tui/components/form/InputText';
import InputNumber from 'tui/components/form/InputNumber';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';

export default {
  components: {
    Button,
    FormRow,
    InputText,
    Radio,
    RadioGroup,
    SamplesExample,
    SamplesCtl,
    InputNumber,
    FormRowDetails,
    FormRowDefaults,
  },

  data() {
    return {
      type: 'success',
      duration: 5000,
      message: 'Success',
    };
  },

  methods: {
    triggerNotification() {
      notify({
        duration: this.duration,
        message: this.message,
        type: this.type,
      });
    },

    redirect() {
      window.location = this.$url('/totara/dashboard/index.php', {
        ...notifyParams({
          duration: this.duration,
          message: this.message,
          type: this.type,
        }),
      });
    },
  },
};
</script>
