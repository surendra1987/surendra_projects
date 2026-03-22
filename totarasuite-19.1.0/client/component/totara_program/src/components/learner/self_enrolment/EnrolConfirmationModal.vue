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
  <ConfirmationModal
    :open="isOpen"
    :title="modalTitle"
    :confirm-button-text="confirmButtonText"
    :loading="isLoading"
    @confirm="handleEnrolmentState(selectedEnrolmentOption.id)"
    @cancel="$emit('close')"
  >
    <EnrolmentOptionCard :option="selectedEnrolmentOption" />
    <div class="tui-totara_program-enrolment-confirmation">
      <div v-if="isWithdrawing">
        <p>
          {{ withdrawEffectText }}
        </p>
        <p v-if="!selectedEnrolmentOption?.can_self_enrol">
          {{
            $str(
              'notallowedreenrol',
              'totara_program',
              selectedEnrolmentOption?.name
            )
          }}
        </p>
      </div>
      <div v-else>
        <p v-if="!selectedEnrolmentOption?.can_self_unenrol">
          {{
            $str(
              'notallowedwithdraw',
              'totara_program',
              selectedEnrolmentOption.type
            )
          }}
        </p>
      </div>
      <p>
        {{ confirmationText }}
      </p>
    </div>
  </ConfirmationModal>
</template>

<script>
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import EnrolmentOptionCard from 'totara_program/components/learner/self_enrolment/EnrolmentOptionCard';
import { notifyParams } from 'tui/notifications';
// Mutations
import SelfEnrolMutation from 'totara_program/graphql/group_self_enrol';
import SelfUnenrolMutation from 'totara_program/graphql/group_self_unenrol';

export default {
  components: {
    ConfirmationModal,
    EnrolmentOptionCard,
  },

  props: {
    currentEnrolmentOptions: [Object],
    isEnrolled: Boolean,
    isOpen: Boolean,
    programName: String,
    selectedEnrolmentOption: Object,
    typeString: String,
  },

  emits: ['close'],

  data() {
    return {
      isLoading: false,
    };
  },

  computed: {
    isWithdrawing() {
      // If the user is enrolled in this option we must be withdrawing
      return this.selectedEnrolmentOption?.is_enrolled;
    },
    confirmationText() {
      if (this.isEnrolled) {
        if (this.isWithdrawing) {
          return this.$str(
            'areyousureprogramwithdrawmessage',
            'totara_program',
            {
              group: this.selectedEnrolmentOption?.name,
              program: this.programName,
            }
          );
        }

        return this.$str(
          'areyousureaddprogramenrolmentmessage',
          'totara_program',
          {
            group: this.selectedEnrolmentOption?.name,
            program: this.programName,
          }
        );
      }

      return this.$str('areyousureprogramenrolmentmessage', 'totara_program', {
        group: this.selectedEnrolmentOption?.name,
        program: this.programName,
      });
    },
    enrolledAssignments() {
      return this.currentEnrolmentOptions?.filter(
        assignment => assignment.is_enrolled
      );
    },
    withdrawEffectText() {
      if (this.enrolledAssignments?.length > 1) {
        return this.$str(
          'unaffectedenrolments',
          'totara_program',
          this.typeString
        );
      }

      return this.$str('removeenrolment', 'totara_program', this.typeString);
    },
    modalTitle() {
      if (this.isWithdrawing) {
        return this.enrolledAssignments?.length > 1
          ? this.$str(
              'withdrawfrom',
              'totara_program',
              this.selectedEnrolmentOption?.type
            )
          : this.$str('withdrawfrom', 'totara_program', this.typeString);
      }

      return this.isEnrolled
        ? this.$str(
            'enrolin',
            'totara_program',
            this.selectedEnrolmentOption?.type
          )
        : this.$str('enrolin', 'totara_program', this.typeString);
    },
    confirmButtonText() {
      return this.isWithdrawing
        ? this.$str('withdraw', 'totara_program')
        : this.$str('enrol', 'enrol');
    },
  },

  methods: {
    async handleEnrolmentState(id) {
      let message = this.$str('youhavebeenenrolled', 'totara_program', {
        type: this.typeString,
        fullname: this.programName,
      });

      if (this.isWithdrawing) {
        message = this.$str('youhavebeenremoved', 'totara_program', {
          type: this.typeString,
          fullname: this.programName,
        });

        if (this.enrolledAssignments.length >= 2) {
          message = this.$str(
            'youhavebeenremovedgroup',
            'totara_program',
            this.selectedEnrolmentOption.name
          );
        }
      }

      try {
        this.isLoading = true;
        let response = await this.$apollo
          .mutate({
            mutation: this.isWithdrawing
              ? SelfUnenrolMutation
              : SelfEnrolMutation,
            variables: {
              input: {
                assignment_id: id,
              },
            },
          })
          .then(response => response.data);

        if (response.result.success) {
          window.location = this.$url(window.location.href, {
            ...notifyParams({
              duration: 5000,
              message: message,
              type: 'success',
            }),
          });
        } else {
          this.isLoading = false;
        }
      } catch (e) {
        console.error(e);
      }
    },
  },
};
</script>

<style scoped lang="scss">
.totara_program-enrolment-confirmation-modal__description {
  padding-bottom: var(--gap-4);
}
</style>
