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
    <h2>ConfirmationModal</h2>
    A confirmation modal, to be applied when the user has triggered an action
    which is unchangeable or highly visible. <br />
    It is an extention of <code>Modal</code> related components.

    <SamplesExample>
      <Button text="Show confirmation modal" @click="showModal" />

      <ConfirmationModal
        :open="modalOpen"
        :title="title"
        :confirm-button-text="customConfirmText"
        :confirm-disabled="confirmDisabled"
        :close-button="closeButton"
        :loading="loading"
        :size="modalSize"
        @confirm="modalConfirmed"
        @cancel="modalCancelled"
      >
        {{ message }}
      </ConfirmationModal>
    </SamplesExample>

    <SamplesCtl>
      <FormRow label="open" helpmsg="Controls the modal's open/close state.">
        <ToggleSwitch
          v-model:value="modalOpen"
          aria-label="set open toggle"
          toggle-only
        />
        <FormRowDetails>
          <code>open: boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        label="loading"
        helpmsg="Specifies whether a modal is at loading state and can't be closed."
      >
        <ToggleSwitch
          v-model:value="loading"
          aria-label="set loading toggle"
          toggle-only
        />
        <FormRowDetails>
          <code>loading: boolean</code>
        </FormRowDetails>
      </FormRow>

      <FormRow
        label="confirmButtonText"
        helpmsg="Defines the confirm button text."
      >
        <InputText v-model:value="customConfirmText" />

        <FormRowDetails>
          <code>confirmButtonText: string</code>
        </FormRowDetails>
        <FormRowDefaults>getString('ok', 'core')</FormRowDefaults>
      </FormRow>
      <FormRow
        label="confirmDisabled"
        helpmsg="Specifies whether to disable a confirm button."
      >
        <ToggleSwitch
          v-model:value="confirmDisabled"
          aria-label="set confirmDisabled toggle"
          toggle-only
        />
        <FormRowDetails>
          <code>confirmDisabled: boolean</code>
        </FormRowDetails>
        <FormRowDefaults>false</FormRowDefaults>
      </FormRow>
      <FormRow
        label="closeButton"
        helpmsg="Specifies whether a modal has a close button."
      >
        <ToggleSwitch
          v-model:value="closeButton"
          aria-label="set closeButton toggle"
          toggle-only
        />
        <FormRowDetails>
          <code>closeButton: boolean</code>
        </FormRowDetails>
        <FormRowDefaults>true</FormRowDefaults>
      </FormRow>
      <FormRow v-slot="{ id }" label="size">
        <RadioGroup v-model:value="modalSize" :horizontal="true">
          <Radio value="small">small</Radio>
          <Radio value="normal">normal</Radio>
          <Radio value="large">large</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          <code>size: ModalSize</code>
        </FormRowDetails>
        <FormRowDefaults>normal</FormRowDefaults>
      </FormRow>
      <FormRow label="title" helpmsg="Defines the title of a confiem modal.">
        <InputText v-model:value="title" />

        <FormRowDetails>
          <code>title: string</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Slot options">
      <FormRow label="default">
        The content area for a confirmation modal.
        <FormRowDetails>
          <code>default</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Event options">
      <FormRow label="cancel">
        Triggered when a dismiss behaviour is done (e.g. close button clicked,
        escape key pressed).
        <FormRowDetails>
          <code>cancel: () => void</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="close-complete">
        Triggered when a modal is closed.
        <FormRowDetails>
          <code>close-complete: () => void</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="confirm">
        Triggered when a confirm button is clicked.
        <FormRowDetails>
          <code>close-complete: () => void</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';
import InputText from 'tui/components/form/InputText';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';

export default {
  components: {
    Button,
    ConfirmationModal,
    FormRow,
    FormRowDetails,
    InputText,
    SamplesExample,
    SamplesCtl,
    ToggleSwitch,
    FormRowDefaults,
    Radio,
    RadioGroup,
  },

  data() {
    return {
      message: 'Are you sure you want to do this?',
      modalOpen: false,
      title: 'This is a simple confirmation modal.',
      customConfirmText: 'Delete',
      closeButton: false,
      loading: false,
      confirmDisabled: false,
      modalSize: 'normal',
    };
  },

  methods: {
    showModal() {
      this.modalOpen = true;
    },

    hideModal() {
      this.modalOpen = false;
      this.loading = false;
    },

    async modalConfirmed() {
      console.log('User confirmed the modal action');
      this.loading = true;
      try {
        // perform async action
        await new Promise(resolve => setTimeout(resolve, 2000));
        this.modalOpen = false;
      } finally {
        this.loading = false;
      }
    },

    modalCancelled() {
      console.log('User cancelled the modal action');
      this.hideModal();
    },
  },
};
</script>
