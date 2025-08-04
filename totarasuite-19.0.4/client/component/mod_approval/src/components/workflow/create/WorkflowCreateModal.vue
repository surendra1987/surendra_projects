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

  @author Simon Tegg <simon.teg@totaralearning.com>
  @module mod_approval
-->
<template>
  <Modal size="large" :aria-labelledby="id" :dismissable="false">
    <DetailsStep v-if="$matches('details')" :aria-link="id" />
    <ChooseFormStep v-else-if="$matches('chooseForm')" :aria-link="id" />
    <ChooseAssignmentStep
      v-else
      :context-id="contextId"
      :can-view-orgframeworks="canViewOrgframeworks"
      :can-view-posframeworks="canViewPosframeworks"
      :aria-link="id"
    />
  </Modal>
</template>

<script>
import { MOD_APPROVAL__WORKFLOW_CREATE } from 'mod_approval/constants';
import Modal from 'tui/components/modal/Modal';
import DetailsStep from './DetailsStep';
import ChooseFormStep from './ChooseFormStep';
import ChooseAssignmentStep from './ChooseAssignmentStep';

export default {
  components: {
    Modal,
    DetailsStep,
    ChooseFormStep,
    ChooseAssignmentStep,
  },

  props: {
    contextId: Number,
    canViewOrgframeworks: Boolean,
    canViewPosframeworks: Boolean,
  },

  xState: {
    machineId: MOD_APPROVAL__WORKFLOW_CREATE,
  },

  data() {
    return {
      id: this.$id('title'),
    };
  },
};
</script>
