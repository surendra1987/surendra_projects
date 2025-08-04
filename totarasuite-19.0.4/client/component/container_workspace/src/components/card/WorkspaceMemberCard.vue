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
  <article
    class="tui-workspaceMemberCard"
    :aria-describedby="owner ? $id('lozenge') : false"
  >
    <MiniProfileCard
      :display="userCardDisplay"
      :hide-drop-down="!hasActions"
      :label-id="labelId"
      :drop-down-button-aria-label="
        $str('more_action_for_member', 'container_workspace', userFullName)
      "
      :aria-describedby="owner ? $id('lozenge') : null"
      class="tui-workspaceMemberCard__profileCard"
    >
      <template v-slot:tag>
        <div class="tui-workspaceMemberCard__tagRow">
          <Popover
            v-if="audiences && audiences.length > 0"
            position="right"
            :triggers="['click']"
            trigger-valign="center"
          >
            <template v-slot:trigger>
              <ButtonIcon
                class="tui-workspaceMemberCard__profileCard-audiencesIcon"
                :aria-label="
                  $str(
                    'audiences_for_member_x',
                    'container_workspace',
                    userFullName
                  )
                "
                :styleclass="{ transparentNoPadding: true }"
                :title="false"
              >
                <UsersIcon />
              </ButtonIcon>
            </template>

            <template v-slot:default>
              <p>
                {{
                  $str(
                    'member_added_through_audiences_label',
                    'container_workspace'
                  )
                }}
              </p>
              <ul>
                <li v-for="audience in audiences" :key="audience.id">
                  {{ audience.name }}
                </li>
              </ul>
            </template>
          </Popover>
        </div>
      </template>

      <template v-if="owner" v-slot:side-content>
        <div class="tui-workspaceMemberCard__ownerLabel">
          {{ $str('owner', 'container_workspace') }}
        </div>
      </template>

      <template v-if="hasActions" v-slot:drop-down-items>
        <!-- Add as owner -->
        <DropdownButton
          v-if="canSetAsOwner"
          @click="openConfirmOwnerModal = true"
        >
          {{ $str('set_member_as_owner', 'container_workspace') }}
        </DropdownButton>
        <!-- Remove as owner -->
        <DropdownButton
          v-else-if="canRemoveAsOwner"
          @click="openRemoveOwnerModal = true"
        >
          {{ $str('remove_member_as_owner', 'container_workspace') }}
        </DropdownButton>
        <!-- Remove from workspace -->
        <DropdownButton v-if="deleteAble" @click="modal.confirm = true">
          {{ $str('remove_member_from_workspace', 'container_workspace') }}
        </DropdownButton>
      </template>
    </MiniProfileCard>

    <ConfirmationModal
      :open="modal.confirm"
      :title="$str('remove_member_title_warning_msg', 'container_workspace')"
      :confirm-button-text="$str('remove', 'core')"
      :loading="removing"
      @confirm="removeMember"
      @cancel="modal.confirm = false"
    >
      <p>
        {{
          $str('remove_member_warning_msg', 'container_workspace', userFullName)
        }}
      </p>
    </ConfirmationModal>

    <!-- Confirm add as owner -->
    <ConfirmationModal
      :confirm-button-text="$str('continue', 'core')"
      :loading="saving"
      :open="openConfirmOwnerModal"
      :title="$str('set_member_as_owner_modal_title', 'container_workspace')"
      @cancel="openConfirmOwnerModal = false"
      @confirm="addAsOwner"
    >
      <p>
        {{
          $str(
            'set_member_as_owner_modal_body',
            'container_workspace',
            userFullName
          )
        }}
      </p>
      <p>
        {{
          $str('set_member_as_owner_modal_body_confirm', 'container_workspace')
        }}
      </p>
    </ConfirmationModal>

    <!-- Confirm remove as owner -->
    <ConfirmationModal
      :confirm-button-text="$str('continue', 'core')"
      :loading="saving"
      :open="openRemoveOwnerModal"
      :title="$str('remove_member_as_owner_modal_title', 'container_workspace')"
      @cancel="openRemoveOwnerModal = false"
      @confirm="removeAsOwner"
    >
      <p>
        {{
          $str(
            'remove_member_as_owner_modal_body',
            'container_workspace',
            userFullName
          )
        }}
      </p>
      <p>
        {{
          $str(
            'remove_member_as_owner_modal_body_confirm',
            'container_workspace'
          )
        }}
      </p>
    </ConfirmationModal>
  </article>
</template>

<script>
import { config } from 'tui/config';
import { notify } from 'tui/notifications';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import DropdownButton from 'tui/components/dropdown/DropdownButton';
import UsersIcon from 'tui/components/icons/Users';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import Popover from 'tui/components/popover/Popover';
import MiniProfileCard from 'tui/components/profile/MiniProfileCard';

// GraphQL
import removeMemberAsOwner from 'container_workspace/graphql/remove_owners';
import removeMemberFromWorkspace from 'container_workspace/graphql/remove_member_from_workspace';
import setMemberAsOwner from 'container_workspace/graphql/add_owners';
import getWorkspace from 'container_workspace/graphql/get_workspace';

