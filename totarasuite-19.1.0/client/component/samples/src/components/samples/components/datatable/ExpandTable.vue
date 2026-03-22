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
    <SamplesExample>
      <h3>ExpandTable</h3>

      <Loader :loading="loadingPreview">
        <Table
          :color-odd-rows="colorOddRows"
          :data="dummyData"
          :expandable-rows="true"
          :border-bottom-hidden="hideBottomBorder"
          :border-top-hidden="hideTopBorder"
          :indent-expanded-contents="indentExpandedContents"
          :loading-overlay-active="true"
          :header-has-loaded="headerHasLoaded"
          :loading-preview="loadingPreview"
          :stealth-expanded="stealthExpanded"
          :stack-at="Number(stackAt)"
        >
          <template v-slot:header-row>
            <ExpandCell :header="true" />
            <HeaderCell size="16" valign="center">col 1</HeaderCell>
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
      </Loader>

      <h3>ExpandTable with a nested Table</h3>

      <Table
        :color-odd-rows="colorOddRows"
        :data="dummyData"
        :expandable-rows="true"
        :expand-multiple-rows="expandMultipleRows"
        :border-bottom-hidden="hideBottomBorder"
        :border-top-hidden="hideTopBorder"
        :stealth-expanded="stealthExpanded"
        :indent-expanded-contents="indentExpandedContents"
        :stack-at="Number(stackAt)"
      >
        <template v-slot:header-row>
          <ExpandCell :header="true" />
          <HeaderCell size="16" valign="center">col 1</HeaderCell>
        </template>

        <template v-slot:row="{ row, expand, expandState }">
          <ExpandCell
            :aria-label="row.title"
            :expand-state="expandState"
            @click="expand()"
          />
          <Cell size="16" column-header="col 1" valign="center">
            {{ row.title }}
          </Cell>
        </template>

        <template v-slot:expand-content>
          <Table
            :color-odd-rows="colorOddRows"
            :data="dummyData"
            :expandable-rows="true"
            :border-bottom-hidden="hideBottomBorder"
            :border-top-hidden="hideTopBorder"
            :indent-contents="indentContents"
            :stealth-expanded="stealthExpanded"
            :indent-expanded-contents="indentExpandedContents"
          >
            <template v-slot:header-row>
              <HeaderCell size="16" valign="center">col 1</HeaderCell>
            </template>

            <template v-slot:row="{ row }">
              <Cell size="16" column-header="col 1" valign="center">
                {{ row.title }}
              </Cell>
            </template>
          </Table>
        </template>
      </Table>

      <h3>ExpandTable with a nested ExpandTable</h3>

      <Table
        :color-odd-rows="colorOddRows"
        :data="dummyData"
        :expandable-rows="true"
        :expand-multiple-rows="expandMultipleRows"
        :border-bottom-hidden="hideBottomBorder"
        :border-top-hidden="hideTopBorder"
        :indent-contents="indentContents"
        :stealth-expanded="stealthExpanded"
        :indent-expanded-contents="indentExpandedContents"
        :stack-at="Number(stackAt)"
      >
        <template v-slot:header-row>
          <ExpandCell :header="true" />
          <HeaderCell size="16" valign="center">col 1</HeaderCell>
        </template>

        <template v-slot:row="{ row, expand, expandState }">
          <ExpandCell
            :aria-label="row.title"
            :expand-state="expandState"
            @click="expand()"
          />
          <Cell size="16" column-header="col 1" valign="center">
            {{ row.title }}
          </Cell>
        </template>

        <template v-slot:expand-content>
          <Table
            :color-odd-rows="colorOddRows"
            :data="dummyData"
            :expandable-rows="true"
            :border-bottom-hidden="hideBottomBorder"
            :border-top-hidden="hideTopBorder"
            :stealth-expanded="stealthExpanded"
            :indent-expanded-contents="indentExpandedContents"
          >
            <template v-slot:header-row>
              <ExpandCell :header="true" />
              <HeaderCell size="16" valign="center">col 1</HeaderCell>
            </template>

            <template v-slot:row="{ row, expand, expandState }">
              <ExpandCell
                :aria-label="row.title"
                :expand-state="expandState"
                @click="expand()"
              />
              <Cell size="16" column-header="col 1" valign="center">
                {{ row.title }}
              </Cell>
            </template>

            <template v-slot:expand-content="{ row }">
              <h2>{{ row.title }}</h2>
              Expanded row content
            </template>
          </Table>
        </template>
      </Table>
    </SamplesExample>

    <SamplesCtl>
      <FormRow label="header-has-loaded">
        <RadioGroup v-model:value="headerHasLoaded" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
      </FormRow>

      <FormRow label="loading-preview">
        <RadioGroup v-model:value="loadingPreview" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
      </FormRow>

      <FormRow label="Colour odd rows">
        <RadioGroup v-model:value="colorOddRows" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
      </FormRow>

      <FormRow label="Hide top border">
        <RadioGroup v-model:value="hideTopBorder" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
      </FormRow>

      <FormRow label="Hide bottom border">
        <RadioGroup v-model:value="hideBottomBorder" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
      </FormRow>

      <FormRow label="Indent contents">
        <RadioGroup v-model:value="indentContents" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
      </FormRow>

      <FormRow label="Hide shadows on expanded rows">
        <RadioGroup v-model:value="stealthExpanded" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
      </FormRow>

      <FormRow label="Indent expanded contents">
        <RadioGroup v-model:value="indentExpandedContents" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
      </FormRow>

      <FormRow label="Expanded multiple rows">
        <RadioGroup v-model:value="expandMultipleRows" :horizontal="true">
          <Radio :value="true">True</Radio>
          <Radio :value="false">False</Radio>
        </RadioGroup>
      </FormRow>

      <FormRow label="Stack at">
        <InputNumber v-model:value="stackAt" />
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import Cell from 'tui/components/datatable/Cell';
import ExpandCell from 'tui/components/datatable/ExpandCell';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import Table from 'tui/components/datatable/Table';
import InputNumber from 'tui/components/form/InputNumber';
import Loader from 'tui/components/loading/Loader';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import FormRow from 'tui/components/form/FormRow';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';

