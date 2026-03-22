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
    Available common button components

    <SamplesExample>
      <Table :data="iconData" :expandable-rows="true">
        <template v-slot:header-row>
          <HeaderCell size="3">Button</HeaderCell>
          <HeaderCell size="3">Name</HeaderCell>
          <HeaderCell size="10">Description</HeaderCell>
        </template>
        <template v-slot:row="{ row }">
          <Cell size="3" column-header="Button">
            <component :is="row.button" :size="size" @click="clicked()" />
          </Cell>

          <Cell size="3" column-header="Name">
            {{ row.name }}
          </Cell>

          <Cell size="10" column-header="Description">
            {{ row.desc }}
          </Cell>
        </template>
      </Table>
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id }" label="Size">
        <RadioGroup v-model:value="size" :horizontal="true">
          <Radio :value="100">100</Radio>
          <Radio :value="200">200</Radio>
          <Radio :value="300">300</Radio>
          <Radio :value="400">400</Radio>
          <Radio :value="500">500</Radio>
          <Radio :value="600">600</Radio>
          <Radio :value="700">700</Radio>
        </RadioGroup>
        <FormRowDetails :id="id">
          size
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import { markRaw } from 'vue';

import CloseButton from 'tui/components/buttons/CloseIcon';
import MoreButton from 'tui/components/buttons/MoreIcon';

import Cell from 'tui/components/datatable/Cell';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import Table from 'tui/components/datatable/Table';

export default {
  components: {
    Cell,
    FormRow,
    FormRowDetails,
    HeaderCell,
    Radio,
    RadioGroup,
    SamplesExample,
    SamplesCtl,
    Table,
  },

  data() {
    return {
      iconData: [
        {
          button: markRaw(CloseButton),
          name: 'CloseButton',
          desc: '...',
        },
        {
          button: markRaw(MoreButton),
          name: 'MoreButton',
          desc: '...',
        },
      ],
      size: 200,
    };
  },

  methods: {
    clicked() {
      console.log('clicked');
    },
  },
};
</script>
