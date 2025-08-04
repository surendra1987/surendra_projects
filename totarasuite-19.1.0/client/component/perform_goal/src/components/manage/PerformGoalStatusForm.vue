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
  @package perform_goal
-->

<template>
  <Uniform
    ref="form"
    class="tui-performGoalStatusForm"
    :initial-values="initialValues"
    :vertical="true"
    @submit="$emit('submit', $event)"
  >
    <FormRowStack>
      <!-- Progress -->
      <FormRow
        v-slot="{ id }"
        :label="$str('goal_form_update_label_progress', 'perform_goal')"
      >
        <InputSet char-length="full">
          <FormNumber
            :id="id"
            char-length="10"
            name="progress"
            :validations="v => [v.required(), v.min(0), v.max(99999)]"
          />
          <InputSizedText
            v-if="targetValueString"
            class="tui-performGoalStatusForm__progressDetails"
          >
            {{ targetValueString }}
          </InputSizedText>
        </InputSet>
      </FormRow>

      <!-- Status -->
      <FormRow
        v-slot="{ id }"
        :label="$str('goal_form_update_label_status', 'perform_goal')"
      >
        <FormSelect
          :id="id"
          char-length="15"
          :options="goalResponse.raw.available_statuses"
          name="status"
        />
      </FormRow>
    </FormRowStack>
  </Uniform>
</template>

<script>
import {
  FormNumber,
  FormRow,
  FormRowStack,
  FormSelect,
  Uniform,
} from 'tui/components/uniform';
import InputSet from 'tui/components/form/InputSet';
import InputSizedText from 'tui/components/form/InputSizedText';

export default {
  components: {
    FormNumber,
    FormRow,
    FormRowStack,
    FormSelect,
    InputSet,
    InputSizedText,
    Uniform,
  },

  props: {
    // Goal response data for form
    goalResponse: { type: Object, required: true },
  },

  emits: ['submit'],

  computed: {
    initialValues() {
      return {
        progress: this.goalResponse.goal
          ? parseFloat(this.goalResponse.goal.current_value)
          : '',
        status: this.goalResponse.goal ? this.goalResponse.goal.status.id : '',
      };
    },

    /**
     * Gets the current target value
     *
     * @return {String|null}
     */
    targetValueString() {
      if (!this.goalResponse.goal || !this.goalResponse.goal.target_value) {
        return null;
      }

      return this.$str(
        'goal_form_update_progress_target',
        'perform_goal',
        parseFloat(this.goalResponse.goal.target_value)
      );
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalStatusForm {
  margin-top: var(--gap-4);

  &__progressDetails {
    color: var(--color-neutral-6);
  }
}
</style>
