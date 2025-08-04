<!-- This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Simon Tegg <simon.tegg@totaralearning.com>
  @module mod_approval
-->
<template>
  <ModalContent
    :title="$str('add_assignment', 'mod_approval')"
    :title-id="ariaLink"
  >
    <div class="tui-mod_approval-chooseAssignmentStep__content">
      <WorkflowDefaultAssignmentPicker
        class="tui-mod_approval-chooseAssignmentStep__picker"
        :value="{
          id: $context.selectedIdentifier,
          type: $context.selectedAssignmentType,
        }"
        :context-id="contextId"
        :can-view-orgframeworks="canViewOrgframeworks"
        :can-view-posframeworks="canViewPosframeworks"
        @input="handlePickerInput"
      />
    </div>
    <template v-slot:footer-content>
      <div class="tui-mod_approval-chooseAssignmentStep__buttons">
        <Button
          :text="$str('back', 'core')"
          @click="$send({ type: $e.BACK })"
        />
        <ButtonGroup>
          <Button
            :styleclass="{ primary: true }"
            :disabled="!$context.selectedIdentifier"
            :text="$str('button_create', 'mod_approval')"
            @click="$send({ type: $e.CREATE })"
          />
          <CancelButton @click="$send({ type: $e.CANCEL })" />
        </ButtonGroup>
      </div>
    </template>
  </ModalContent>
</template>

<script>
import { MOD_APPROVAL__WORKFLOW_CREATE } from 'mod_approval/constants';
import ModalContent from 'tui/components/modal/ModalContent';
import Button from 'tui/components/buttons/Button';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import CancelButton from 'tui/components/buttons/Cancel';
import WorkflowDefaultAssignmentPicker from 'mod_approval/components/workflow/WorkflowDefaultAssignmentPicker';

export default {
  components: {
    ModalContent,
    ButtonGroup,
    Button,
    CancelButton,
    WorkflowDefaultAssignmentPicker,
  },

  props: {
    contextId: Number,
    canViewOrgframeworks: Boolean,
    canViewPosframeworks: Boolean,
    ariaLink: String,
  },

  xState: {
    machineId: MOD_APPROVAL__WORKFLOW_CREATE,
  },

  methods: {
    handlePickerInput(assignment) {
      this.$send({
        type: this.$e.SELECT_ASSIGNMENT_TARGET,
        identifier: assignment.id,
        assignmentType: assignment.type,
      });
    },
  },
};
</script>

<style lang="scss">
.tui-mod_approval-chooseAssignmentStep {
  &__content {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    height: rem-px(500);
    padding-top: var(--gap-6);
  }

  &__picker {
    flex-grow: 1;
    min-height: rem-px(300);
  }

  &__buttons {
    display: flex;
    flex: 1;
    justify-content: space-between;
    max-height: rem-px(100);
  }
}
</style>
