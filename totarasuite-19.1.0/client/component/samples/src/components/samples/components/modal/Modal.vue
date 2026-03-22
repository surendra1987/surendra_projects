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
  <section>
    <h2>Modal</h2>
    A standard modal used for displaying an important message or action to the
    user. Usually lives inside a <code>ModalPresenter</code> and can contain
    <code>ModalContent</code> and <code>ModalHeaderButton</code>.

    <SamplesExample>
      <Button text="Show modal" @click="showModal" />

      <ModalPresenter
        :open="configPresenter.open"
        @request-close="modalResponse"
      >
        <Modal v-bind="configModal" :aria-labelledby="$id('title')">
          <ModalContent title="title" :title-id="$id('title')">
            <FormRowStack>
              <FormRow v-slot="{ id, label }" label="Name">
                <InputText
                  :id="id"
                  v-model:value="inputName"
                  :placeholder="label"
                />
              </FormRow>

              <Button
                :text="isShowMore ? 'Show less' : 'Show more'"
                @click="isShowMore = !isShowMore"
              />

              <div v-if="isShowMore">
                {{ 'hello '.repeat(1000) }}
              </div>
            </FormRowStack>

            <template v-slot:buttons>
              <OkCancelGroup @ok="confirm" @cancel="close" />
            </template>
          </ModalContent>
        </Modal>
      </ModalPresenter>
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id, label }" label="Title">
        <InputText
          :id="id"
          v-model:value="configContent.title"
          :placeholder="label"
        />
      </FormRow>
      <FormRow v-slot="{ id }" label="Type">
        <RadioGroup v-model:value="configModal.type" horizontal>
          <Radio value="normal">normal</Radio>
          <Radio value="sheet">sheet</Radio>
          <Radio value="drawer">drawer</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          <code>type: 'normal'|'sheet'|'drawer'</code>
        </FormRowDetails>
        <FormRowDefaults>normal</FormRowDefaults>
      </FormRow>

      <FormRow v-slot="{ id }" label="Size">
        <RadioGroup v-model:value="configModal.size" :horizontal="true">
          <Radio value="small">small</Radio>
          <Radio value="normal">normal</Radio>
          <Radio value="large">large</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          <code>size: 'small'|'normal'|'large'</code>
        </FormRowDetails>
        <FormRowDefaults>normal</FormRowDefaults>
      </FormRow>
      <FormRow
        label="dismissable"
        helpmsg="Specifies whether a modal supports dismissing behaviours and has a close button."
      >
        <ToggleSwitch
          v-model:value="configModal.dismissable"
          aria-label="set dismissable toggle"
          toggle-only
        />
        <FormRowDetails>
          <code>dismissable: boolean</code>
        </FormRowDetails>
        <FormRowDefaults>true</FormRowDefaults>
      </FormRow>
      <FormRow
        label="shade"
        helpmsg="Specifies whether a modal has a backdrop."
      >
        <ToggleSwitch
          v-model:value="configModal.shade"
          aria-label="set shade toggle"
          toggle-only
        />
        <FormRowDetails>
          <code>shade: boolean</code>
        </FormRowDetails>
        <FormRowDefaults>true</FormRowDefaults>
      </FormRow>

      <FormRow
        label="ariaLabelledby"
        helpmsg="Specifies the aria-labelledby of a modal."
      >
        <InputText v-model:value="configModal.ariaLabelledby" />
        <FormRowDetails>
          <code>aria-labelledby: string</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="ariaLabel" helpmsg="Specifies the aria-label of a modal.">
        <InputText v-model:value="configModal.ariaLabel" />
        <FormRowDetails>
          <code>aria-label: string</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        label="errorModal"
        helpmsg="Specifies whether a modal has an error modal which has the highest z-index."
      >
        <ToggleSwitch
          v-model:value="configModal.errorModal"
          aria-label="set errorModal toggle"
          toggle-only
        />
        <FormRowDetails>
          <code>errorModal: boolean</code>
        </FormRowDetails>
        <FormRowDefaults>true</FormRowDefaults>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Slot options">
      <FormRow label="default">
        The content area for a modal, normally contains a
        <code>ModalContent</code> component.
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
          <code>close: ({result: any, cancel: ()=>void }) => void</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </section>
  <section>
    <h2>ModalPresenter</h2>
    <p>Wraps a <code>Modal</code> component and provides open/close control.</p>

    <SamplesCtl>
      <FormRow label="open" helpmsg="Controls the modal's open/close state.">
        <ToggleSwitch
          v-model:value="configPresenter.open"
          aria-label="set open toggle"
          toggle-only
        />
        <FormRowDetails>
          <code>open: boolean</code>
        </FormRowDetails>
        <FormRowDefaults>true</FormRowDefaults>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Slot options">
      <FormRow label="default">
        The content area for a modal presenter, normally contains a
        <code>Modal</code> component.
        <FormRowDetails>
          <code>default</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Event options">
      <FormRow label="close-complete">
        Triggered when a modal is closed.
        <FormRowDetails>
          <code>close-complete: () => void</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="request-close">
        Triggered when a dismiss behaviour is done (e.g. close button clicked,
        escape key pressed).
        <FormRowDetails>
          <code>request-close: () => void</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </section>
  <section>
    <h2>ModalContent</h2>
    <p>The content of a modal component.</p>
    <SamplesExample>
      <ModalContent v-bind="configContent">
        Some content
      </ModalContent>
    </SamplesExample>
    <SamplesCtl>
      <FormRow label="title" helpmsg="Specifies the title of a modal content.">
        <InputText v-model:value="configContent.title" />
        <FormRowDetails>
          <code>title: string</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        label="titleId"
        helpmsg="Specifies a unique id for a modal content title."
      >
        <InputText v-model:value="configContent.titleId" />
        <FormRowDetails>
          <code>titleId: string</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        label="titleSmall"
        helpmsg="Specifies whether a modal content's title is small."
      >
        <ToggleSwitch
          v-model:value="configContent.titleSmall"
          aria-label="set titleSmall toggle"
          toggle-only
        />
        <FormRowDetails>
          <code>titleSmall: boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        label="titleVisible"
        helpmsg="Specifies whether a modal content's title is visible."
      >
        <ToggleSwitch
          v-model:value="configContent.titleVisible"
          aria-label="set titleVisible toggle"
          toggle-only
        />
        <FormRowDetails>
          <code>titleVisible: boolean</code>
        </FormRowDetails>
        <FormRowDefaults>true</FormRowDefaults>
      </FormRow>
      <FormRow
        label="closeButton"
        helpmsg="Specifies whether to show the close button in a modal content."
      >
        <ToggleSwitch
          v-model:value="configContent.closeButton"
          aria-label="set closeButton toggle"
          toggle-only
        />
        <FormRowDetails>
          <code>closeButton: boolean</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        label="contentPadding"
        helpmsg="Specifies whether to give padding to a modal content."
      >
        <ToggleSwitch
          v-model:value="configContent.contentPadding"
          aria-label="set contentPadding toggle"
          toggle-only
        />
        <FormRowDetails>
          <code>contentPadding: boolean</code>
        </FormRowDetails>
        <FormRowDefaults>true</FormRowDefaults>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Slot options">
      <FormRow label="default">
        The main content area for a modal content.
        <FormRowDetails>
          <code>default</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="title">
        The content area for a modal's title.
        <FormRowDetails>
          <code>title</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="custom-title">
        The content area after a modal's title. It doesn't apply the usual
        title's classes.
        <FormRowDetails>
          <code>custom-title</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="header-buttons">
        The button area in the modal's header. Use
        <code>ModalHeaderButton</code> for a consistent styling.
        <FormRowDetails>
          <code>header-buttons</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="footer-content">
        The content area for a modals's footer.
        <FormRowDetails>
          <code>footer-content</code>
        </FormRowDetails>
      </FormRow>
      <FormRow label="buttons">
        The button area in a modal's footer. This area is inside the default
        footer content, so it will be replaced by using a custom
        <code>#footer-content</code> slot.
        <FormRowDetails>
          <code>buttons</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Event options">
      <FormRow label="dismiss">
        Triggered when the close button in the modal header is clicked.
        <FormRowDetails>
          <code>dismiss: () => void</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </section>
  <section>
    <h2>ModalHeaderButton</h2>
    <SamplesExample>
      <ModalHeaderButton v-bind="configHeaderBtn">
        <SizeExpandIcon :size="200" state="dimmed" />
      </ModalHeaderButton>
    </SamplesExample>
    <SamplesCtl>
      <FormRow
        label="ariaLabel"
        helpmsg="Specifies the aria-label of a modal header button."
      >
        <InputText v-model:value="configHeaderBtn.ariaLabel" />
        <FormRowDetails>
          <code>aria-label: string</code>
        </FormRowDetails>
      </FormRow>
      <FormRow
        label="title"
        helpmsg="Specifies the title property of a modal header button."
      >
        <InputText v-model:value="configHeaderBtn.title" />
        <FormRowDetails>
          <code>title: string</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Slot options">
      <FormRow label="default">
        The main cotent area in a button that only allows a icon.
        <FormRowDetails>
          <code>default</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
    <SamplesCtl label="Event options">
      <FormRow label="click">
        Triggered when the close button in the modal header is clicked.
        <FormRowDetails>
          <code>click: (event) => void</code>
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </section>
</template>