export default {
  components: {
    Cell,
    ExpandCell,
    HeaderCell,
    Table,
    InputNumber,
    Loader,
    SamplesExample,
    SamplesCtl,
    FormRow,
    Radio,
    RadioGroup,
  },

  data() {
    return {
      dummyData: [
        {
          ready: true,
          title: 'aaa',
        },
        {
          ready: true,
          title: 'some random text',
        },
        {
          ready: false,
          title: 'ccc',
        },
        {
          ready: true,
          title: 'ddd',
        },
      ],
      colorOddRows: false,
      hideBottomBorder: false,
      hideTopBorder: false,
      indentContents: false,
      headerHasLoaded: false,
      loadingPreview: false,
      stealthExpanded: false,
      indentExpandedContents: false,
      expandMultipleRows: false,
      stackAt: 570,
      codeTemplate: `<Table
  :color-odd-rows="true"
  :data="dummyData"
  :expandable-rows="true"
  :stealth-expanded="stealthExpanded"
>
  <!-- Header content -->
  <template v-slot:header-row>
    <ExpandCell :header="true" />
    <HeaderCell size="16" valign="center">col 1</HeaderCell>
  </template>

  <!-- Rows -->
  <template v-slot:row="{ row, expand, expandState }">
    <ExpandCell :expand-state="expandState" @click="expand()" />
    <Cell size="16" column-header="col 1" valign="center">
      {{ row.title }}
    </Cell>
  </template>

  <!-- Expand content -->
  <template v-slot:expand-content="{ row }">
    <h3>{{ row.title }}</h3>
  </template>
</Table>`,
      codeScript: `import Cell from 'tui/components/datatable/Cell';
import ExpandCell from 'tui/components/datatable/ExpandCell';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import Table from 'tui/components/datatable/Table';

export default {
  components: {
    Cell,
    ExpandCell,
    HeaderCell,
    Table,
  },

  data() {
    return {
      dummyData: [
        {
          title: 'aaa',
        },
        {
          title: 'some random text',
        },
      ],
    }
  }
}`,
    };
  },
};
</script>
