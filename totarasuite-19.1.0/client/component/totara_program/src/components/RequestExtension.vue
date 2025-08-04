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

  @author Brian Barnes <brian.barnes@totara.com>
  @module tui
-->
<template>
  <div class="tui-totara_program__requestExtension">
    <Button
      v-if="!(requestSubmitted || extensionRequested)"
      :styleclass="{ transparent: true, stealth: true }"
      :text="$str('requestextension', 'totara_program')"
      @click="modalOpen = true"
    />
    <template v-else>
      {{ $str('pendingextension', 'totara_program') }}
    </template>
    <ModalPresenter :open="modalOpen" @request-close="modalOpen = false">
      <Modal>
        <ModalContent
          :title="$str('extensionrequest', 'totara_program')"
          :close-button="true"
        >
          <Uniform
            ref="extensionForm"
            :initial-values="formDefault"
            :validate="validateForm"
            @validation-changed="updateValidation"
            @submit="submit"
          >
            <FormRow
              v-slot="{ ariaDescribedbyId, labelId }"
              :label="$str('extenduntil', 'totara_program')"
              :is-stacked="false"
              :required="true"
            >
              <InputSet :label-id="labelId" :char-length="50">
                <FormDateSelector
                  name="date"
                  type="date"
                  :year-range-start="yearStart"
                />
                <span
                  class="tui-totara_program__requestExtension-dateSeparator"
                  >{{ $str('datepickerattime', 'totara_core') }}</span
                >
                <FormSelect
                  name="hour"
                  :options="hours"
                  value="0"
                  char-length="5"
                  :aria-describedby="ariaDescribedbyId"
                  :aria-label="$str('hour', 'core')"
                />
                <span class="tui-totara_program__requestExtension-dateSeparator"
                  >:</span
                >
                <FormSelect
                  name="minute"
                  :options="minutes"
                  value="0"
                  char-length="5"
                  :aria-describedby="ariaDescribedbyId"
                  :aria-label="$str('minutes', 'core')"
                />
              </InputSet>
            </FormRow>

            <FormRow
              :label="$str('reason', 'totara_program')"
              :is-stacked="false"
              :required="true"
            >
              <FormText name="reason" :validations="v => [v.required()]" />
            </FormRow>
            <input type="submit" class="hidden" />
          </Uniform>
          <template v-slot:buttons>
            <ButtonGroup class="tui-totara_program__requestExtension-buttons">
              <Button
                :text="$str('submit', 'core')"
                :disabled="!isValid"
                :styleclass="{ primary: true }"
                :loading="requestSubmitting"
                @click="$refs.extensionForm.submit()"
              />
              <Button
                :text="$str('cancel', 'core')"
                @click="modalOpen = false"
              />
            </ButtonGroup>
          </template>
        </ModalContent>
      </Modal>
    </ModalPresenter>
  </div>
</template>
<script>
import {
  Uniform,
  FormRow,
  FormDateSelector,
  FormSelect,
  FormText,
} from 'tui/components/uniform';
import InputSet from 'tui/components/form/InputSet';
import Button from 'tui/components/buttons/Button';
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';

import extensionRequest from 'totara_program/graphql/request_extension';

export default {
  components: {
    FormRow,
    FormDateSelector,
    FormSelect,
    FormText,
    InputSet,
    Button,
    Modal,
    ModalContent,
    ModalPresenter,
    ButtonGroup,
    Uniform,
  },

  props: {
    programId: [Number, String],
    currentDue: String,
    extensionRequested: Boolean,
  },

  emits: ['extension-requested'],

  data() {
    let hours = [];
    let minutes = [];
    for (let hour = 0; hour < 24; hour++) {
      hours.push({ id: hour, label: hour < 10 ? '0' + hour : hour });
    }

    for (let minute = 0; minute < 60; minute += 5) {
      minutes.push({ id: minute, label: minute < 10 ? '0' + minute : minute });
    }
    let d = new Date(this.currentDue * 1000);

    return {
      modalOpen: false,
      errors: {},
      isValid: false,
      requestSubmitting: false,
      requestSubmitted: false,
      yearStart: d.getFullYear(),
      hours,
      minutes,
      formDefault: {
        date: { iso: d.toISOString() },
        hour: d.getHours(),
        minute: d.getMinutes(),
      },
    };
  },

  methods: {
    /**
     * Submits the extension request
     *
     * @param {Object} values the forms values
     */
    submit(values) {
      this.validateForm(values);
      if (!this.isValid) {
        return;
      }
      this.requestSubmitting = true;
      let date = new Date(values.date.iso);
      date.setHours(values.hour);
      date.setMinutes(values.minute);

      this.$apollo
        .mutate({
          mutation: extensionRequest,
          variables: {
            input: {
              id: this.programId,
              extdatetime: date,
              extreason: values.reason,
            },
          },
        })
        .then(() => {
          this.modalOpen = false;
          this.requestSubmitted = true;
          this.$emit('extension-requested');
        })
        .finally(() => {
          this.requestSubmitting = false;
        });
    },

    /**
     * Updates the validation state of the form
     */
    updateValidation(val) {
      this.isValid = val.isValid;
    },

    /**
     * Validates the extension request
     *
     * @param {Object} values the forms values
     */
    validateForm(values) {
      let current = new Date(this.currentDue * 1000);
      let errors = {};
      if (!values.date) {
        errors.date = this.$str('error:invaliddate', 'totara_program');
        return errors;
      }
      let requested = new Date(values.date.iso);
      requested.setHours(values.hour);
      requested.setMinutes(values.minute);

      if (requested < Date.now()) {
        errors.date = this.$str('extensionearlierthannow', 'totara_program');
      }

      if (requested < current) {
        errors.date = this.$str(
          'extensionearlierthanduedate',
          'totara_program'
        );
      }

      return errors;
    },
  },
};
</script>
<style lang="scss">
.tui-totara_program__requestExtension {
  &-date {
    flex-wrap: wrap;
  }

  &-dateSeparator {
    display: inline-block;
    margin-top: var(--gap-2);
  }

  &-submit {
    display: none;
  }
}
</style>
