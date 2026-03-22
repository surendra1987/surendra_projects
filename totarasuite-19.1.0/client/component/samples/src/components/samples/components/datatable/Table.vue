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

  @author Yi-Chin Chyi <yi-chin.chyi@totara.com>
  @package samples
-->

<template>
  <h2>Table</h2>
  Table displays multiple data with similar format.
  <SamplesExample>
    <h3>Simple Table</h3>
    <p>
      A table with headers and rows that use the
      <code>&lt;HeaderCell&gt;</code> component in the
      <code>header-row</code> slot and the <code>&lt;Cell&gt;</code> component
      in the <code>row</code> slot within the
      <code>&lt;Table&gt;</code> component.
    </p>
    <Table :data="dataSimple" v-bind="config">
      <template v-slot:header-row>
        <HeaderCell v-bind="configHeader">Col Header 1</HeaderCell>
        <HeaderCell>Col Header 2</HeaderCell>
      </template>
      <template v-slot:row="{ row }">
        <Cell>{{ row.col1 }}</Cell>
        <Cell>{{ row.col2 }}</Cell>
      </template>
    </Table>

    <h3>Expand Table</h3>
    <p>
      Add the <code>expandable-rows</code> attribute to the
      <code>&lt;Table&gt;</code> component to create a table with expandable
      rows. And use the <code>&lt;ExpandCell&gt;</code> component as the icon
      trigger to expand a row and the <code>expand-content</code> slot to
      display content in the expanded block.
    </p>
    <Table
      :data="dataExpand"
      :stack-at="Number(config.stackAt)"
      loading-overlay-active
      v-bind="{ ...config, ...configExpandTable }"
    >
      <template v-slot:header-row>
        <ExpandCell header />
        <HeaderCell v-bind="configHeader" size="16" valign="center"
          >col 1</HeaderCell
        >
      </template>

      <template v-slot:row="{ row, expand, expandState }">
        <ExpandCell
          :aria-label="row.title"
          :expand-state="expandState"
          @click="expand()"
        />
        <Cell size="16" column-header="col 1" valign="center">
          <template v-slot:default>
            {{ row.title }}
          </template>
        </Cell>
      </template>

      <template v-slot:expand-content="{ row }">
        <h3>{{ row.title }}</h3>
        Expanded row content
      </template>
    </Table>
  </SamplesExample>
  <SamplesCtl>
    <FormRow label="data" required>
      Defines the data displayed in a table. The sigle item can then be accessed
      in <code>row</code> slot with <code>v-slot:row="{ row }"</code>
      <FormRowDetails>
        <code>data: Array&lt;any&gt;</code>
      </FormRowDetails>
    </FormRow>

    <FormRow
      label="archived"
      helpmsg="Apply the archived CSS style to the table."
    >
      <ToggleSwitch
        v-model:value="config.archived"
        aria-label="set archived toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>archived: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="thinHeader"
      helpmsg="Apply the thin header CSS style to the table."
    >
      <ToggleSwitch
        v-model:value="config.thinHeader"
        aria-label="set thinHeader toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>thinHeader: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="indentContents"
      helpmsg="Apply the indent CSS style to the table."
    >
      <ToggleSwitch
        v-model:value="config.indentContents"
        aria-label="set indentContents toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>indentContents: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="borderBottomHidden"
      helpmsg="Remove the bottom border of a table."
    >
      <ToggleSwitch
        v-model:value="config.borderBottomHidden"
        aria-label="set borderBottomHidden toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>borderBottomHidden: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="borderSeparatorHidden"
      helpmsg="Hide separator border between rows."
    >
      <ToggleSwitch
        v-model:value="config.borderSeparatorHidden"
        aria-label="set borderSeparatorHidden toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>borderSeparatorHidden: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="borderTopHidden"
      helpmsg="Hide separator border between table head and body."
    >
      <ToggleSwitch
        v-model:value="config.borderTopHidden"
        aria-label="set borderTopHidden toggle."
        toggle-only
      />
      <FormRowDetails>
        <code>borderTopHidden: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="colorOddRows" helpmsg="Apply background to the odd rows.">
      <ToggleSwitch
        v-model:value="config.colorOddRows"
        aria-label="set colorOddRows toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>colorOddRows: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="hoverOff" helpmsg="Remove the highlight on hovoring a row.">
      <ToggleSwitch
        v-model:value="config.hoverOff"
        aria-label="set hoverOff toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>hoverOff: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="stackAt"
      helpmsg="Specifies the pixel width below which the table should stack."
    >
      <InputNumber v-model:value.number="config.stackAt" />

      <FormRowDetails>
        <code>stackAt: number</code>
      </FormRowDetails>
      <FormRowDefaults>
        570
      </FormRowDefaults>
    </FormRow>
    <FormRow
      label="noItemsText"
      helpmsg="Specifies the text to display when there is no data."
    >
      <InputText v-model:value="config.noItemsText" />

      <FormRowDetails>
        <code>noItemsText: string</code>
      </FormRowDetails>
      <FormRowDefaults>
        getString('noitems', 'totara_core')
      </FormRowDefaults>
    </FormRow>
    <hr />
    <p><b>Props related to loading skeleton</b></p>
    <FormRow
      label="loadingPreview"
      helpmsg="Specifis whether to show the loading skeleton in table cells."
    >
      <ToggleSwitch
        v-model:value="config.loadingPreview"
        aria-label="set loadingPreview toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>loadingPreview: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="loadingPreviewRows">
      <template #help-message
        >Works with <code>loadingPreview = true</code> and specifis the number
        of rows to show the loading skeleton.</template
      >
      <InputNumber v-model:value.number="config.loadingPreviewRows" />

      <FormRowDetails>
        <code>loadingPreviewRows: number</code>
      </FormRowDetails>
      <FormRowDefaults>5</FormRowDefaults>
    </FormRow>
    <FormRow label="loadingOverlayActive">
      <template #help-message
        >Works with <code>loadingPreview = true</code> and apply the overlay CSS
        style to loading skeleton.</template
      >
      <ToggleSwitch
        v-model:value="config.loadingOverlayActive"
        aria-label="set loadingOverlayActive toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>loadingOverlayActive: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="headerHasLoaded">
      <template #help-message
        >Works with <code>loadingPreview = true</code> to decide if the header
        data should be shown while the rest of the cells display a loading
        skeleton.</template
      >
      <ToggleSwitch
        v-model:value="config.headerHasLoaded"
        aria-label="set headerHasLoaded toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>headerHasLoaded: boolean</code>
      </FormRowDetails>
    </FormRow>
    <hr />
    <p><b>Props for Expand Table</b></p>
    <FormRow label="expandableRows">
      <template #help-message
        >Defines table rows to be expandable. Also, add
        <code>&lt;ExpandCell&gt;</code> in <code>#header-row</code> and
        <code>#rows</code> slots to create the trigger button, and use
        <code>#expand-content</code> slot to define the content in the expanded
        blocks.</template
      >
      <ToggleSwitch
        v-model:value="configExpandTable.expandableRows"
        aria-label="set expandableRows toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>expandableRows: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="indentExpandedContents"
      helpmsg="Apply the indent CSS style to the expanded block."
    >
      <ToggleSwitch
        v-model:value="configExpandTable.indentExpandedContents"
        aria-label="set indentExpandedContents toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>indentExpandedContents: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="stealthExpanded"
      helpmsg="Remove the drop shadow from expanded blocks."
    >
      <ToggleSwitch
        v-model:value="configExpandTable.stealthExpanded"
        aria-label="set stealthExpanded toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>stealthExpanded: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="expandMultipleRows"
      helpmsg="Specifies whether user can expand mutiple rows at the same time."
    >
      <ToggleSwitch
        v-model:value="configExpandTable.expandMultipleRows"
        aria-label="set expandMultipleRows toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>expandMultipleRows: boolean</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Slot Options">
    <FormRow label="header-row">
      The content area for table head. Use <code>&lt;HeaderCell&gt;</code> for
      head cell data or <code>&lt;ExpandCell&gt;</code> for expand table head
      cell.
      <FormRowDetails>
        <code>#header-row</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="pre-rows">
      The row content area below the table head for providing extra information
      to a table.
      <FormRowDetails>
        <code>#pre-rows</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="row">
      The row content area in table body. Use <code>&lt;Cell&gt;</code> for cell
      data or <code>&lt;ExpandCell&gt;</code> for the trigger expanding the row.
      <FormRowDetails>
        <code
          >#row="{ row, id, expand, expandState, expandGroup, firstInGroup,
          groupId, inGroup, dragging }"</code
        ><br />
        <ul>
          <li><code>row</code>: Access the single row data.</li>
          <li>
            <code>expandState</code>: In a expand table, this indicates whether
            the row is expanded and should be passed down to the
            <code>&lt;ExpandCell&gt;</code>'s
            <code>:expand-state</code> attribute.
          </li>
          <li>
            <code>expand</code>: The method that toggles the expand block and
            should be passed down to the <code>&lt;ExpandCell&gt;</code>'s
            <code>@click</code> event.
          </li>
        </ul>
      </FormRowDetails>
    </FormRow>
    <FormRow label="expand-content">
      The expanded content area in rows.
      <FormRowDetails>
        <code>#expand-content="{ row }"</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="group-expand-content">
      The expanded content area in row groups.
      <FormRowDetails>
        <code>#group-expand-content="{ group }"</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>

  <h2>HeaderCell</h2>
  <code>&lt;HeaderCell&gt;</code> is used with <code>&lt;Table&gt;</code> in the
  <code>#header-row</code> slot. It defines the data for table header cells.
  <SamplesExample>
    <Table :data="dataSimple">
      <template v-slot:header-row>
        <HeaderCell v-bind="configHeader">Experimental cell</HeaderCell>
        <HeaderCell>Control Cell</HeaderCell>
      </template>
      <template v-slot:row="{ row }">
        <Cell>{{ row.col1 }}</Cell>
        <Cell>{{ row.col2 }}</Cell>
      </template>
    </Table>
  </SamplesExample>
  <SamplesCtl>
    <FormRow
      label="align"
      helpmsg="Specifies the horizontal alignment of the content in a header cell."
    >
      <RadioGroup v-model:value="configHeader.align" name="align">
        <Radio value="start">start</Radio>
        <Radio value="center">center</Radio>
        <Radio value="end">end</Radio>
      </RadioGroup>
      <FormRowDetails>
        <code>align: 'start'|'center'|'end'</code>
      </FormRowDetails>
      <FormRowDefaults>start</FormRowDefaults>
    </FormRow>
    <FormRow
      label="valign"
      helpmsg="Specifies the vertical alignment of the content in a header cell."
    >
      <RadioGroup v-model:value="configHeader.valign" name="valign">
        <Radio value="start">start</Radio>
        <Radio value="center">center</Radio>
        <Radio value="end">end</Radio>
      </RadioGroup>
      <FormRowDetails>
        <code>valign: 'start'|'center'|'end'</code>
      </FormRowDetails>
      <FormRowDefaults>start</FormRowDefaults>
    </FormRow>
    <FormRow
      label="size"
      helpmsg="Specifies the relative width of a cell in a row."
    >
      <SelectFilter
        v-model:value="configHeader.size"
        label=""
        :options="[
          '1',
          '2',
          '3',
          '4',
          '5',
          '6',
          '7',
          '8',
          '9',
          '10',
          '11',
          '12',
          '13',
          '14',
          '15',
          '16',
        ]"
      />

      <FormRowDetails>
        <code
          >size: '1' | '2' | '3' | '4' | '5' | '6' | '7' | '8' | '9' | '10' |
          '11' | '12' | '13' | '14' | '15' | '16'</code
        >
      </FormRowDetails>
      <FormRowDefaults>1</FormRowDefaults>
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Slot Options">
    <FormRow label="default">
      The content area in a head cell.
      <FormRowDetails>
        <code>#default</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="custom-loader">
      Cusomizes the loading state of a header cell. Works with
      <code>loadingPreview = true</code>.
      <FormRowDetails>
        <code>#custom-loader</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>

  <h2>Cell</h2>
  The <code>&lt;Cell&gt;</code> component is used with
  <code>&lt;Table&gt;</code> component in its <code>#row</code> slot to define
  cell data.
  <SamplesExample>
    <Table :data="dataSimple" :loading-preview="config.loadingPreview">
      <template v-slot:header-row>
        <HeaderCell>col1</HeaderCell>
        <HeaderCell>col2</HeaderCell>
      </template>
      <template v-slot:row>
        <Cell v-bind="configCell">Experimental cell</Cell>
        <Cell>Control cell</Cell>
      </template>
    </Table>
  </SamplesExample>
  <SamplesCtl>
    <FormRow
      label="align"
      helpmsg="Specifies the horizontal alignment of the content in a cell."
    >
      <RadioGroup v-model:value="configCell.align" name="align">
        <Radio value="start">start</Radio>
        <Radio value="center">center</Radio>
        <Radio value="end">end</Radio>
      </RadioGroup>
      <FormRowDetails>
        <code>align: 'start'|'center'|'end'</code>
      </FormRowDetails>
      <FormRowDefaults>start</FormRowDefaults>
    </FormRow>
    <FormRow
      label="valign"
      helpmsg="Specifies the vertical alignment of the content in a cell."
    >
      <RadioGroup v-model:value="configCell.valign" name="valign">
        <Radio value="start">start</Radio>
        <Radio value="center">center</Radio>
        <Radio value="end">end</Radio>
      </RadioGroup>
      <FormRowDetails>
        <code>valign: 'start'|'center'|'end'</code>
      </FormRowDetails>
      <FormRowDefaults>start</FormRowDefaults>
    </FormRow>
    <FormRow
      label="size"
      helpmsg="Specifies the relative width of a cell in a row."
    >
      <SelectFilter
        v-model:value="configCell.size"
        label=""
        :options="[
          '1',
          '2',
          '3',
          '4',
          '5',
          '6',
          '7',
          '8',
          '9',
          '10',
          '11',
          '12',
          '13',
          '14',
          '15',
          '16',
        ]"
      />

      <FormRowDetails>
        <code
          >size: '1' | '2' | '3' | '4' | '5' | '6' | '7' | '8' | '9' | '10' |
          '11' | '12' | '13' | '14' | '15' | '16'</code
        >
      </FormRowDetails>
      <FormRowDefaults>1</FormRowDefaults>
    </FormRow>
    <FormRow
      label="columnHeader"
      helpmsg="Specifies the header text that displayed inside a cell in a stacked table."
    >
      <InputText v-model:value="configCell.columnHeader" />
      <FormRowDetails>
        <code>columnHeader: string</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="heavy" helpmsg="Specifis the font weight in a cell.">
      <ToggleSwitch
        v-model:value="configCell.heavy"
        aria-label="set heavy toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>heavy: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="loaderLines">
      <template #help-message
        >Specifies the line number of loading skeleton on a cell's loading
        state. Works with the <code>&lt;Table&gt;</code>'s
        <code>loadingPreview = true</code></template
      >
      <InputNumber v-model:value.number="configCell.loaderLines" />

      <FormRowDetails>
        <code>loaderLines: number</code>
      </FormRowDetails>
      <FormRowDefaults>
        1
      </FormRowDefaults>
    </FormRow>
    <FormRow label="loaderLinesStacked">
      <template #help-message
        >Specifies the line number of loading skeleton on a cell's loading state
        in a stacked table. Works with the <code>&lt;Table&gt;</code>'s
        <code>loadingPreview = true</code></template
      >
      <InputNumber v-model:value.number="configCell.loaderLinesStacked" />
      <FormRowDetails>
        <code>loaderLinesStacked: number</code>
      </FormRowDetails>
      <FormRowDefaults>
        1
      </FormRowDefaults>
    </FormRow>
    <FormRow label="repeatedHeader" helpmsg="Hides the cell content.">
      <ToggleSwitch
        v-model:value="configCell.repeatedHeader"
        aria-label="set repeatedHeader toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>repeatedHeader: boolean</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>

  <h2>ExpandCell</h2>
  <SamplesExample>
    <Table :data="dataExpand" expandable-rows loading-overlay-active>
      <template v-slot:header-row>
        <ExpandCell header />
        <HeaderCell size="16" valign="center">col 1</HeaderCell>
      </template>

      <template v-slot:row="{ expand, expandState, id }">
        <ExpandCell
          aria-label="control cell"
          v-bind="id == 0 ? configExpandCell : {}"
          :expand-state="expandState"
          @click="expand()"
        />
        <Cell size="16" column-header="col 1" valign="center">
          <template v-slot:default>
            {{ id == 0 ? 'Experimental cell' : 'Control cell' }}
          </template>
        </Cell>
      </template>

      <template v-slot:expand-content="{ row }">
        <h3>{{ row.title }}</h3>
        Expanded row content
      </template>
    </Table>
  </SamplesExample>
  <SamplesCtl>
    <FormRow
      label="ariaLabel"
      required
      helpmsg="Specifies the value of aria-label on a expand icon button."
    >
      <InputText v-model:value="configExpandCell.ariaLabel" />

      <FormRowDetails>
        <code>ariaLabel: string</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="text"
      required
      helpmsg="Specifies the text of expand button on a stacked table."
    >
      <InputText v-model:value="configExpandCell.text" />

      <FormRowDetails>
        <code>text: string</code>
      </FormRowDetails>
      <FormRowDefaults>
        getString('details', 'totara_core')
      </FormRowDefaults>
    </FormRow>
    <FormRow
      label="header"
      helpmsg="Indicates it's used as the header cell for the column containing the expand icon button."
    >
      <ToggleSwitch
        v-model:value="configExpandCell.header"
        aria-label="set header toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>header: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow
      label="empty"
      helpmsg="Indicates the expanded content for the row is empty, therefore hide the expand button."
    >
      <ToggleSwitch
        v-model:value="configExpandCell.empty"
        aria-label="set empty toggle"
        toggle-only
      />
      <FormRowDetails>
        <code>empty: boolean</code>
      </FormRowDetails>
    </FormRow>
    <FormRow label="expandState">
      Defines whether the row is expanded. This should bind with the
      <code>expandState</code> slot prop provided by <code>#row</code> slot.
      <FormRowDetails>
        <code>expandState: boolean</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
  <SamplesCtl label="Event options">
    <FormRow label="click">
      The event is triggered by clicking expand button. It should use the
      <code>expand</code> slot prop provided by <code>#row</code> slot.
      <FormRowDetails>
        <code>click: (event) => void</code>
      </FormRowDetails>
    </FormRow>
  </SamplesCtl>
