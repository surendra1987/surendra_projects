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
  @module totara_perform
-->

<template>
  <!-- Response attribution and visibility -->
  <div class="tui-performPASettingVisibility">
    <h2 class="tui-performPASettingVisibility__heading">
      {{ $str('manage_setting_heading_visibility', 'mod_perform') }}
    </h2>

    <FormRowStack spacing="large">
      <!-- Anonymise responses -->
      <FormRow
        :helpmsg="
          $str('activity_general_anonymous_responses_label_help', 'mod_perform')
        "
        :label="
          $str('activity_general_anonymous_responses_label', 'mod_perform')
        "
      >
        <div
          class="tui-performPASettingVisibility__anonymous"
          :class="{
            'tui-performPASettingVisibility__anonymous--static': !data
              .anonymous_responses.mutable,
          }"
        >
          <span v-if="!data.anonymous_responses.mutable">
            {{ anonymousStaticValue }}
          </span>
          <ToggleSwitch
            v-else
            v-model:value="anonymousValue"
            :aria-label="
              $str('activity_general_anonymous_responses_label', 'mod_perform')
            "
            :disabled="saving"
            :toggle-first="true"
            @input="anonymityValueChanged"
          />
        </div>
      </FormRow>

      <!-- Response visibility -->
      <FormRow
        v-slot="{ labelId }"
        :label="$str('visibility_condition_response_label', 'mod_perform')"
        :helpmsg="$str('visibility_condition_label_help', 'mod_perform')"
      >
        <div>
          <RadioGroup
            v-model:value="visibilityValue"
            :aria-labelledby="labelId"
            @input="responseVisibilityChanged"
          >
            <Radio
              v-for="item in visibilityConditionOptions"
              :key="item.value"
              :disabled="
                saving || (anonymousValue && item.value !== visibilityAnonValue)
              "
              :value="item.value"
            >
              {{ item.name }}
            </Radio>
          </RadioGroup>
        </div>
      </FormRow>

      <FormRow v-if="!validClosureState">
        <div class="tui-performPASettingVisibility__warning">
          <NotificationBanner
            :message="
              $str(
                'visibility_condition_status_mismatch_warning',
                'mod_perform'
              )
            "
            type="warning"
          />
        </div>
      </FormRow>
    </FormRowStack>
  </div>
</template>

<script>
import FormRow from 'tui/components/form/FormRow';
import FormRowStack from 'tui/components/form/FormRowStack';
import NotificationBanner from 'tui/components/notifications/NotificationBanner';
import { notify } from 'tui/notifications';
import Radio from 'tui/components/form/Radio';
import RadioGroup from 'tui/components/form/RadioGroup';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';

// graphQL
import updateVisibilitySetting from 'mod_perform/graphql/update_activity_visibility_settings';

export default {
  components: {
    FormRow,
    FormRowStack,
    NotificationBanner,
    Radio,
    RadioGroup,
    ToggleSwitch,
  },

  props: {
    activityId: { required: true, type: [Number, String] },
    data: { required: true, type: Object },
  },

  emits: ['updated'],

  data() {
    return {
      // Value for anonymous setting
      anonymousValue: this.data.anonymous_responses.value,
      // Currently saving the setting
      saving: false,
      // Have a valid closure state for the current visibility values
      validClosureState: this.data.valid_closure_state,
      // Value to set visibility to when anonymous is enabled
      visibilityAnonValue: this.data.visibility_condition.anonymous_value,
      // Visibility options
      visibilityConditionOptions: this.data.visibility_condition.options,
      // Value for visibility setting
      visibilityValue: this.data.visibility_condition.value,
    };
  },

  computed: {
    /**
     * Text to display instead of anonymous toggle on active activities
     *
     * @return {boolean}
     */
    anonymousStaticValue() {
      return this.anonymousValue
        ? this.$str('boolean_setting_text_enabled', 'mod_perform')
        : this.$str('boolean_setting_text_disabled', 'mod_perform');
    },
  },

  watch: {
    data: {
      deep: true,
      handler(values) {
        this.anonymousValue = values.anonymous_responses.value;
        this.visibilityValue = values.visibility_condition.value;
        this.validClosureState = values.valid_closure_state;
      },
    },
  },

  methods: {
    /**
     * Set visibility condition edit/read-only mode base on the setting of anonymise responses
     */
    anonymityValueChanged() {
      if (this.anonymousValue) {
        this.visibilityValue = this.visibilityAnonValue;
      }

      this.updateSetting();
    },

    /**
     * Response visibility value has changed
     */
    responseVisibilityChanged() {
      this.updateSetting();
    },

    /**
     * Handle the submission of setting value changes
     *
     */
    async updateSetting() {
      try {
        this.saving = true;
        const result = await this.$apollo.mutate({
          mutation: updateVisibilitySetting,
          variables: {
            input: {
              activity_id: this.activityId,
              anonymous_responses: this.anonymousValue,
              visibility_condition: this.visibilityValue,
            },
          },
        });

        this.saving = false;

        if (result) {
          this.$emit('updated');
        }
      } catch (e) {
        this.saving = false;
        // Error notification
        notify({
          message: this.$str('toast_error_generic_update', 'mod_perform'),
          type: 'error',
        });
      }
    },
  },
};
</script>

<style lang="scss">
.tui-performPASettingVisibility {
  display: flex;
  flex-direction: column;
  gap: var(--gap-4);

  &__heading {
    @include font(h4);
    margin: 0;
  }

  &__anonymous {
    padding-top: tui-input-toggle-v-padding();

    &--static {
      padding-top: tui-input-v-padding-borderless();
    }
  }

  &__warning {
    max-width: 712px;
  }
}
</style>
