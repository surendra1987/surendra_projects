<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Kian Nguyen <kian.nguyen@totaralearning.com>
  @module container_workspace
-->
<template>
  <div>
    <LearningCard
      variant="background"
      :title="workspace.name"
      :href="workspace.url"
      :image="workspace.image"
    >
      <template v-slot:footer="{ popFront }">
        <div>
          <p
            v-if="!joined && hasRequestedToJoin"
            class="tui-originalSpaceCard__pendingText"
          >
            {{ $str('request_to_join_pending', 'container_workspace') }}
          </p>
          <LoadingButton
            v-if="!joined && canJoin"
            ref="actionButton"
            :class="popFront"
            :text="$str('join_me', 'container_workspace')"
            :aria-label="
              $str('join_space', 'container_workspace', workspace.name)
            "
            :loading="submitting"
            @click.stop="joinWorkspace"
          />

          <Button
            v-else-if="!joined && hasRequestedToJoin"
            ref="actionButton"
            :text="$str('cancel_request', 'container_workspace')"
            :class="popFront"
            :loading="submitting"
            @click.stop="cancelRequestJoinWorkspace"
          />

          <LoadingButton
            v-else-if="!joined && canRequestToJoin"
            ref="actionButton"
            :text="$str('request_to_join', 'container_workspace')"
            :class="popFront"
            :aria-label="
              $str(
                'request_to_join_space',
                'container_workspace',
                workspace.name
              )
            "
            :loading="submitting"
            @click.stop="modalOpen = true"
          />

          <!-- If the actor is an owner then this button will be disabled for them -->
          <Dropdown v-else-if="joined" context-mode="uncontained">
            <template v-slot:trigger="{ isOpen, toggle }">
              <Button
                ref="actionButton"
                :class="popFront"
                :text="$str('joined', 'container_workspace')"
                :caret="!owned"
                :aria-expanded="isOpen.toString()"
                :disabled="owned"
                @click.stop="toggle"
              />
            </template>

            <DropdownItem @click.stop.prevent="handleLeaveWorkspaceClick">
              {{ $str('leave_workspace', 'container_workspace') }}
            </DropdownItem>
          </Dropdown>
        </div>
      </template>
    </LearningCard>
    <ModalPresenter :open="modalOpen" @request-close="modalOpen = false">
      <WorkspaceRequestModal
        :title="$str('request_to_join', 'container_workspace')"
        :body-text="$str('request_to_join_text', 'container_workspace')"
        :button-text="$str('submit', 'core')"
        :is-saving="isSaving"
        @submit="requestToJoinWorkspace"
      />
    </ModalPresenter>

    <ConfirmationModal
      :open="showLeaveConfirm"
      :title="$str('leave_workspace', 'container_workspace')"
      :confirm-button-text="$str('leave', 'container_workspace')"
      :loading="submitting"
      @confirm="leaveWorkspace"
      @cancel="showLeaveConfirm = false"
    >
      {{ leaveConfirmMessage }}
    </ConfirmationModal>

    <InformationModal
      :title="$str('leave_workspace', 'container_workspace')"
      :open="showLeaveFailGroup"
      @close="showLeaveFailGroup = false"
    >
      {{
        $str(
          'leave_prevented_due_to_group_membership_message',
          'container_workspace'
        )
      }}
    </InformationModal>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import Dropdown from 'tui/components/dropdown/Dropdown';
import LearningCard from 'tui/components/card/LearningCard';
import LoadingButton from 'totara_engage/components/buttons/LoadingButton';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import InformationModal from 'tui/components/modal/InformationModal';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import WorkspaceRequestModal from 'container_workspace/components/modal/WorkspaceRequestModal';

// GraphQL queries
import joinWorkspace from 'container_workspace/graphql/join_workspace';
import requestToJoin from 'container_workspace/graphql/request_to_join';
import leaveWorkspace from 'container_workspace/graphql/leave_workspace';
import cancelMemberRequest from 'container_workspace/graphql/cancel_member_request';
import { PUBLIC } from 'container_workspace/access';