<script setup>
import Button from 'tui/components/buttons/Button';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
import FormRowStack from 'tui/components/form/FormRowStack';
import InputText from 'tui/components/form/InputText';
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import ModalHeaderButton from 'tui/components/modal/ModalHeaderButton';
import SizeExpandIcon from 'tui/components/icons/SizeExpand';
import OkCancelGroup from 'tui/components/buttons/OkCancelGroup';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import { reactive, ref } from 'vue';

const configPresenter = reactive({
  open: false,
});

const configModal = reactive({
  type: 'normal',
  size: 'normal',
  dismissable: true,
  shade: true,
  ariaLabelledby: '',
  ariaLabel: '',
  errorModal: false,
});

const configContent = reactive({
  title: 'Enter your name',
  isVisible: true,
  closeButton: false,
  titleId: 'sample_modal_content',
  titleSmall: false,
  titleVisible: true,
  contentPadding: true,
});

const configHeaderBtn = reactive({
  ariaLabel: 'resizeAriaLabel',
  title: 'resizeAriaLabel',
});

const isShowMore = ref(false);
const inputName = ref('');

const showModal = () => {
  configPresenter.open = true;
};

const modalResponse = e => {
  if (e.result) {
    console.log(e.result);
  }
  configPresenter.open = false;
};

const close = () => {
  modalResponse('');
};

const confirm = () => {
  modalResponse({ result: { name: inputName } });
};
</script>
