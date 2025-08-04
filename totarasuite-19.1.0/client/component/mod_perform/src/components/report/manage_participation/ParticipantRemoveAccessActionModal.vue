<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2025 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @module mod_perform
-->

<template>
  <ModalPresenter :open="modalOpen" @request-close="modalClose">
    <Modal size="normal" :aria-labelledby="$id('report_access_remove')">
      <ModalContent
        :title="
          $str(
            !accessRemoved
              ? 'participant_instances_access_remove_title'
              : 'participant_instances_access_restore_title',
            'mod_perform'
          )
        "
        :title-id="$id('report_access_remove')"
      >
        <template v-if="!accessRemoved">
          <p>
            {{
              $str(
                'participant_instances_access_remove_message_line1',
                'mod_perform'
              )
            }}
          </p>

          <p>
            {{
              $str(
                'participant_instances_access_remove_message_line2',
                'mod_perform'
              )
            }}
          </p>
        </template>
        <template v-else>
          <p>
            {{
              $str(
                'participant_instances_access_restore_message_line1',
                'mod_perform'
              )
            }}
          </p>

          <p>
            {{
              $str(
                'participant_instances_access_restore_message_line2',
                'mod_perform'
              )
            }}
          </p>
        </template>

        <p>
          {{
            $str('participant_instances_access_message_confirm', 'mod_perform')
          }}
        </p>

        <template v-slot:buttons>
          <ButtonGroup>
            <Button
              :styleclass="{ primary: true }"
              :text="$str('button_continue', 'mod_perform')"
              @click="changeAvailability()"
            />
            <Button
              :text="$str('button_cancel', 'mod_perform')"
              @click="modalClose()"
            />
          </ButtonGroup>
        </template>
      </ModalContent>
    </Modal>
  </ModalPresenter>
</template>
<script>
import Button from 'tui/components/buttons/Button';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import Modal from 'tui/components/modal/Modal';
import ModalContent from 'tui/components/modal/ModalContent';
import ModalPresenter from 'tui/components/modal/ModalPresenter';

// Util
import { notify } from 'tui/notifications';
import { redirectWithPost } from 'tui/dom/form';

// GraphQL queries
import ChangeParticipantAccessMutation from 'mod_perform/graphql/remove_participant_instance_access';

export default {
  components: {
    Button,
    ButtonGroup,
    Modal,
    ModalContent,
    ModalPresenter,
  },

  props: {
    accessRemoved: { type: Boolean },
    modalOpen: { type: Boolean },
    participantInstanceId: { type: String },
    reportType: { type: String },
  },

  emits: ['modal-close'],

  methods: {
    modalClose() {
      this.$emit('modal-close');
    },

    /**
     * Show generic save/update error toast.
     */
    showErrorNotification() {
      notify({
        message: this.$str('toast_error_generic_update', 'mod_perform'),
        type: 'error',
      });
    },

    /**
     * Change participant instance access
     */
    async changeAvailability() {
      try {
        await this.$apollo.mutate({
          mutation: ChangeParticipantAccessMutation,
          variables: {
            input: {
              participant_instance_id: this.participantInstanceId,
              status: !this.accessRemoved ? 1 : 0,
            },
          },
        });

        this.$emit('modal-close');

        redirectWithPost(window.location, {
          is_access_removed: !this.accessRemoved,
          is_access_restored: this.accessRemoved,
          report_type: this.reportType,
        });
      } catch (e) {
        this.showErrorNotification();
      }
    },
  },
};
</script>
