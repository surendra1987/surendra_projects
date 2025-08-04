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
  <div class="tui-auth_ssosaml-idpNavButtons">
    <template v-for="option in buttonOptions">
      <ActionLink
        v-if="option.url"
        :key="option.id"
        :text="option.label"
        :href="option.url"
      />
      <Button
        v-else
        :key="option.id"
        :text="option.label"
        @click="option.action"
      />
    </template>

    <Dropdown position="bottom-right">
      <template v-slot:trigger="{ toggle, isOpen }">
        <ButtonIcon
          class="tui-auth_ssosaml-idpNavButtons__moreButton"
          :aria-expanded="isOpen.toString()"
          :aria-label="
            $str('actions_for_x', 'auth_ssosaml', $str('idp', 'auth_ssosaml'))
          "
          @click="toggle"
        >
          <MoreIcon :size="300" />
        </ButtonIcon>
      </template>

      <template v-for="option in dropdownOptions">
        <DropdownItem v-if="option.url" :key="option.id" :href="option.url">
          {{ option.label }}
        </DropdownItem>
        <DropdownButton v-else :key="option.id" @click="option.action">
          {{ option.label }}
        </DropdownButton>
      </template>
    </Dropdown>

    <ConfirmationModal
      :open="deleteConfirmationOpen"
      :title="$str('delete_idp', 'auth_ssosaml')"
      :confirm-button-text="$str('delete', 'core')"
      size="small"
      :loading="deleting"
      @confirm="handleDeleteConfirm"
      @cancel="handleDeleteCancel"
    >
      {{ $str('delete_idp_confirm_message', 'auth_ssosaml') }}
    </ConfirmationModal>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownButton from 'tui/components/dropdown/DropdownButton';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import MoreIcon from 'tui/components/icons/More';
import ActionLink from 'tui/components/links/ActionLink';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import deleteMutation from 'auth_ssosaml/graphql/delete_idp';

export default {
  components: {
    Button,
    ButtonIcon,
    Dropdown,
    DropdownButton,
    DropdownItem,
    MoreIcon,
    ActionLink,
    ConfirmationModal,
  },

  props: {
    idpId: { type: String, required: true },
    exclude: { type: Array, default: () => [] },
    buttons: { type: Array, default: () => [] },
  },

  data() {
    return {
      deleteConfirmationOpen: false,
      deleting: false,
    };
  },

  computed: {
    options() {
      const { idpId } = this;
      const options = [
        {
          id: 'view',
          label: this.$str('view', 'core'),
          url: this.$url('/auth/ssosaml/idp.php', { id: idpId }),
        },
        {
          id: 'edit',
          label: this.$str('edit', 'core'),
          url: this.$url('/auth/ssosaml/idp_edit.php', { id: idpId }),
        },
        {
          id: 'test',
          label: this.$str('test', 'auth_ssosaml'),
          url: this.$url('/auth/ssosaml/idp_test.php', { id: idpId }),
        },
        {
          id: 'logs',
          label: this.$str('logs', 'core'),
          url: this.$url('/auth/ssosaml/saml_log.php', { idp: idpId }),
        },
        {
          id: 'delete',
          label: this.$str('delete', 'core'),
          action: this.handleDeleteClick,
        },
      ];
      return options.filter(x => !this.exclude.includes(x.id));
    },

    dropdownOptions() {
      return this.options.filter(x => !this.buttons.includes(x.id));
    },

    buttonOptions() {
      return this.buttons.map(id => this.options.find(x => x.id == id));
    },
  },

  methods: {
    handleDeleteClick() {
      this.deleteConfirmationOpen = true;
    },

    async handleDeleteConfirm() {
      this.deleting = true;
      try {
        await this.$apollo.mutate({
          mutation: deleteMutation,
          variables: {
            input: {
              id: this.idpId,
            },
          },
          refetchQueries: ['auth_ssosaml_idps'],
          awaitRefetchQueries: true,
        });

        window.location = this.$url('/auth/ssosaml/plugin_settings.php');
      } finally {
        this.deleting = false;
        this.deleteConfirmationOpen = false;
      }
    },

    handleDeleteCancel() {
      this.deleteConfirmationOpen = false;
    },
  },
};
</script>

<style lang="scss">
.tui-auth_ssosaml-idpNavButtons {
  @include tui-stack-horizontal(var(--gap-2));
  display: flex;

  &__moreButton {
    min-width: rem-px(56);
  }
}
</style>
