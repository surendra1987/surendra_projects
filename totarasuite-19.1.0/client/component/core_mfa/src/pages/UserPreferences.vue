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

  @author Simon Chester <simon.chester@totara.com>
  @module core_mfa
-->

<template>
  <LayoutOneColumn :title="$str('manage_multi_factor_authentication', 'mfa')">
    <template v-slot:feedback-banner>
      <NotificationBanner
        v-if="authPluginCompatibleWarning"
        :message="$str('error:auth_plugins_compatibility', 'mfa')"
        type="warning"
      />
    </template>

    <template v-slot:header-buttons>
      <Button
        :text="$str('add_factor', 'mfa')"
        :styleclass="{ primary: true }"
        :disabled="factors.length === 0"
        @click="addOpen = true"
      />
    </template>

    <template v-slot:pre-body>
      <div class="tui-core_mfa-userPreferences__subtitle">
        {{ $str('manage_mfa_subtitle', 'mfa') }}
      </div>
    </template>

    <template v-slot:content>
      <div class="tui-core_mfa-userPreferences__content">
        <InstancesTable
          v-if="visibleInstances.length > 0"
          :data="visibleInstances"
          @remove="promptRemove"
        />
        <p v-else>
          {{ $str('configured_factors_empty_state', 'mfa') }}
        </p>
      </div>
    </template>

    <template v-slot:modals>
      <ConfirmationModal
        :open="deleteConfirmationOpen"
        :title="$str('remove_factor', 'mfa')"
        :confirm-button-text="$str('remove', 'core')"
        size="small"
        :loading="deleting"
        close-button
        @confirm="handleDeleteConfirm"
        @cancel="deleteConfirmationOpen = false"
      >
        {{ $str('remove_factor_confirm_message', 'mfa') }}
      </ConfirmationModal>

      <ModalPresenter :open="addOpen" @request-close="addOpen = false">
        <FactorChooserModal :factors="factors" />
      </ModalPresenter>
    </template>
  </LayoutOneColumn>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import LayoutOneColumn from 'tui/components/layouts/LayoutOneColumn';
import NotificationBanner from 'tui/components/notifications/NotificationBanner';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import InstancesTable from 'core_mfa/components/manage/InstancesTable';
import FactorChooserModal from 'core_mfa/components/register/FactorChooserModal';
import deleteMutation from 'core/graphql/mfa_delete_instance';

export default {
  components: {
    Button,
    LayoutOneColumn,
    NotificationBanner,
    ConfirmationModal,
    ModalPresenter,
    InstancesTable,
    FactorChooserModal,
  },

  props: {
    factors: { type: Array, required: true },
    instances: { type: Array, required: true },
    authPluginCompatibleWarning: { type: Boolean, required: true },
  },

  data() {
    return {
      addOpen: false,
      editingItem: null,
      deleteConfirmationOpen: false,
      deleting: false,
      visibleInstances: [...this.instances],
    };
  },

  methods: {
    promptRemove(item) {
      this.editingItem = item;
      this.deleteConfirmationOpen = true;
    },

    async handleDeleteConfirm() {
      const item = this.editingItem;
      this.deleting = true;
      try {
        await this.$apollo.mutate({
          mutation: deleteMutation,
          variables: {
            input: {
              id: item.id,
            },
          },
        });
        this.visibleInstances = this.visibleInstances.filter(
          x => x.id !== item.id
        );
        this.deleteConfirmationOpen = false;
        // get new values for add
        window.location.reload();
      } finally {
        this.deleting = false;
      }
    },
  },
};
</script>

<style lang="scss">
.tui-core_mfa-userPreferences {
  &__subtitle {
    margin-top: var(--gap-2);
  }

  &__content {
    display: flex;
    flex-direction: column;
    gap: var(--gap-8);
  }
}
</style>
