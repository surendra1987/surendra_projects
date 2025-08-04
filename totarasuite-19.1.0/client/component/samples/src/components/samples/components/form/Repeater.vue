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

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module samples
-->

<script setup>
import { ref, watch } from 'vue';
import AddIcon from 'tui/components/icons/Add';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import FormRow from 'tui/components/form/FormRow';
import InputNumber from 'tui/components/form/InputNumber';
import InputText from 'tui/components/form/InputText';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import Repeater from 'tui/components/form/Repeater';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';

const afterRow = ref('Battleships are fun');
const allowDeletingFirstItems = ref(true);
const customAddButton = ref(false);
const deleteIcon = ref(false);
const disabled = ref(false);
const header = ref('What is the best battleship?');
const maxRows = ref(5);
const minRows = ref(1);
const noSpacing = ref(false);
const repeatHeader = ref(false);
const rows = ref([
  {
    value: 'HMS Victory',
    disabled: false,
    placeholder: 'first battleship',
  },
  {
    value: 'Bismarck',
    disabled: false,
    placeholder: 'second battleship',
  },
  {
    value: 'USS Enterprise',
    disabled: false,
    placeholder: 'third battleship',
  },
]);

watch(
  rows,
  newVal => {
    rows.value = newVal;
  },
  { deep: true }
);

function addNewSection() {
  rows.value = [
    ...rows.value,
    {
      disabled: false,
      label: '',
      value: '',
      placeholder: 'Battleship name',
    },
  ];
}

function deleteSection(row) {
  rows.value = rows.value.filter(v => v !== row);
}
</script>

<template>
  <SamplesExample>
    <Repeater
      :rows="rows"
      :min-rows="parseInt(minRows)"
      :max-rows="parseInt(maxRows)"
      :disabled="disabled"
      :delete-icon="deleteIcon"
      :allow-deleting-first-items="allowDeletingFirstItems"
      :no-spacing="noSpacing"
      :repeat-header="repeatHeader"
      @add="addNewSection"
      @remove="deleteSection"
    >
      <template v-if="header" v-slot:header>
        {{ header }}
      </template>
      <template v-slot="{ row }">
        <div>
          <InputText
            v-model:value="row.value"
            aria-label="aria-label"
            :disabled="row.disabled"
            :placeholder="row.placeholder"
          />
        </div>
      </template>
      <template v-if="afterRow" v-slot:after-row>
        {{ afterRow }}
      </template>
      <template v-if="customAddButton" v-slot:add>
        <ButtonIcon
          v-if="rows.length < maxRows"
          :aria-label="$str('add', 'core')"
          :aria-controls="uid"
          :styleclass="{ small: true }"
          :disabled="disabled"
          @click="addNewSection"
        >
          Custom add button text
          <AddIcon />
        </ButtonIcon>
      </template>
    </Repeater>
  </SamplesExample>

  <SamplesCtl>
    <FormRow v-slot="{ id }" label="Minimum rows">
      <InputNumber v-model:value="minRows" />
      <FormRowDetails :id="id">
        min-rows
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Max rows">
      <InputNumber v-model:value="maxRows" />
      <FormRowDetails :id="id">
        max-rows
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Disabled">
      <RadioGroup v-model:value="disabled" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        disabled
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Include delete icon">
      <RadioGroup v-model:value="deleteIcon" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        delete-icon
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Allow deleting of the first item">
      <RadioGroup v-model:value="allowDeletingFirstItems" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        allow-deleting-first-items
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="No spacing">
      <RadioGroup v-model:value="noSpacing" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        no-spacing
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Repeat header">
      <RadioGroup v-model:value="repeatHeader" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        repeat-header
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>

  <SamplesCtl label="Slot options">
    <FormRow v-slot="{ id }" label="Header content">
      <InputText v-model:value="header" />
      <FormRowDetails :id="id">
        header
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Content after each row">
      <InputText v-model:value="afterRow" />
      <FormRowDetails :id="id">
        after-row
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Custom add button">
      <RadioGroup v-model:value="customAddButton" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        add
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>
