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

  @author Mark Metcalfe <mark.metcalfe@totaralearning.com>
  @module samples
-->

<template>
  <div>
    <h2>InformationModal</h2>
    An information modal, which forces the user to read information that is not
    actionable. <br />
    It is an extention of <code>Modal</code> related components.

    <SamplesExample>
      <Button text="Show information modal" @click="showModal" />

      <InformationModal :open="modalOpen" :title="title" @close="modalClosed">
        {{ message }}
      </InformationModal>
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
      <FormRow
        label="title"
        helpmsg="Specifies the title of a information modal."
      >
        <InputText v-model:value="title" />
        <FormRowDetails>
          <code>title: string</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Slot options">
      <FormRow label="default">
        The content area for a information modal.
        <FormRowDetails>
          <code>default</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Event options">
      <FormRow label="close">
        Triggered when a dismiss behaviour is done (e.g. close button clicked,
        escape key pressed).
        <FormRowDetails>
          <code>close: () => void</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import FormRowDetails from 'tui/components/form/FormRowDetails';
import Button from 'tui/components/buttons/Button';
import FormRow from 'tui/components/form/FormRow';
import InformationModal from 'tui/components/modal/InformationModal';
import InputText from 'tui/components/form/InputText';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';

export default {
  components: {
    FormRowDetails,
    Button,
    FormRow,
    InformationModal,
    InputText,
    SamplesExample,
    SamplesCtl,
    FormRowDefaults,
    ToggleSwitch,
  },

  data() {
    return {
      message:
        'You can not do this action yet. You must complete more things before you can.',
      modalOpen: false,
      title: 'This is a simple information modal.',
      closeButton: true,
    };
  },

  methods: {
    showModal() {
      this.modalOpen = true;
    },

    hideModal() {
      this.modalOpen = false;
    },

    modalClosed() {
      console.log('User closed the modal');
      this.hideModal();
    },
  },
};
</script>
