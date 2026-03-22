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
  @module auth_ssosaml
-->

<template>
  <ListingCard
    :title="idp.label || $str('default_label', 'auth_ssosaml')"
    :href="$url('/auth/ssosaml/idp.php', { id: idp.id })"
  >
    <template v-slot:title-extra>
      <Lozenge
        v-if="idp.debug"
        type="warning"
        :text="$str('debug_mode_on', 'auth_ssosaml')"
      />
    </template>
    <template v-slot:side>
      <div class="tui-auth_ssosaml-idpManageCard__side">
        <div class="tui-auth_ssosaml-idpManageCard__enable">
          <ToggleSwitch
            :text="$str('enable', 'core')"
            :value="enabled"
            toggle-first
            :disabled="!canEnable"
            @input="handleToggle"
          />
        </div>
        <div class="tui-auth_ssosaml-idpManageCard__separator" />
        <div class="tui-auth_ssosaml-idpManageCard__dropdown">
          <Dropdown position="bottom-right">
            <template v-slot:trigger="{ toggle, isOpen }">
              <MoreButton
                :aria-expanded="isOpen.toString()"
                :aria-label="
                  $str(
                    'actions_for_x',
                    'auth_ssosaml',
                    $str('idp', 'auth_ssosaml')
                  )
                "
                :size="300"
                @click="toggle"
              />
            </template>

            <DropdownItem :href="$url('/auth/ssosaml/idp.php', { id: idp.id })">
              {{ $str('view', 'core') }}
            </DropdownItem>
            <DropdownItem
              :href="$url('/auth/ssosaml/idp_edit.php', { id: idp.id })"
            >
              {{ $str('edit', 'core') }}
            </DropdownItem>
            <DropdownItem
              :href="$url('/auth/ssosaml/idp_test.php', { id: idp.id })"
            >
              {{ $str('test', 'auth_ssosaml') }}
            </DropdownItem>
            <DropdownItem
              :href="$url('/auth/ssosaml/saml_log.php', { idp: idp.id })"
            >
              {{ $str('logs', 'core') }}
            </DropdownItem>
            <DropdownButton @click="$emit('delete')">
              {{ $str('delete', 'core') }}
            </DropdownButton>
          </Dropdown>
        </div>
      </div>
    </template>
    <template v-slot:modals>
      <ConfirmationModal
        :open="enableConfirmOpen"
        :title="$str('enable_idp', 'auth_ssosaml')"
        :confirm-button-text="$str('enable_now', 'auth_ssosaml')"
        size="small"
        :loading="saving"
        @confirm="handleEnableConfirm"
        @cancel="enableConfirmOpen = false"
      >
        {{ $str('enable_idp_confirm_message', 'auth_ssosaml') }}
      </ConfirmationModal>
      <ConfirmationModal
        :open="disableConfirmOpen"
        :title="$str('disable_idp', 'auth_ssosaml')"
        :confirm-button-text="$str('disable_now', 'auth_ssosaml')"
        size="small"
        :loading="saving"
        @confirm="handleDisableConfirm"
        @cancel="disableConfirmOpen = false"
      >
        {{ $str('disable_idp_confirm_message', 'auth_ssosaml') }}
      </ConfirmationModal>
    </template>
  </ListingCard>
</template>

<script>
import MoreButton from 'tui/components/buttons/MoreIcon';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownButton from 'tui/components/dropdown/DropdownButton';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import Lozenge from 'tui/components/lozenge/Lozenge';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
import ListingCard from 'auth_ssosaml/components/idp/ListingCard';
import setEnabledMutation from 'auth_ssosaml/graphql/set_idp_enabled';
import { notify } from 'tui/notifications';

export default {
  components: {
    MoreButton,
    Dropdown,
    DropdownButton,
    DropdownItem,
    Lozenge,
    ConfirmationModal,
    ToggleSwitch,
    ListingCard,
  },

  props: {
    idp: Object,
    open: Boolean,
  },

  emits: ['delete'],

  data() {
    return {
      saving: false,
      enableConfirmOpen: false,
      disableConfirmOpen: false,
    };
  },

  computed: {
    enabled() {
      return this.idp.status;
    },

    canEnable() {
      const idp = this.idp;
      return Boolean(
        idp.label &&
          idp.metadata.source !== 'NONE' &&
          idp.idp_user_id_field &&
          idp.totara_user_id_field
      );
    },
  },

  methods: {
    handleToggle() {
      if (this.enabled) {
        this.disableConfirmOpen = true;
      } else {
        this.enableConfirmOpen = true;
      }
    },

    async handleEnableConfirm() {
      await this.saveEnabled(true);
      this.enableConfirmOpen = false;
    },

    async handleDisableConfirm() {
      await this.saveEnabled(false);
      this.disableConfirmOpen = false;
    },

    async saveEnabled(status) {
      this.saving = true;
      try {
        await this.$apollo.mutate({
          mutation: setEnabledMutation,
          variables: {
            input: {
              id: this.idp.id,
              status,
            },
          },
        });

        notify({ message: this.$str('changessaved', 'core') });
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>

<style lang="scss">
.tui-auth_ssosaml-idpManageCard {
  &__side {
    @include font(body-sm);
    display: flex;
    align-items: center;
  }

  &__dropdown {
    font-size: 0; // work around dropdown alignment issue
  }

  &__enable {
    padding: 0 var(--gap-2);
  }

  &__separator {
    height: rem-px(16);
    margin: 0 var(--gap-2);
    border-right: 1px solid var(--color-border);
  }
}
</style>
