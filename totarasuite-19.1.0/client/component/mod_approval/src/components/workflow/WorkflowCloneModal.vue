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

  @author Simon Chester <simon.chester@totaralearning.com>
  @module mod_approval
-->
<template>
  <Modal
    :size="$matches('collectForm') ? 'normal' : 'large'"
    :dismissable="false"
  >
    <ModalContent
      v-if="$matches('collectForm')"
      :title="$str('clone_workflow', 'mod_approval')"
    >
      <div class="tui-mod_approval-workflowCloneModal__contentForm">
        <Uniform
          ref="form"
          :initial-values="initialValues"
          vertical
          @submit="handleFormSubmit"
        >
          <FormRow
            ref="nameRow"
            :label="$str('workflow_name', 'mod_approval')"
            required
          >
            <FormText
              name="name"
              char-length="full"
              :validations="v => [v.required()]"
            />
          </FormRow>

          <!-- make pressing enter work -->
          <input v-show="false" type="submit" />
        </Uniform>
      </div>

      <template v-slot:buttons>
        <Button
          :text="$str('next', 'core')"
          :styleclass="{ primary: true }"
          @click="$refs.form.submit()"
        />
        <Button :text="$str('cancel', 'core')" @click="$send($e.CANCEL)" />
      </template>
    </ModalContent>
    <ModalContent
      v-if="$matches('collectAssignment')"
      :title="$str('select_assignment', 'mod_approval')"
    >
      <div class="tui-mod_approval-workflowCloneModal__contentPicker">
        <WorkflowDefaultAssignmentPicker
          v-model:value="assignment"
          class="tui-mod_approval-workflowCloneModal__picker"
          :context-id="$selectors.getContextId($context)"
          :can-view-orgframeworks="canViewOrgframeworks"
          :can-view-posframeworks="canViewPosframeworks"
        />
      </div>

      <template v-slot:footer-content>
        <div class="tui-mod_approval-workflowCloneModal__footer">
          <Button :text="$str('back', 'core')" @click="$send($e.BACK)" />
          <ButtonGroup>
            <Button
              :text="$str('clone', 'mod_approval')"
              :styleclass="{ primary: true }"
              :disabled="!canSubmit"
              @click="handleAssignmentSubmit"
            />
            <Button :text="$str('cancel', 'core')" @click="$send($e.CANCEL)" />
          </ButtonGroup>
        </div>
      </template>
    </ModalContent>
  </Modal>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import { FormRow, FormText, Uniform } from 'tui/components/uniform';
import WorkflowDefaultAssignmentPicker from 'mod_approval/components/workflow/WorkflowDefaultAssignmentPicker';
import { MOD_APPROVAL__WORKFLOW_CLONE } from 'mod_approval/constants';

export default {
  components: {
    Button,
    ButtonGroup,
    Modal,
    ModalContent,
    FormRow,
    FormText,
    Uniform,
    WorkflowDefaultAssignmentPicker,
  },

  props: {
    canViewOrgframeworks: Boolean,
    canViewPosframeworks: Boolean,
  },

  data() {
    return {
      assignment: null,
    };
  },

  xState: {
    machineId: MOD_APPROVAL__WORKFLOW_CLONE,
  },

  computed: {
    initialValues() {
      return this.$context.clonedWorkflowDetails || {};
    },

    canSubmit() {
      return Boolean(this.assignment);
    },
  },

  mounted() {
    this.$nextTick(() => {
      const nameRow = this.$refs.nameRow;
      if (nameRow && nameRow.$el) {
        const name = nameRow.$el.querySelector('input[name=name]');
        if (name) {
          name.focus();
          name.select();
        }
      }
    });
  },

  methods: {
    handleFormSubmit(values) {
      if (
        this.$context.workflow.default_assignment.assignment_type === 'CONTEXT'
      ) {
        let cloneData = {
          name: values.name,
          default_assignment: {
            type: 'CONTEXT',
            id: this.$context.workflow.default_assignment.id,
          },
        };
        this.$send({ type: this.$e.CLONE, cloneData });
      } else {
        this.$send({ type: this.$e.NEXT, values });
      }
    },

    handleAssignmentSubmit() {
      this.$send({ type: this.$e.CLONE, assignment: this.assignment });
    },
  },
};
</script>

<style lang="scss">
.tui-mod_approval-workflowCloneModal {
  &__contentForm {
    padding: var(--gap-4) 0;
  }

  &__contentPicker {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    height: rem-px(500);
    padding-top: var(--gap-4);
  }

  &__picker {
    flex-grow: 1;
    min-height: 0;
  }

  &__footer {
    display: flex;
    flex-grow: 1;
    justify-content: space-between;
  }
}
</style>
