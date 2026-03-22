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

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @package perform_goal
-->

<template>
  <Uniform
    ref="form"
    class="tui-performGoalForm"
    :initial-values="initialValues"
    input-width="full"
    validation-mode="submit"
    :vertical="true"
    @submit="$emit('submit', $event)"
  >
    <FormRowStack>
      <!-- Name -->
      <FormRow
        v-slot="{ id }"
        :label="$str('goal_form_label_name', 'perform_goal')"
        required
      >
        <FormText
          :id="id"
          char-length="30"
          name="name"
          :validations="v => [v.required(), v.maxLength(1024)]"
        />
      </FormRow>

      <!-- Description -->
      <FormRow
        v-slot="{ labelId }"
        :full-width="true"
        :label="$str('goal_form_label_description', 'perform_goal')"
      >
        <FormField v-slot="{ value, update }" name="description">
          <Editor
            class="tui-performGoalForm__descriptionEditor"
            :aria-labelledby="labelId"
            :default-format="jsonFormat"
            :lock-format="true"
            :value="value"
            variant="description"
            @input="update"
          />
        </FormField>
        <div class="tui-performGoalForm__descriptionMessage">
          {{ $str('goal_form_description_message', 'perform_goal') }}
        </div>
      </FormRow>

      <!-- Measurement -->
      <FormRow
        v-slot="{ id }"
        :helpmsg="$str('goal_form_label_measurement_helpmsg', 'perform_goal')"
        :label="$str('goal_form_label_measurement', 'perform_goal')"
        required
      >
        <div class="tui-performGoalForm__measurement">
          <!-- Target -->
          <FormRow
            :label="$str('goal_form_label_target_value', 'perform_goal')"
            subfield
          >
            <FormNumber
              :id="id"
              char-length="5"
              name="target_value"
              :validations="v => [v.required(), v.min(0.00001), v.max(99999)]"
            />
          </FormRow>
        </div>
      </FormRow>

      <!-- Timeframe -->
      <FormScope path="dates" :validate="dateValidator">
        <FormRow
          v-slot="{ id }"
          :label="$str('goal_form_label_timeframe', 'perform_goal')"
        >
          <div class="tui-performGoalForm__timeframe">
            <!-- Start -->
            <FormRow
              :label="$str('goal_form_label_timeframe_start', 'perform_goal')"
              subfield
            >
              <FormDateSelector
                :id="id"
                name="start_date"
                type="date"
                :initial-current-date="!initialValues.dates.start_date"
                :validations="v => [v.required()]"
              />
            </FormRow>

            <!-- Due -->
            <FormRow
              :label="$str('goal_form_label_timeframe_due', 'perform_goal')"
              subfield
            >
              <FormDateSelector
                :id="id"
                name="target_date"
                type="date"
                :initial-current-date="!initialValues.dates.target_date"
                :validations="v => [v.required()]"
              />
            </FormRow>
          </div>
        </FormRow>
      </FormScope>
    </FormRowStack>
  </Uniform>
</template>

<script>
import Editor from 'tui/components/editor/Editor';
import { EditorContent } from 'tui/editor';

// Util
import { Format } from 'tui/editor';
import { isIsoAfter } from 'tui/date';

import {
  FormDateSelector,
  FormField,
  FormNumber,
  FormRow,
  FormRowStack,
  FormScope,
  FormText,
  Uniform,
} from 'tui/components/uniform';

export default {
  components: {
    Editor,
    FormDateSelector,
    FormField,
    FormNumber,
    FormRow,
    FormRowStack,
    FormScope,
    FormText,
    Uniform,
  },

  props: {
    // The data for this goal
    goalData: { type: Object },
    // The raw data for this goal
    rawGoalData: { type: Object },
  },

  emits: ['submit'],

  data() {
    return {
      initialValues: {
        description: this.rawGoalData
          ? this.rawGoalData.description instanceof EditorContent
            ? this.rawGoalData.description
            : new EditorContent({
                content: this.rawGoalData.description,
              })
          : null,
        name: this.goalData ? this.goalData.name : '',
        dates: {
          start_date: this.rawGoalData ? this.rawGoalData.start_date : null,
          target_date: this.rawGoalData ? this.rawGoalData.target_date : null,
        },
        target_value: this.goalData
          ? parseFloat(this.goalData.target_value)
          : 100,
      },
      jsonFormat: Format.JSON_EDITOR,
    };
  },

  methods: {
    /**
     * If start date is strictly greater than due date, then validation error
     *
     * @param {Object} values Object containing the start and due dates
     * @return {Object}
     */
    dateValidator(values) {
      const errors = {};

      if (
        values.start_date.iso !== values.target_date.iso &&
        isIsoAfter(values.start_date.iso, values.target_date.iso)
      ) {
        errors.start_date = this.$str(
          'goal_form_start_date_error',
          'perform_goal'
        );
      }

      return errors;
    },
  },
};
</script>

<style lang="scss">
.tui-performGoalForm {
  &__timeframe {
    display: flex;
    flex-direction: column;
    gap: var(--gap-3);
  }

  &__descriptionMessage {
    @include font(body-sm);
    color: var(--color-neutral-6);
  }

  &__measurement {
    display: flex;
    flex-direction: row;
    gap: var(--gap-3);
  }

  &__descriptionEditor {
    max-height: 45vh;
  }

  @media (min-width: $tui-screen-xs) {
    &__timeframe {
      flex-direction: row;
    }
  }
}
</style>
