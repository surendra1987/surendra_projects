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

  @author Simon Chester <simon.chester@totaralearning.com>
  @module samples
-->

<template>
  <div>
    <h2>Notification Banner</h2>
    A notification banner is usually displayed at the top of the page content
    and added on page load.

    <SamplesExample>
      <NotificationBanner
        :dismissable="dismissable"
        :message="message"
        :type="type"
        :self-dismiss="selfDismiss"
        @dismiss="dismiss"
      />

      <h3>Notification banner with custom slot content</h3>
      <NotificationBanner
        :dismissable="dismissable"
        :type="type"
        :self-dismiss="selfDismiss"
        @dismiss="dismiss"
      >
        <template v-slot:body>
          <Card :no-border="true" class="tui-sampleNotificationBannerCard">
            <div>{{ message }}</div>
            <Button text="button" />
          </Card>
        </template>
      </NotificationBanner>
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id }" label="Type">
        <RadioGroup v-model:value="type" :horizontal="true">
          <Radio value="info">Info</Radio>
          <Radio value="success">Success</Radio>
          <Radio value="warning">Warning</Radio>
          <Radio value="error">Error</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          <code>type: 'info'|'success'|'warning'|'error'</code>
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id, label }" label="Message">
        <InputText :id="id" v-model:value="message" :placeholder="label" />
        <FormRowDetails :id="id">
          <code>message: string</code>
        </FormRowDetails>
      </FormRow>

      <FormRow label="Dismissable">
        <template #help-message
          >Specifies whether to show a cross button. The
          <code>dismiss</code> event is triggered when the cross button is
          clicked.</template
        >
        <RadioGroup v-model:value="dismissable" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails>
          <code>dismissable: boolean</code>
        </FormRowDetails>
      </FormRow>

      <FormRow
        v-slot="{ id }"
        label="Self-dismiss"
        helpmsg="Specifies whether to show a cross button. The notification banner is removes when the cross button is clicked"
      >
        <RadioGroup v-model:value="selfDismiss" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          <code>selfDismiss: boolean</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Slot Options">
      <FormRow label="body">
        The content area for a notification banner's body.
        <FormRowDetails>
          <code>#body</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Event Options">
      <FormRow label="dismiss">
        Triggered when <code>dismissable = true</code> and the cross button is
        clicked.
        <FormRowDetails>
          <code>dismiss: () => void</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import Card from 'tui/components/card/Card';
import NotificationBanner from 'tui/components/notifications/NotificationBanner';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import RadioGroup from 'tui/components/form/RadioGroup';
import Radio from 'tui/components/form/Radio';
import InputText from 'tui/components/form/InputText';

export default {
  components: {
    Button,
    Card,
    NotificationBanner,
    SamplesExample,
    SamplesCtl,
    FormRow,
    FormRowDetails,
    RadioGroup,
    Radio,
    InputText,
  },

  data() {
    return {
      type: 'info',
      message: 'Every time you lick a stamp, you consume 1/10 of a calorie.',
      dismissable: false,
      selfDismiss: false,
    };
  },

  methods: {
    dismiss() {
      console.log('notification dismissed');
    },
  },
};
</script>

<style lang="scss">
.tui-sampleNotificationBannerCard {
  align-items: center;
  justify-content: center;
  padding: var(--gap-4);

  & > * + * {
    margin-left: var(--gap-2);
  }
}
</style>