export default {
  components: {
    LearningCard,
    Button,
    LoadingButton,
    DropdownItem,
    Dropdown,
    ConfirmationModal,
    InformationModal,
    ModalPresenter,
    WorkspaceRequestModal,
  },

  props: {
    workspace: {
      type: Object,
      required: true,
    },
  },

  emits: ['join-workspace', 'leave-workspace', 'request-to-join-workspace'],

  data() {
    return {
      submitting: false,
      modalOpen: false,
      isSaving: false,
      showLeaveConfirm: false,
      showLeaveFailGroup: false,
    };
  },

  computed: {
    interactor() {
      return this.workspace.interactor;
    },

    joined() {
      return this.interactor.joined;
    },

    /**
     * Whether the actor is able to join the workspace or. For now, every other user is able to join
     * the workspace.
     */
    canJoin() {
      return this.interactor.can_join;
    },

    /**
     * Whether the actor is able to request to join the workspace or not.
     */
    canRequestToJoin() {
      return this.interactor.can_request_to_join;
    },

    /**
     * Whether the actor has already requested to join or not.
     */
    hasRequestedToJoin() {
      return this.interactor.has_requested_to_join;
    },

    /**
     * Whether the actor is an owner of this workspace.
     */
    owned() {
      return this.interactor.own;
    },

    leaveConfirmMessage() {
      return this.workspaceAccess === PUBLIC
        ? this.$str('leave_workspace_message', 'container_workspace')
        : this.$str(
            'leave_workspace_message_not_public',
            'container_workspace'
          );
    },

    /**
     * This should confirm if a modal is open on the page or not
     *
     * @returns {Boolean} whether there is a modal opened from this component
     */
    modalOpened() {
      return this.showLeaveFailGroup || this.showLeaveConfirm || this.modalOpen;
    },
  },

  watch: {
    /**
     * When a modal is closed this ensures the button on the workspace to re-gain focus
     *
     * @param {Boolean} newVal
     */
    modalOpened(newVal) {
      if (!newVal) {
        this.$refs.actionButton.$el.focus();
      }
    },
  },

  methods: {
    handleClick() {
      window.location.href = this.workspace.url;
    },

    async joinWorkspace() {
      this.submitting = true;

      try {
        const {
          data: { member },
        } = await this.$apollo.mutate({
          mutation: joinWorkspace,
          refetchAll: false,
          variables: {
            workspace_id: this.workspace.id,
          },
        });

        this.$emit('join-workspace', member);
      } finally {
        this.submitting = false;
      }
    },

    handleLeaveWorkspaceClick() {
      if (this.interactor.cannot_leave_reason === 'AUDIENCE_MEMBERSHIP') {
        this.showLeaveFailGroup = true;
      } else {
        this.showLeaveConfirm = true;
      }
    },

    async leaveWorkspace() {
      this.submitting = true;
      try {
        const {
          data: { member },
        } = await this.$apollo.mutate({
          mutation: leaveWorkspace,
          variables: {
            workspace_id: this.workspace.id,
          },
        });

        this.$emit('leave-workspace', member);
      } finally {
        this.submitting = false;
        this.showLeaveConfirm = false;
      }
    },

    /**
     *
     * @param {Object} formValue
     */
    async requestToJoinWorkspace(formValue) {
      if (!this.modalOpen) {
        return;
      }

      this.isSaving = true;
      try {
        const {
          data: { member_request },
        } = await this.$apollo.mutate({
          mutation: requestToJoin,
          refetchAll: false,
          variables: {
            workspace_id: this.workspace.id,
            request_content: formValue.messageContent,
          },
        });
        this.$emit('request-to-join-workspace', member_request);
      } finally {
        this.isSaving = false;
        this.modalOpen = false;
      }
    },

    async cancelRequestJoinWorkspace() {
      if (this.modalOpen || this.submitting) {
        return;
      }

      this.submitting = true;

      try {
        const {
          data: { member_request },
        } = await this.$apollo.mutate({
          mutation: cancelMemberRequest,
          variables: {
            workspace_id: this.workspace.id,
          },
        });

        this.$emit('request-to-join-workspace', member_request);
      } finally {
        this.submitting = false;
      }
    },
  },
};
</script>

<style lang="scss">
.tui-originalSpaceCard {
  &__pendingText {
    color: var(--color-neutral-1);
  }
}
</style>
