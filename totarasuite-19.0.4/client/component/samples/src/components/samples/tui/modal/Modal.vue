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
    A standard modal used for displaying an important message or action to the
    user.

    <SamplesExample>
      <Button text="Show modal" @click="showModal" />

      <ModalPresenter :open="modalOpen" @request-close="modalResponse">
        <Modal
          :type="type"
          :size="size"
          :shade="shade"
          :aria-labelledby="$id('title')"
        >
          <ModalContent
            :title="title"
            :title-id="$id('title')"
            :title-visible="isVisible"
            :close-button="size !== 'sheet'"
          >
            <FormRowStack>
              <FormRow v-slot="{ id, label }" label="Name">
                <InputText :id="id" v-model:value="name" :placeholder="label" />
              </FormRow>

              <Button
                :text="showingMore ? 'Show less' : 'Show more'"
                @click="showingMore = !showingMore"
              />

              <div v-if="showingMore">
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

    <SamplesPropCtl>
      <FormRow v-slot="{ id, label }" label="Title">
        <InputText :id="id" v-model:value="title" :placeholder="label" />
      </FormRow>

      <FormRow label="Type">
        <RadioGroup v-model:value="type" :horizontal="true">
          <Radio value="normal">normal (default)</Radio>
          <Radio value="sheet">sheet</Radio>
          <Radio value="drawer">drawer</Radio>
        </RadioGroup>
      </FormRow>

      <FormRow label="Size">
        <RadioGroup v-model:value="size" :horizontal="true">
          <Radio value="small">small</Radio>
          <Radio value="normal">normal (default)</Radio>
          <Radio value="large">large</Radio>
        </RadioGroup>
      </FormRow>

      <FormRow label="Title visible">
        <Checkbox v-model:checked="isVisible" />
      </FormRow>

      <FormRow label="Shade">
        <Checkbox v-model:checked="shade" />
      </FormRow>
    </SamplesPropCtl>

    <SamplesCode>
      <template v-slot:template>{{ codeTemplate }}</template>
      <template v-slot:script>{{ codeScript }}</template>
    </SamplesCode>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import FormRow from 'tui/components/form/FormRow';
import FormRowStack from 'tui/components/form/FormRowStack';
import InputText from 'tui/components/form/InputText';
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import OkCancelGroup from 'tui/components/buttons/OkCancelGroup';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import Checkbox from 'tui/components/form/Checkbox';
import SamplesCode from 'samples/components/sample_parts/misc/SamplesCode';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesPropCtl from 'samples/components/sample_parts/misc/SamplesPropCtl';

export default {
  components: {
    Button,
    FormRow,
    FormRowStack,
    InputText,
    Modal,
    ModalContent,
    ModalPresenter,
    OkCancelGroup,
    Radio,
    RadioGroup,
    Checkbox,
    SamplesCode,
    SamplesExample,
    SamplesPropCtl,
  },

  data() {
    return {
      modalOpen: false,
      name: '',
      type: 'normal',
      size: 'normal',
      shade: true,
      title: 'Enter your name',
      isVisible: true,
      showingMore: false,
      codeTemplate: `<ModalPresenter :open="modalOpen" @request-close="modalResponse">
  <Modal :size="size" :aria-labelledby="$id('title')">
    <ModalContent
      :title="title"
      :title-id="$id('title')"
      :title-visible="isVisible"
      :close-button="true"
    >
      Some text...

      <template v-slot:buttons>
        <OkCancelGroup @ok="confirm" @cancel="close" />
      </template>
    </ModalContent>
  </Modal>
</ModalPresenter>`,
      codeScript: `import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import OkCancelGroup from 'tui/components/buttons/OkCancelGroup';

export default {
  components: {
    Modal,
    ModalContent,
    ModalPresenter,
    OkCancelGroup,
  }
}

methods: {
  showModal() {
    this.modalOpen = true;
  },

  modalResponse(e) {
    if (e.result) {
      console.log(e.result);
    }
    this.modalOpen = false;
  },

  close() {
    this.modalResponse('');
  },

  confirm() {
    this.modalResponse({ result: { name: this.name } });
  },
},`,
    };
  },

  methods: {
    showModal() {
      this.modalOpen = true;
    },

    modalResponse(e) {
      if (e.result) {
        console.log(e.result);
      }
      this.modalOpen = false;
    },

    close() {
      this.modalResponse('');
    },

    confirm() {
      this.modalResponse({ result: { name: this.name } });
    },
  },
};
</script>
