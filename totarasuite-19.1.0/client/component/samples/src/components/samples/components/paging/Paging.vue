<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2021 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Brian Barnes <brian.barnes@totaralearning.com>
  @module samples
-->

<template>
  <div>
    Paging component

    <SamplesExample>
      <Paging
        :page="parseInt(pageNumber, 10)"
        :items-per-page="parseInt(itemsPerPage, 10)"
        :total-items="parseInt(totalItems, 10)"
        @page-change="updatePage"
        @count-change="updatePageSize"
      />
    </SamplesExample>

    <SamplesCtl>
      <FormRow v-slot="{ id }" label="Page number" required>
        <InputNumber v-model:value="pageNumber" />
        <FormRowDetails :id="id">
          page
        </FormRowDetails>
      </FormRow>
      <FormRow v-slot="{ id }" label="Total Items" required>
        <InputNumber v-model:value="totalItems" />
        <FormRowDetails :id="id">
          totalItems
        </FormRowDetails>
      </FormRow>
    </SamplesCtl>
  </div>
</template>

<script>
import FormRow from 'tui/components/form/FormRow';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import InputNumber from 'tui/components/form/InputNumber';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';
import SamplesCtl from 'samples/components/sample_parts/misc/SamplesCtl';
import Paging from 'tui/components/paging/Paging';

export default {
  components: {
    FormRow,
    FormRowDetails,
    SamplesExample,
    SamplesCtl,
    Paging,
    InputNumber,
  },

  data() {
    return {
      pageNumber: 3,
      itemsPerPage: 10,
      totalItems: 100,
    };
  },

  methods: {
    updatePage(page) {
      this.pageNumber = page;
    },

    updatePageSize(size) {
      this.itemsPerPage = size;
    },
  },
};
</script>
