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
  @author Kevin Hottinger <kevin.hottinger@totaralearning.com>
  @module samples
-->

<template>
  <div class="tui-samplePopover">
    The popover component is a flexible component for displaying content within
    a popover which supports multiple types of triggers.

    <SamplesExample>
      <div class="tui-samplePopover__buttonRow">
        <!-- Hover Popover -->
        <Popover
          :closeable="closeable"
          :context-mode="contextMode"
          :position="position"
          :size="size"
          :slim="slim"
          :title="title"
          :has-content-padding="hasContentPadding"
          :triggers="['hover']"
        >
          <template v-slot:trigger>
            <Button text="Hover" />
          </template>

          {{ content }}

          <template v-if="hasButtons" v-slot:buttons="{ close }">
            <Button
              :styleclass="{ primary: true, small: true }"
              text="Primary"
              @click="close"
            />
            <Button :styleclass="{ small: true }" text="Secondary" />
          </template>
        </Popover>

        <!-- Click Popover -->
        <Popover
          :closeable="closeable"
          :context-mode="contextMode"
          :position="position"
          :size="size"
          :slim="slim"
          :title="title"
          :has-content-padding="hasContentPadding"
          :triggers="['click']"
        >
          <template v-slot:trigger>
            <Button text="Click" />
          </template>

          {{ content }}

          <template v-if="hasButtons" v-slot:buttons="{ close }">
            <Button
              :styleclass="{ primary: true, small: true }"
              text="Primary"
              @click="close"
            />
            <Button :styleclass="{ small: true }" text="Secondary" />
          </template>
        </Popover>

        <!-- Focus Popover -->
        <Popover
          :closeable="closeable"
          :context-mode="contextMode"
          :position="position"
          :size="size"
          :slim="slim"
          :title="title"
          :has-content-padding="hasContentPadding"
          :triggers="['focus']"
        >
          <template v-slot:trigger>
            <Button text="Focus" />
          </template>

          {{ content }}

          <template v-if="hasButtons" v-slot:buttons="{ close }">
            <Button
              :styleclass="{ primary: true, small: true }"
              text="Primary"
              @click="close"
            />
            <Button :styleclass="{ small: true }" text="Secondary" />
          </template>
        </Popover>

        <!-- Inside a modal Popover -->
        <Button text="Inside a modal" @click="showModal" />
        <ModalPresenter :open="modalOpen" @request-close="modalRequestClose">
          <Modal size="normal" :aria-labelledby="$id('title')">
            <ModalContent
              title="Hello"
              :title-id="$id('title')"
              :close-button="true"
            >
              <div class="tui-samplePopover__buttonRow">
                <Popover
                  :closeable="closeable"
                  :context-mode="contextMode"
                  :position="position"
                  :size="size"
                  :slim="slim"
                  :title="title"
                  :has-content-padding="hasContentPadding"
                  :triggers="['hover', 'click']"
                >
                  <template v-slot:trigger>
                    <Button text="Trigger" />
                  </template>

                  {{ content }}

                  <template v-if="hasButtons" v-slot:buttons="{ close }">
                    <Button
                      :styleclass="{ primary: true, small: true }"
                      text="Primary"
                      @click="close"
                    />
                    <Button :styleclass="{ small: true }" text="Secondary" />
                  </template>
                </Popover>
              </div>
            </ModalContent>
          </Modal>
        </ModalPresenter>
      </div>
    </SamplesExample>

    <SamplesCtl>
      <!-- size -->
      <FormRow v-slot="{ id }" label="Size">
        <RadioGroup v-model:value="size" :horizontal="true">
          <Radio :value="null">None</Radio>
          <Radio value="sm">Small</Radio>
          <Radio value="md">Medium</Radio>
          <Radio value="lg">Large</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          size
        </FormRowDetails>
      </FormRow>

      <!-- title -->
      <FormRow v-slot="{ id, label }" label="Title">
        <InputText :id="id" v-model:value="title" :placeholder="label" />
        <FormRowDetails :id="id">
          title
        </FormRowDetails>
      </FormRow>

      <!-- hasContentPadding -->
      <FormRow v-slot="{ id }" label="Has content padding">
        <RadioGroup v-model:value="hasContentPadding" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          hasContentPadding
        </FormRowDetails>
      </FormRow>

      <!-- position -->
      <FormRow v-slot="{ id }" label="Position">
        <RadioGroup v-model:value="position">
          <Radio value="bottom">Bottom</Radio>
          <Radio value="top">Top</Radio>
          <Radio value="left">Left</Radio>
          <Radio value="right">Right</Radio>
          <Radio value="bottom-left">Bottom left</Radio>
          <Radio value="bottom-right">Bottom right</Radio>
          <Radio value="top-left">Top left</Radio>
          <Radio value="top-right">Top right</Radio>
          <Radio value="left-top">Left top</Radio>
          <Radio value="left-bottom">Left bottom</Radio>
          <Radio value="right-top">Right top</Radio>
          <Radio value="right-bottom">Right bottom</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          position
        </FormRowDetails>
      </FormRow>

      <!-- closeable -->
      <FormRow v-slot="{ id }" label="Closeable (click trigger only)">
        <RadioGroup v-model:value="closeable" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          closeable
        </FormRowDetails>
      </FormRow>

      <!-- slim -->
      <FormRow v-slot="{ id }" label="Slim (reduced padding)">
        <RadioGroup v-model:value="slim" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          slim
        </FormRowDetails>
      </FormRow>

      <!-- contextMode -->
      <FormRow v-slot="{ id }" label="Context mode">
        <RadioGroup v-model:value="contextMode" :horizontal="true">
          <Radio value="contained">Contained</Radio>
          <Radio value="uncontained">Uncontained</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          contextMode
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>

    <SamplesCtl label="Slot options">
      <FormRow v-slot="{ id, label }" label="Content">
        <InputText :id="id" v-model:value="content" :placeholder="label" />
        <FormRowDetails :id="id">
          content
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Has buttons">
        <RadioGroup v-model:value="hasButtons" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          hasButtons
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import InputText from 'tui/components/form/InputText';
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import Popover from 'tui/components/popover/Popover';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';

export default {
  components: {
    Button,
    FormRow,
    FormRowDetails,
    InputText,
    Modal,
    ModalContent,
    ModalPresenter,
    Popover,
    Radio,
    RadioGroup,
    SamplesExample,
    SamplesCtl,
  },

  data() {
    return {
      contextMode: 'contained',
      closeable: false,
      content:
        'Content. Popovers have optional titles and form fields/action buttons. Action buttons should be small. Use popovers for supporting contextual information.',
      hasButtons: false,
      hasContentPadding: true,
      modalOpen: false,
      position: 'bottom',
      size: null,
      slim: false,
      title: '',
    };
  },

  methods: {
    showModal() {
      this.modalOpen = true;
    },

    modalRequestClose() {
      this.modalOpen = false;
    },
  },
};
</script>

<style lang="scss">
.tui-samplePopover {
  &__buttonRow {
    display: flex;
    > * + * {
      margin-left: var(--gap-4);
    }
  }
}
</style>
