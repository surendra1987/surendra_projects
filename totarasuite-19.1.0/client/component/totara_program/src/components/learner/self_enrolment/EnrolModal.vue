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
  <Button
    v-if="showEnrolButton"
    :text="$str('enrol', 'enrol')"
    variant="primary"
    @click="handleModalState"
  />
  <Dropdown v-else-if="enrolmentOptions.length > 0">
    <template v-slot:trigger="{ toggle, isOpen }">
      <MoreIcon
        :aria-expanded="isOpen ? 'true' : 'false'"
        :aria-label="$str('actions_for', 'totara_program', programName)"
        :size="300"
        @click="toggle"
      />
    </template>
    <DropdownItem
      v-if="enrolmentOptions && enrolmentOptions.length"
      :title="$str('enrolmentdetails', 'totara_program')"
      :aria-label="
        $str('enrolmentdetailsaction', 'totara_program', programName)
      "
      @click="handleModalState"
    >
      {{ $str('enrolmentdetails', 'totara_program') }}
    </DropdownItem>
  </Dropdown>
  <EnrolmentOptionsModal
    :date-assigned="dateAssigned"
    :due-date="dueDate"
    :enrolment-options="enrolmentOptions"
    :is-enrolled="isEnrolled"
    :is-open="isInformationModalOpen"
    :type-string="typeString"
    :withdraw-string="withdrawString"
    @close="closeInformationModalOpen"
    @set-enrolment-option="setEnrolmentOption"
  />
  <EnrolConfirmationModal
    :current-enrolment-options="applicableAssignments"
    :is-enrolled="isEnrolled"
    :is-open="isConfirmationModalOpen"
    :program-name="programName"
    :selected-enrolment-option="selectedEnrolmentOption"
    :type-string="typeString"
    @close="closeConfirmationModal"
  />
</template>

<script>
import Button from 'tui/components/buttons/Button';
import EnrolConfirmationModal from 'totara_program/components/learner/self_enrolment/EnrolConfirmationModal';
import EnrolmentOptionsModal from 'totara_program/components/learner/self_enrolment/EnrolmentOptionsModal';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import MoreIcon from 'tui/components/buttons/MoreIcon';

export default {
  components: {
    Button,
    EnrolConfirmationModal,
    EnrolmentOptionsModal,
    Dropdown,
    DropdownItem,
    MoreIcon,
  },

  props: {
    programId: [Number, String],
    programName: {
      type: String,
      required: true,
    },
    showEnrolButton: Boolean,
    isEnrolled: Boolean,
    canEdit: Boolean,
    isCertification: Boolean,
    applicableAssignments: [Object],
    dueDate: String,
    dateAssigned: String,
    withdrawString: String,
    typeString: String,
  },

  data() {
    return {
      isConfirmationModalOpen: false,
      isInformationModalOpen: false,
      selectedEnrolmentOption: null,
      showToast: false,
    };
  },

  computed: {
    enrolmentOptions() {
      const filteredOptions = this.applicableAssignments?.filter(option => {
        return option.can_self_enrol || option.is_enrolled;
      });

      const optionsOrder = { true: 1, null: 2, false: 3 };
      return filteredOptions?.sort(
        (a, b) => optionsOrder[a.is_enrolled] - optionsOrder[b.is_enrolled]
      );
    },
  },

  methods: {
    handleModalState() {
      if (this.enrolmentOptions.length === 1 && !this.isEnrolled) {
        this.selectedEnrolmentOption = this.enrolmentOptions[0];
        this.isConfirmationModalOpen = true;
      } else {
        this.isInformationModalOpen = true;
      }
    },
    openConfirmationModal() {
      this.isInformationModalOpen = false;
      this.isConfirmationModalOpen = true;
    },
    closeConfirmationModal() {
      this.isConfirmationModalOpen = false;
    },
    closeInformationModalOpen() {
      this.isInformationModalOpen = false;
    },
    setEnrolmentOption(id) {
      this.selectedEnrolmentOption = this.enrolmentOptions.find(
        option => option.id === id
      );

      this.openConfirmationModal();
    },
  },
};
</script>