</template>

<script setup>
import { reactive, ref } from 'vue';

import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import Table from 'tui/components/datatable/Table';
import Cell from 'tui/components/datatable/Cell';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import ExpandCell from 'tui/components/datatable/ExpandCell';
import RadioGroup from 'tui/components/form/RadioGroup';
import Radio from 'tui/components/form/Radio';
import SelectFilter from 'tui/components/filters/SelectFilter';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';
import InputText from 'tui/components/form/InputText';
import InputNumber from 'tui/components/form/InputNumber';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';

const dataSimple = ref([
  { col1: 'col 1', col2: 'col 2' },
  { col1: 'col 1', col2: 'col 2' },
]);

const dataExpand = ref([
  { ready: true, title: 'aaa' },
  { ready: true, title: 'some random text' },
  { ready: false, title: 'ccc' },
  { ready: true, title: 'ddd' },
]);

const config = reactive({
  archived: false,
  indentContents: false,
  indentExpandedContents: false,
  borderBottomHidden: false,
  borderSeparatorHidden: false,
  borderTopHidden: false,
  colorOddRows: false,
  hoverOff: false,
  stackAt: 570,
  noItemsText: 'Nothing here.',
  loadingPreview: false,
  headerHasLoaded: false,
  loadingOverlayActive: false,
  loadingPreviewRows: 5,
  thinHeader: false,
  stackedHeaderRowGap: false,
});

const configExpandTable = reactive({
  expandMultipleRows: false,
  indentExpandedContents: false,
  stealthExpanded: false,
  expandableRows: true,
});

const configHeader = reactive({
  align: 'start',
  valign: 'start',
  size: '1',
});

const configCell = reactive({
  align: 'start',
  valign: 'start',
  size: '1',
  columnHeader: '',
  heavy: false,
  loaderLines: 1,
  loaderLinesStacked: 1,
  repeatedHeader: false,
});

const configExpandCell = reactive({
  ariaLabel: 'expand button',
  text: '...more...',
  empty: false,
  header: false,
});
</script>
