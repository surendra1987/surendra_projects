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

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @package performgoal_type_basic
-->

<template>
  <!-- Use standard goal card component -->
  <GoalCard :goal="goal" @goal-clicked="$emit('goalClicked', $event)" />
</template>

<script>
import GoalCard from 'perform_goal/components/view/PerformGoalCard';

export default {
  components: {
    GoalCard,
  },

  props: {
    // goal details
    goal: { type: Object, required: true },
  },

  emits: ['goalClicked'],
};
</script>