export default {
  components: {
    ButtonIcon,
    DropdownButton,
    UsersIcon,
    ConfirmationModal,
    Popover,
    MiniProfileCard,
  },

  props: {
    userFullName: {
      type: String,
      required: true,
    },

    userCardDisplay: {
      type: Object,
      required: true,
    },

    // Can remove this member as a owner
    canRemoveAsOwner: { type: Boolean },

    // Can set this member as a owner
    canSetAsOwner: { type: Boolean },

    deleteAble: {
      type: Boolean,
      default: false,
    },

    /**
     * The user's id of a member.
     */
    userId: {
      type: [String, Number],
      required: true,
    },

    workspaceId: {
      type: [String, Number],
      required: true,
    },

    workspaceName: { type: String, required: true },

    labelId: {
      type: String,
      required: true,
    },

    owner: Boolean,

    /** @type {import('vue').PropType<?Array<{ id: any, name: string }>>} */
    audiences: Array,
  },

  emits: ['remove-as-owner', 'remove-member', 'set-as-owner'],

  data() {
    return {
      removing: false,
      modal: {
        confirm: false,
      },
      openConfirmOwnerModal: false,
      openRemoveOwnerModal: false,
      saving: false,
    };
  },

  computed: {
    /*
     * Does the member have any actions the current user can trigger
     *
     * @return {Boolean}
     */
    hasActions() {
      return this.deleteAble || this.canSetAsOwner || this.canRemoveAsOwner;
    },
  },

  methods: {
    /**
     * Handle the request for setting member as an owner
     *
     */
    async addAsOwner() {
      try {
        this.saving = true;

        const result = await this.$apollo.mutate({
          mutation: setMemberAsOwner,
          variables: {
            owner_ids: [this.userId],
            workspace_id: this.workspaceId,
          },

          refetchQueries: [
            {
              query: getWorkspace,
              variables: {
                id: this.workspaceId,
                theme: config.theme.name,
              },
            },
          ],
        });

        this.saving = false;

        if (result && result.data.container_workspace_add_owners.success) {
          this.$emit('set-as-owner');
          this.openConfirmOwnerModal = false;

          notify({
            message: this.$str(
              'set_member_as_owner_success',
              'container_workspace',
              {
                user: this.userFullName,
                workspace: this.workspaceName,
              }
            ),
            type: 'success',
          });
        } else {
          this.openConfirmOwnerModal = false;

          notify({
            message: this.$str('error:set_as_owner', 'container_workspace'),
            type: 'error',
          });
        }
      } catch (e) {
        this.saving = false;
        // Error notification
        notify({
          message: this.$str('error:set_as_owner', 'container_workspace'),
          type: 'error',
        });
      }
    },

    /**
     * Handle the request for removing member as an owner
     *
     */
    async removeAsOwner() {
      try {
        this.saving = true;

        const result = await this.$apollo.mutate({
          mutation: removeMemberAsOwner,
          variables: {
            owner_ids: [this.userId],
            workspace_id: this.workspaceId,
          },

          refetchQueries: [
            {
              query: getWorkspace,
              variables: {
                id: this.workspaceId,
                theme: config.theme.name,
              },
            },
          ],
        });

        this.saving = false;

        if (result && result.data.container_workspace_remove_owners.success) {
          this.$emit('remove-as-owner');
          this.openRemoveOwnerModal = false;

          notify({
            message: this.$str(
              'remove_member_as_owner_success',
              'container_workspace',
              {
                user: this.userFullName,
                workspace: this.workspaceName,
              }
            ),
            type: 'success',
          });
        } else {
          this.openRemoveOwnerModal = false;

          notify({
            message: this.$str('error:remove_as_owner', 'container_workspace'),
            type: 'error',
          });
        }
      } catch (e) {
        this.saving = false;
        // Error notification
        notify({
          message: this.$str('error:remove_as_owner', 'container_workspace'),
          type: 'error',
        });
      }
    },

    async removeMember() {
      if (this.removing) {
        return;
      }

      this.removing = true;

      try {
        const result = await this.$apollo.mutate({
          mutation: removeMemberFromWorkspace,
          variables: {
            workspace_id: this.workspaceId,
            user_id: this.userId,
          },

          refetchQueries: [
            {
              query: getWorkspace,
              variables: {
                id: this.workspaceId,
                theme: config.theme.name,
              },
            },
          ],
        });

        if (result && result.data.result) {
          this.modal.confirm = false;
          this.$emit('remove-member', this.userId);
        } else {
          this.modal.confirm = false;

          notify({
            message: this.$str('error:remove_user', 'container_workspace'),
            type: 'error',
          });
        }
      } catch (e) {
        await notify({
          message: this.$str('error:remove_user', 'container_workspace'),
          type: 'error',
        });
      } finally {
        this.removing = false;
      }
    },
  },
};
</script>

<style lang="scss">
.tui-workspaceMemberCard {
  &__profileCard {
    width: 100%;

    &-audiencesIcon {
      margin-left: var(--gap-2);
    }
  }

  &__tagRow {
    display: flex;
    align-items: center;
  }

  &__ownerLabel {
    @include tui-font-body-x-small();
    color: var(--color-neutral-6);
  }
}
</style>
