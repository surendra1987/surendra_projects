<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Timothy Liew <timothy.liew@totara.com>
  @module totara_program
-->

<template>
  <InformationModal
    :open="isOpen"
    :title="
      isEnrolled
        ? $str('enrolmentdetails', 'totara_program')
        : $str('enrolmentoptions', 'totara_program')
    "
    @close="$emit('close')"
  >
    <div class="tui-totara_program-enrolment-details">
      <p v-if="!isEnrolled">
        {{ $str('selectanoptiontoenrol', 'totara_program', typeString) }}
      </p>
      <span v-if="dateAssigned">
        <span class="tui-totara_program-enrolment-details__label"
          >{{ $str('dateassigned', 'totara_program') }}:</span
        >
        {{ dateAssigned }}
      </span>
      <span v-if="dueDate">
        <span class="tui-totara_program-enrolment-details__label"
          >{{ $str('duedate', 'totara_program') }}:</span
        >
        {{ dueDate }}
      </span>
      <span v-if="withdrawString">
        {{ withdrawString }}
      </span>
    </div>
    <EnrolmentOptionsWrapper
      v-if="showEnrolmentDetails"
      :title="$str('yourenrolments', 'totara_program')"
      :enrolment-options="enrolledOptions"
      @set-selected-option="$emit('setEnrolmentOption', $event)"
    />
    <EnrolmentOptionsWrapper
      :title="
        showOtherEnrolmentOptions
          ? $str('otherenrolmentoptions', 'totara_program')
          : ''
      "
      :enrolment-options="unenrolledOptions"
      @set-selected-option="$emit('setEnrolmentOption', $event)"
    />
  </InformationModal>
</template>

<script>
import EnrolmentOptionsWrapper from 'totara_program/components/learner/self_enrolment/EnrolmentOptionsWrapper';
import InformationModal from 'tui/components/modal/InformationModal';

export default {
  components: {
    EnrolmentOptionsWrapper,
    InformationModal,
  },

  props: {
    dateAssigned: String,
    dueDate: String,
    enrolmentOptions: [Object],
    isEnrolled: Boolean,
    isOpen: Boolean,
    typeString: String,
    withdrawString: String,
  },

  emits: ['setEnrolmentOption', 'close'],

  computed: {
    enrolledOptions() {
      return this.enrolmentOptions.filter(option => option.is_enrolled);
    },
    unenrolledOptions() {
      return this.enrolmentOptions.filter(option => !option.is_enrolled);
    },
    showOtherEnrolmentOptions() {
      return (
        this.unenrolledOptions.length > 0 && this.enrolledOptions.length > 0
      );
    },
    showEnrolmentDetails() {
      return this.enrolledOptions.length > 0;
    },
  },
};
</script>

<style scoped lang="scss">
.tui-totara_program-enrolment {
  &-details {
    display: flex;
    flex-direction: column;
    gap: var(--gap-2);

    &__label {
      font-weight: bold;
    }
  }
}
</style>
