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
  <div class="mod_aproval-workflowDefaultAssignmentPicker">
    <FormRow
      v-slot="{ id }"
      :label="$str('assignment_type', 'mod_approval')"
      vertical
    >
      <Select
        :id="id"
        :value="type"
        :options="assignmentTypes"
        char-length="15"
        @input="handleTypeChange"
      />
    </FormRow>

    <component
      :is="typePicker"
      :key="type"
      v-bind="typePickerProps"
      class="mod_aproval-workflowDefaultAssignmentPicker__picker"
      :value="entryId"
      :disabled-ids="disabledIds"
      :context-id="contextId"
      :force-loading="forceLoading"
      @input="handlePickerChange"
    />
  </div>
</template>

<script>
import FormRow from 'tui/components/form/FormRow';
import Select from 'tui/components/form/Select';
import { AssignmentType } from 'mod_approval/constants';
import OrganisationPicker from 'mod_approval/components/browse_picker/OrganisationPicker';
import PositionPicker from 'mod_approval/components/browse_picker/PositionPicker';
import AudiencePicker from 'mod_approval/components/browse_picker/AudiencePicker';

const typePickers = {
  [AssignmentType.ORGANISATION]: OrganisationPicker,
  [AssignmentType.POSITION]: PositionPicker,
  [AssignmentType.COHORT]: AudiencePicker,
};

export default {
  components: {
    FormRow,
    Select,
    OrganisationPicker,
    PositionPicker,
    AudiencePicker,
  },

  props: {
    contextId: Number,
    disabledIds: Array,
    forceLoading: Boolean,
    value: {
      type: Object,
    },
    canViewOrgframeworks: {
      type: Boolean,
      default: true,
    },
    canViewPosframeworks: {
      type: Boolean,
      default: true,
    },
  },

  emits: ['input', 'update:value'],

  data() {
    let assignmentTypes = [
      {
        id: AssignmentType.COHORT,
        label: this.$str('model_assignment_type_cohort', 'mod_approval'),
      },
    ];

    if (this.canViewOrgframeworks) {
      assignmentTypes.push({
        id: AssignmentType.ORGANISATION,
        label: this.$str('model_assignment_type_organisation', 'mod_approval'),
      });
    }
    if (this.canViewPosframeworks) {
      assignmentTypes.push({
        id: AssignmentType.POSITION,
        label: this.$str('model_assignment_type_position', 'mod_approval'),
      });
    }

    return {
      step: 'form',
      type: AssignmentType.COHORT,
      entryId: null,
      assignmentTypes: assignmentTypes,
    };
  },

  computed: {
    typePicker() {
      return typePickers[this.type];
    },

    typePickerProps() {
      const props = {};
      if (this.type === AssignmentType.COHORT) {
        props.contextId = this.contextId;
      }
      return props;
    },
  },

  watch: {
    value: {
      handler(value) {
        // don't set type if value is unset, otherwise we can't change type
        if (value) {
          this.type = value.type;
        }
        this.entryId = value && value.id;
      },
      immediate: true,
    },
  },

  methods: {
    handleTypeChange(value) {
      this.entryId = null;
      this.type = value;
      this.$emit('update:value', { type: this.type });
      this.$emit('input', { type: this.type });
    },

    handlePickerChange(value) {
      this.entryId = value;
      this.$emit('update:value', { type: this.type, id: this.entryId });
      this.$emit('input', { type: this.type, id: this.entryId });
    },
  },
};
</script>

<style lang="scss">
.mod_aproval-workflowDefaultAssignmentPicker {
  display: flex;
  flex-direction: column;

  &__picker {
    flex-grow: 1;
    min-height: 0;
    margin-top: var(--gap-6);
  }
}
</style>
