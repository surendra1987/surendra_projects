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
  <div class="tui-loader">
    <SamplesExample>
      <Button :text="$str('add_audiences', 'totara_core')" @click="adderOpen" />

      <AudienceAdder
        :show-loading-btn="showAddButtonSpinner"
        :open="showAdder"
        :custom-query="query"
        :existing-items="addedIds"
        @added="adderUpdate"
        @add-button-clicked="toggleLoading"
        @cancel="adderCancelled"
      />

      <h4>Selected Items:</h4>
      <div v-for="audience in addedAudiences" :key="audience.id">
        {{ audience }}
      </div>
    </SamplesExample>
  </div>
</template>

<script>
import AudienceAdder from 'tui/components/adder/AudienceAdder';

import Button from 'tui/components/buttons/Button';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';

// Queries
import cohorts from 'core/graphql/cohorts';

export default {
  components: {
    AudienceAdder,
    Button,
    SamplesExample,
  },

  data() {
    return {
      addedAudiences: [],
      addedIds: [],
      showAdder: false,
      showAddButtonSpinner: false,
      query: cohorts,
    };
  },

  methods: {
    adderOpen() {
      this.showAdder = true;
    },

    adderCancelled() {
      this.showAdder = false;
    },

    adderUpdate(selection) {
      this.addedIds = selection.ids;
      this.addedAudiences = selection.data;
      this.showAddButtonSpinner = false;
      this.showAdder = false;
    },

    toggleLoading() {
      this.showAddButtonSpinner = true;
    },
  },
};
</script>
