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

  @author Rodney Cruden-powell <rodney.cruden-powell@totara.com>
  @module samples
-->
<script setup>
import { ref } from 'vue';

import AdvancedSelect from 'tui/components/datatable/AdvancedTableSelect';
import Cell from 'tui/components/datatable/Cell';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import InputNumber from 'tui/components/form/InputNumber';
import InputText from 'tui/components/form/InputText';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SelectTable from 'tui/components/datatable/SelectTable';

const advancedSelectEnabled = ref(false);
const borderBottomHidden = ref(false);
const borderSeparatorHidden = ref(false);
const checkboxVAlign = ref('center');
const colorOddRows = ref(false);
const disabledIds = ref('');
const dummyData = ref([
  { title: 'Software Architect', name: 'Ethan Caldwell' },
  { title: 'Marketing Strategist', name: 'Sophie Whitaker' },
  { title: 'Data Analyst', name: 'Liam Donnelly' },
  { title: 'UX Designer', name: 'Ava Kensington' },
]);
const entireSelected = ref(false);
const expandableRows = ref(false);
const groupMode = ref(false);
const headerHasLoaded = ref(false);
const hoverOff = ref(false);
const largeCheckBox = ref(false);
const loadingOverlayActive = ref(true);
const loadingPreview = ref(false);
const loadingPreviewRows = ref(5);
const noItemsText = ref('');
const noLabelOffset = ref(false);
const selectAllEnabled = ref(false);
const selectEntireEnabled = ref(false);
const selectedHighlightOff = ref(false);
const stackAt = ref(570);
const stackedHeaderRowGap = ref(false);
const value = ref([1, 3]);
</script>

<template>
  <SamplesExample>
    <SelectTable
      v-model:value="value"
      :advanced-select-enabled="advancedSelectEnabled"
      :border-bottom-hidden="borderBottomHidden"
      :border-separator-hidden="borderSeparatorHidden"
      :checkbox-v-align="checkboxVAlign"
      :color-odd-rows="colorOddRows"
      :data="dummyData"
      :disabled-ids="disabledIds.split(',').map(item => parseInt(item.trim()))"
      :entire-selected="entireSelected"
      :expandable-rows="expandableRows"
      :group-mode="groupMode"
      :header-has-loaded="headerHasLoaded"
      :hover-off="hoverOff"
      :large-check-box="largeCheckBox"
      :loading-overlay-active="loadingOverlayActive"
      :loading-preview-rows="loadingPreviewRows"
      :loading-preview="loadingPreview"
      :no-items-text="noItemsText"
      :select-all-enabled="selectAllEnabled"
      :select-entire-enabled="selectEntireEnabled"
      :selected-highlight-off="selectedHighlightOff"
      :stack-at="Number(stackAt)"
      :stacked-header-row-gap="stackedHeaderRowGap"
      row-label-key="name"
    >
      <template
        v-slot:advanced-select="{
          selectAllChecked,
          deselectAllVisible,
          selectAllVisible,
          onSelectAllChange,
          loadingOverlayActive,
        }"
      >
        <AdvancedSelect
          :loading-overlay-active="loadingOverlayActive"
          :loading-preview="loadingPreview"
          :large-check-box="largeCheckBox"
          :checkbox-checked="selectAllChecked"
          @checkbox-change="onSelectAllChange"
          @select-all-visible="selectAllVisible"
          @deselect-all-visible="deselectAllVisible"
        />
      </template>

      <template v-slot:header-row>
        <HeaderCell size="12" valign="center">Name</HeaderCell>
        <HeaderCell size="4" valign="center">Title</HeaderCell>
      </template>

      <template v-slot:row="{ row }">
        <Cell size="12" column-header="Name" valign="center">
          <template v-slot:default>
            {{ row.name }}
          </template>
        </Cell>

        <Cell size="4" column-header="Title" valign="center">
          <template v-slot:default>
            {{ row.title }}
          </template>
        </Cell>
      </template>
    </SelectTable>
    <br />
    <p>value: {{ value }}</p>
  </SamplesExample>

  <SamplesCtl>
    <FormRow v-slot="{ id }" label="Advanced select enabled">
      <RadioGroup v-model:value="advancedSelectEnabled" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>advancedSelectEnabled: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Border bottom hidden">
      <RadioGroup v-model:value="borderBottomHidden" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>borderBottomHidden: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Border separator hidden">
      <RadioGroup v-model:value="borderSeparatorHidden" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>borderSeparatorHidden: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Colour odd rows">
      <RadioGroup v-model:value="colorOddRows" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>colorOddRows: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Checkbox vertical align">
      <RadioGroup v-model:value="checkboxVAlign" :horizontal="true">
        <Radio value="start">Start</Radio>
        <Radio value="center">Center</Radio>
        <Radio value="end">End</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>checkboxVAlign: string</code>
      </FormRowDetails>
    </FormRow>

    <FormRow
      v-slot="{ id }"
      label="Disabled Ids"
      helpmsg="Provide a comma separated list of ids. This is then converted to an array for this example"
    >
      <InputText :id="id" v-model:value="disabledIds" />
      <FormRowDetails :id="id">
        <code>disabledIds: array</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Entire selected">
      <RadioGroup v-model:value="entireSelected" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>entireSelected: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Expandable rows">
      <RadioGroup v-model:value="expandableRows" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>expandableRows: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Group mode">
      <RadioGroup v-model:value="groupMode" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>groupMode: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Hover off">
      <RadioGroup v-model:value="hoverOff" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>hoverOff: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Large checkbox">
      <RadioGroup v-model:value="largeCheckBox" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>largeCheckBox: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow
      v-slot="{ id }"
      label="Loading overlay active"
      helpmsg="Enable loading preview to see the effects of this"
    >
      <RadioGroup v-model:value="loadingOverlayActive" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>loadingOverlayActive: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Header has loaded">
      <RadioGroup v-model:value="headerHasLoaded" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>headerHasLoaded: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Loading preview">
      <RadioGroup v-model:value="loadingPreview" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>loadingPreview: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Loading preview rows">
      <InputNumber v-model:value="loadingPreviewRows" />
      <FormRowDetails :id="id">
        <code>loadingPreviewRows: number</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="No label offset">
      <RadioGroup v-model:value="noLabelOffset" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>noLabelOffset: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Select all enabled">
      <RadioGroup v-model:value="selectAllEnabled" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>selectAllEnabled</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Select entire enabled">
      <RadioGroup v-model:value="selectEntireEnabled" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>selectEntireEnabled: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Selected highlight off">
      <RadioGroup v-model:value="selectedHighlightOff" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>selectedHighlightOff: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Stacked header row gap">
      <RadioGroup v-model:value="stackedHeaderRowGap" :horizontal="true">
        <Radio :value="true">True</Radio>
        <Radio :value="false">False</Radio>
      </RadioGroup>
      <FormRowDetails :id="id">
        <code>stackedHeaderRowGap: boolean</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="No items text">
      <InputText :id="id" v-model:value="noItemsText" />
      <FormRowDetails :id="id">
        <code>noItemsText: string</code>
      </FormRowDetails>
    </FormRow>

    <FormRow v-slot="{ id }" label="Stack at">
      <InputNumber v-model:value="stackAt" />
      <FormRowDetails :id="id">
        <code>stackAt: string</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>
