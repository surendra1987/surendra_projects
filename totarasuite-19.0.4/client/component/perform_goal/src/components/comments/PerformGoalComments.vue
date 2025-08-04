<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @package perform_goal
-->

<template>
  <div class="tui-performGoalDetailsComments">
    <SidePanelCommentBox
      area="goal_comment"
      class="tui-performGoalDetailsComments__area"
      component="perform_goal"
      :extra-extensions="[]"
      :instance-id="goal.id"
      :show-comment-form="true"
      :show-like-button="true"
      @create-comment="handleCommentsUpdate"
      @delete-comment="handleCommentsUpdate"
      @add-reply="handleCommentsUpdate"
    />
  </div>
</template>

<script>
import SidePanelCommentBox from 'totara_comment/components/box/SidePanelCommentBox';

export default {
  components: {
    SidePanelCommentBox,
  },

  props: {
    // Goal data
    goal: { type: Object, required: true },
  },

  emits: ['content-update'],

  methods: {
    handleCommentsUpdate() {
      this.$emit('content-update');
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalDetailsComments {
  height: 100%;
}
</style>
