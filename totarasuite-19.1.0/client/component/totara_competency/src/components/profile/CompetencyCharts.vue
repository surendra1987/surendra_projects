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

  @author Aleksandr Baishev <aleksandr.baishev@totaralearning.com>
  @module totara_competency
-->

<template>
  <div class="tui-totara_competency-competencyCharts">
    <div class="tui-totara_competency-competencyCharts__grid">
      <div
        v-for="(item, key) in data.items"
        :key="key + item.name + item.overall_progress"
        class="tui-totara_competency-competencyCharts__chart"
        data-testid="competency-chart"
      >
        <IndividualAssignmentProgress
          :assignment-progress="item"
          :user-id="userId"
          :is-current-user="isCurrentUser"
        />
      </div>
    </div>
  </div>
</template>

<script>
import IndividualAssignmentProgress from 'totara_competency/components/IndividualAssignmentProgress';

export default {
  components: {
    IndividualAssignmentProgress,
  },

  props: {
    data: {
      required: true,
      type: Object,
    },
    userId: {
      type: Number,
      required: true,
    },
    isCurrentUser: {
      type: Boolean,
      required: true,
    },
  },
};
</script>

<style lang="scss">
.tui-totara_competency-competencyCharts {
  container-type: inline-size;

  &__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--gap-card-grid);

    @container (min-width: 83rem) {
      display: grid;
      grid-template-columns: 1fr 1fr;
    }
  }

  &__chart {
    width: 100%;
    min-width: 0;
    max-width: rem-px(800);
    margin: 0 auto;
    padding: var(--gap-7) var(--gap-5);
    border: 1px var(--color-neutral-5) solid;
    border-radius: var(--border-radius-small);

    @container (min-width: 83rem) {
      max-width: none;
    }
  }
}
</style>
