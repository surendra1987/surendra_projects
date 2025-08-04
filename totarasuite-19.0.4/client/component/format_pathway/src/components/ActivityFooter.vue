<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2023 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Jack Humphrey <jack.humphrey@totara.com>
  @module format_pathway
-->

<template>
  <ButtonGroup class="tui-format_pathway-activityFooter">
    <Button
      v-if="showCompleteButton"
      class="tui-format_pathway-activityFooter__completionButton"
      :styleclass="{ primary: true }"
      :text="$str('activity_mark_as_complete', 'format_pathway')"
      :loading="activityCompleting"
      @click="$emit('mark-activity-as-complete')"
    />

    <ActionLink
      v-if="nextActivityUrl !== null && onActivityPage"
      :text="$str('next', 'core')"
      :href="nextActivityUrl"
    />

    <ActionLink
      v-if="showExitActivityButton && onActivityPage"
      :text="$str('exitactivity', 'scorm')"
      :href="currentActivityUrl"
    />
  </ButtonGroup>
</template>

<script>
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import Button from 'tui/components/buttons/Button';
import ActionLink from 'tui/components/links/ActionLink';

import { Completion } from 'format_pathway/activityCompletion';

export default {
  name: 'ActivityFooter',

  components: {
    ButtonGroup,
    Button,
    ActionLink,
  },

  props: {
    activeActivityCompletion: Object,
    activityCompleting: Boolean,
    courseCompletionData: Object,

    currentActivityUrl: {
      type: String,
      default: null,
    },
    nextActivityUrl: {
      type: String,
      default: null,
    },
    onActivityPage: Boolean,
    courseInteractor: Object,
    showExitActivityButton: Boolean,
  },

  emits: ['mark-activity-as-complete'],

  computed: {
    showCompleteButton() {
      return (
        this.courseCompletionEnabled &&
        this.selfCompletionEnabled &&
        this.courseInteractor.is_enrolled &&
        this.activityIncomplete &&
        this.onActivityPage &&
        this.courseInteractor.is_enrolled_not_pending_approval
      );
    },

    courseCompletionEnabled() {
      return (
        this.courseCompletionData &&
        this.courseCompletionData.completionenabled === true
      );
    },

    selfCompletionEnabled() {
      return (
        this.activeActivityCompletion &&
        this.activeActivityCompletion.activitycompletiontracking ===
          Completion.MANUAL
      );
    },

    activityIncomplete() {
      return (
        this.activeActivityCompletion &&
        !this.activeActivityCompletion.completionstatus
      );
    },
  },
};
</script>

<style lang="scss">
.tui-format_pathway-activityFooter {
  margin: var(--gap-5) 0;
}
</style>
