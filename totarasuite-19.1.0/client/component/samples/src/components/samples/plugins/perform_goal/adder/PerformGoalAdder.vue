<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

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

<template>
  <div class="tui-loader">
    <SamplesExample>
      <Button text="Add Goals" @click="adderOpen" />

      <GoalAdder
        :show-loading-btn="showAddButtonSpinner"
        :open="showAdder"
        :existing-items="addedIds"
        @added="adderUpdate"
        @add-button-clicked="toggleLoading"
        @cancel="adderCancelled"
      />

      <h4>Selected Items:</h4>
      <div v-for="goal in addedGoals" :key="goal.id">
        {{ goal }}
      </div>
    </SamplesExample>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import GoalAdder from 'perform_goal/components/adder/PerformGoalAdder';
import SamplesExample from 'samples/components/sample_parts/misc/SamplesExample';

export default {
  components: {
    Button,
    GoalAdder,
    SamplesExample,
  },

  data() {
    return {
      addedGoals: [],
      addedIds: [],
      showAddButtonSpinner: false,
      showAdder: false,
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
      this.addedGoals = selection.data;
      this.showAddButtonSpinner = false;
      this.showAdder = false;
    },

    toggleLoading() {
      this.showAddButtonSpinner = true;
    },
  },
};
</script>
