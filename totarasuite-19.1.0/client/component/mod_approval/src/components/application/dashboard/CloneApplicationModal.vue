<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Simon Chester <simon.chester@totara.com>
  @author Angela Kuznetsova <angela.kuznetsova@totara.com>
  @module mod_approval
-->
<template>
  <Modal :aria-labelledby="$id('title')">
    <ModalContent
      :title="$str('clone_application', 'mod_approval')"
      :title-id="$id('title')"
      class="tui-mod_approval-cloneApplicationModal"
    >
      <Loader v-if="$apollo.loading" loading />
      <template v-else>
        <FormRow
          v-slot="{ id }"
          :label="$str('select_job_assignment', 'mod_approval')"
        >
          <Select
            :id="id"
            v-model:value="selectedJobAssignmentId"
            class="tui-mod_approval-cloneApplicationModal__select"
            :options="jobAssignmentOptions"
            :show-label="true"
          />
        </FormRow>
      </template>
      <template v-slot:buttons>
        <ButtonGroup>
          <Button
            :text="$str('button_clone', 'mod_approval')"
            :styleclass="{ primary: true }"
            :disabled="!selectedJobAssignmentId"
            @click="create"
          />
          <Button
            :text="$str('button_cancel', 'mod_approval')"
            @click="$emit('request-close')"
          />
        </ButtonGroup>
      </template>
    </ModalContent>
  </Modal>
</template>

<script>
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import Loader from 'tui/components/loading/Loader';
import Button from 'tui/components/buttons/Button';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import FormRow from 'tui/components/form/FormRow';
import Select from 'tui/components/form/Select';

export default {
  components: {
    Modal,
    ModalContent,
    Loader,
    Button,
    ButtonGroup,
    FormRow,
    Select,
  },

  props: {
    workflowTypeId: String,
    applicantId: String,
    createNewApplicationMenu: Array,
  },

  emits: ['request-close', 'clone'],

  data() {
    return {
      selectedJobAssignmentId:
        this.createNewApplicationMenu[0] &&
        this.createNewApplicationMenu[0].job_assignment_id,
    };
  },

  computed: {
    jobAssignmentOptions() {
      return this.createNewApplicationMenu.map(jobAssignment => ({
        id: jobAssignment.job_assignment_id,
        label: jobAssignment.job_assignment,
      }));
    },
  },

  methods: {
    create() {
      this.$emit('clone', this.selectedJobAssignmentId);
    },
  },
};
</script>

<style lang="scss">
.tui-mod_approval-cloneApplicationModal {
  min-height: rem-px(250);

  &__select {
    margin-top: var(--gap-1);
  }
}
</style>
