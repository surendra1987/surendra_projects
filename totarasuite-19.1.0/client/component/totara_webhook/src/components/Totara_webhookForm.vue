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

  @author ben fesili <ben.fesili@totara.com>
  @module totara_webhook
-->

<script setup>
import FormRowActionButtons from 'tui/components/form/FormRowActionButtons';
import {} from 'totara_webhook/constants';

import {
  FormRow,
  FormField,
  FormText,
  Uniform,
  FormRadioGroup,
  FormCheckbox,
} from 'tui/components/uniform';
import FormTagList from 'tui/components/uniform/FormTagList';
import InputGroup from 'tui/components/form/InputGroup';
import InputGroupInput from 'tui/components/form/InputGroupInput';
import InputGroupLabel from 'tui/components/form/InputGroupLabel';
import Radio from 'tui/components/form/Radio';

defineProps({
  initialValues: Object,
  availableEvents: Array,
  submitting: Boolean,
});

defineEmits(['submit', 'cancel']);
</script>

<script>
export default {
  data() {
    return {
      eventSearchItem: '',
      status: this.initialValues.status,
      statusLabel: this.initialValues.status
        ? this.$str('totara_webhook_status_enabled', 'totara_webhook')
        : this.$str('totara_webhook_status_disabled', 'totara_webhook'),
      selectedEvents: this.initialValues.events,
    };
  },

  computed: {
    events_list() {
      const selectedEvents = this.selectedEvents;
      const events = this.availableEvents.filter(
        event => !selectedEvents.some(tag => tag.class === event.class)
      );
      if (this.eventSearchItem === '') {
        return [...events];
      }
      return events.filter(event =>
        event.name.toUpperCase().includes(this.eventSearchItem.toUpperCase())
      );
    },
  },

  methods: {
    eventFilter(value) {
      this.eventSearchItem = value;
    },
    handleChange(value) {
      this.selectedEvents = value.events;
    },
    validateEndpoint() {
      const regex = new RegExp('http(s)*://', 'gi');
      return {
        validate: value => value.replace(regex, '').trim() !== '',
        message: () => this.$str('invalid_endpoint', 'totara_webhook'),
      };
    },
    updateStatusLabel() {
      this.status = !this.status;
      this.statusLabel = this.status
        ? this.$str('totara_webhook_status_enabled', 'totara_webhook')
        : this.$str('totara_webhook_status_disabled', 'totara_webhook');
    },
  },
};
</script>

<template>
  <Uniform
    :initial-values="initialValues"
    @change="handleChange"
    @submit="$emit('submit', $event)"
  >
    <FormRow :label="$str('name', 'totara_webhook')" required>
      <FormText name="name" :validations="v => [v.required()]" />
    </FormRow>
    <FormRow :label="$str('endpoint', 'totara_webhook')" required>
      <FormField
        v-slot="{ attrs, value, update, blur }"
        name="endpoint"
        :validations="v => [v.required(), validateEndpoint()]"
      >
        <InputGroup name="endpoint">
          <InputGroupLabel :text="'https://'" />
          <InputGroupInput
            v-bind="attrs"
            :value="value"
            @input="update"
            @blur="blur"
          />
        </InputGroup>
      </FormField>
    </FormRow>
    <FormRow :label="$str('events', 'totara_webhook')">
      <FormTagList
        name="events"
        :items="events_list"
        :filter="eventSearchItem"
        @filter="eventFilter"
      >
        <template v-slot:tag="{ tag }">
          <div class="tui-customTag">
            {{ tag.name }}
          </div>
        </template>
        <template v-slot:item="{ item }">
          <div>
            {{ item.name }}
          </div>
        </template>
      </FormTagList>
    </FormRow>
    <FormRow :label="$str('totara_webhook_status', 'totara_webhook')">
      <FormCheckbox name="status" @change="updateStatusLabel">
        {{ statusLabel }}
      </FormCheckbox>
    </FormRow>
    <FormRow :label="$str('webhook_dispatch_timing', 'totara_webhook')">
      <FormRadioGroup name="immediate">
        <Radio key="is_scheduled" :value="false">
          {{ $str('webhook_scheduled', 'totara_webhook') }}
        </Radio>
        <Radio key="is_immediate" :value="true">
          {{ $str('webhook_immediate', 'totara_webhook') }}
        </Radio>
      </FormRadioGroup>
    </FormRow>
    <FormRowActionButtons :submitting="submitting" @cancel="$emit('cancel')" />
  </Uniform>
</template>

<style lang="scss">
.tui-tagList__tagItem {
  max-width: 100%;
}
.tui-customTag {
  padding: rem-px(4);
  border: 1px solid var(--btn-text-color);
  border-radius: 6px;
}
</style>
