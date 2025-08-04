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
  @module performelement_perform_goal_creation
-->
<template>
  <div class="tui-linkedReviewParticipantPrint">
    <div v-if="hasBeenAnswered">
      <PerformGoalCreationResponseDisplay
        :active-section-is-closed="false"
        :data="data"
        :element="element"
        :from-print="true"
        :permissions="{}"
      />
    </div>

    <NotepadLines :lines="6" />
  </div>
</template>

<script>
import NotepadLines from 'tui/components/form/NotepadLines';
import PerformGoalCreationResponseDisplay from 'performelement_perform_goal_creation/components/PerformGoalCreationResponseDisplay';

export default {
  components: {
    NotepadLines,
    PerformGoalCreationResponseDisplay,
  },

  props: {
    data: { type: Array, required: true },
    element: { type: Object, required: true },
  },

  computed: {
    /**
     * Has this question been answered.
     *
     * @return {boolean}
     */
    hasBeenAnswered() {
      return this.data.length > 0;
    },
  },
};
</script>
