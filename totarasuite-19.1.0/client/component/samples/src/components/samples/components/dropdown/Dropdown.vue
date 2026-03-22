<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Simon Chester <simon.chester@totara.com>
  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module tui
-->

<script setup>
import { ref } from 'vue';
import Button from 'tui/components/buttons/Button';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import DropdownButton from 'tui/components/dropdown/DropdownButton';
import { Uniform, FormRow, FormRadioGroup } from 'tui/components/uniform';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import Radio from 'tui/components/form/Radio';
import AddIcon from 'tui/components/icons/Add';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';

const values = ref({
  contextMode: 'contained',
  position: undefined,
  separator: false,
  disabled: false,
  many: false,
});

const modalOpen = ref(false);

function doThing() {
  console.log('clicked');
}
</script>

<template>
  <SamplesExample>
    <div class="tui-samples-dropdown">
      <Dropdown
        :separator="values.separator"
        :disabled="values.disabled"
        :position="values.position"
        :context-mode="values.contextMode"
      >
        <template v-slot:trigger="{ toggle, isOpen }">
          <Button
            :aria-expanded="isOpen ? 'true' : 'false'"
            aria-label="Dropdown"
            :disabled="values.disabled"
            :caret="true"
            text="Dropdown"
            @click="toggle"
          />
        </template>
        <DropdownItem @click="doThing">Action</DropdownItem>
        <DropdownItem disabled @click="doThing">Another action</DropdownItem>
        <DropdownItem @click="doThing">
          This is an extra long dropdown item
        </DropdownItem>
        <DropdownButton @click="doThing">
          Button
        </DropdownButton>
        <DropdownItem href="https://www.totara.com/">
          External link
        </DropdownItem>
        <template v-if="values.many">
          <DropdownItem v-for="i in 10" :key="i" @click="doThing">
            Action
          </DropdownItem>
        </template>
      </Dropdown>

      <Dropdown
        :separator="values.separator"
        :disabled="values.disabled"
        :position="values.position"
      >
        <template v-slot:trigger="{ toggle, isOpen }">
          <ButtonIcon
            :aria-expanded="isOpen ? 'true' : 'false'"
            aria-label="Add"
            :disabled="values.disabled"
            :caret="true"
            @click="toggle"
          >
            <AddIcon />
          </ButtonIcon>
        </template>
        <DropdownItem @click="doThing">Action</DropdownItem>
        <DropdownItem @click="doThing">Another action</DropdownItem>
        <template v-if="values.many">
          <DropdownItem v-for="i in 10" :key="i" @click="doThing">
            Action
          </DropdownItem>
        </template>
      </Dropdown>

      <Button text="Show modal" @click="modalOpen = true" />
    </div>

    <ModalPresenter :open="modalOpen" @request-close="modalOpen = false">
      <Modal :aria-labelledby="$id('title')">
        <ModalContent title="Modal" :title-id="$id('title')">
          <Dropdown
            :separator="values.separator"
            :disabled="values.disabled"
            :position="values.position"
          >
            <template v-slot:trigger="{ toggle, isOpen }">
              <Button
                :aria-expanded="isOpen ? 'true' : 'false'"
                aria-label="Dropdown"
                :caret="true"
                text="Dropdown"
                @click="toggle"
              />
            </template>
            <DropdownItem>Action</DropdownItem>
            <DropdownItem disabled>Another action</DropdownItem>
            <DropdownItem>
              This is an extra long dropdown item
            </DropdownItem>
            <template v-if="values.many">
              <DropdownItem v-for="i in 10" :key="i" @click="doThing">
                Action
              </DropdownItem>
            </template>
          </Dropdown>
        </ModalContent>
      </Modal>
    </ModalPresenter>
  </SamplesExample>

  <SamplesCtl>
    <Uniform :initial-values="values" @change="v => (values = v)">
      <FormRow v-slot="{ id }" label="Separator">
        <FormRadioGroup name="separator" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </FormRadioGroup>
        <FormRowDetails :id="id">
          <code>separator: boolean</code>
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Disabled">
        <FormRadioGroup name="disabled" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </FormRadioGroup>
        <FormRowDetails :id="id">
          <code>disabled: boolean</code>
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Position">
        <FormRadioGroup name="position" :horizontal="true">
          <Radio :value="undefined">Unspecified</Radio>
          <Radio value="top-left">top-left</Radio>
          <Radio value="top-right">top-right</Radio>
          <Radio value="bottom-left">bottom-left</Radio>
          <Radio value="bottom-right">bottom-right</Radio>
        </FormRadioGroup>
        <FormRowDetails :id="id">
          <code>position: string</code>
        </FormRowDetails>
      </FormRow>

      <FormRow v-slot="{ id }" label="Context mode">
        <FormRadioGroup name="contextMode" :horizontal="true">
          <Radio value="contained">contained</Radio>
          <Radio value="uncontained">uncontained</Radio>
        </FormRadioGroup>
        <FormRowDetails :id="id">
          <code>contextMode: string</code>
        </FormRowDetails>
      </FormRow>

      <FormRow label="Many items">
        <FormRadioGroup name="many" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </FormRadioGroup>
      </FormRow>
    </Uniform>
  </SamplesCtl>
</template>

<style lang="scss">
.tui-samples-dropdown {
  display: flex;

  & > * + * {
    margin-left: var(--gap-1);
  }
}
</style>
