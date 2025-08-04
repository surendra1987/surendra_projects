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

<script setup>
import { computed, ref } from 'vue';
import { totaraUrl } from 'tui/util';
import { notify } from 'tui/notifications';
import { getString } from 'tui/i18n';
import apollo from 'tui/apollo/client';
import { useQuery } from 'tui/apollo/composable';
import Loader from 'tui/components/loading/Loader';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import IdPManageCard from 'auth_ssosaml/components/idp/IdPManageCard';
import Button from 'tui/components/buttons/Button';
import idpsQuery from 'auth_ssosaml/graphql/idps';
import deleteMutation from 'auth_ssosaml/graphql/delete_idp';
import createMutation from 'auth_ssosaml/graphql/create_idp';

defineProps({
  opensslAvailable: { type: Boolean, default: true },
});

const creating = ref(false);
const deleteConfirmationOpen = ref(false);
const deleting = ref(false);
const activeEntry = ref(false);

const idpQuery = useQuery(idpsQuery);
const idps = computed(() => idpQuery.result.value?.idps.items);

const loading = computed(() => !idps.value);

function handleDeleteClick(entry) {
  activeEntry.value = entry;
  deleteConfirmationOpen.value = true;
}

async function handleDeleteConfirm() {
  deleting.value = true;
  try {
    await apollo.mutate({
      mutation: deleteMutation,
      variables: {
        input: {
          id: activeEntry.value.id,
        },
      },
      refetchQueries: ['auth_ssosaml_idps'],
      awaitRefetchQueries: true,
    });

    notify({ message: getString('idp_deleted', 'auth_ssosaml') });
  } finally {
    deleting.value = false;
    deleteConfirmationOpen.value = false;
  }
}

function handleDeleteCancel() {
  deleteConfirmationOpen.value = false;
}

async function handleCreateClick() {
  creating.value = true;
  try {
    const result = await apollo.mutate({
      mutation: createMutation,
    });

    window.location = totaraUrl('/auth/ssosaml/idp_edit.php', {
      id: result.data.idp.id,
    });
  } finally {
    creating.value = false;
  }
}
</script>

<template>
  <div class="tui-auth_ssosaml-manageIdps">
    <div class="tui-auth_ssosaml-manageIdps__header">
      <h2 class="tui-auth_ssosaml-manageIdps__title">
        {{ $str('manage_identity_providers', 'auth_ssosaml') }}
      </h2>
      <Button
        v-if="opensslAvailable"
        :loading="creating"
        :text="$str('add_new_provider', 'auth_ssosaml')"
        :styleclass="{ primary: true }"
        @click="handleCreateClick"
      />
    </div>

    <Loader v-if="loading" loading />
    <div v-else class="tui-auth_ssosaml-manageIdps__cards">
      <IdPManageCard
        v-for="idp in idps"
        :key="idp.id"
        :idp="idp"
        @delete="handleDeleteClick(idp)"
      />
      <div v-if="idps.length === 0">
        {{ $str('listing_no_idps', 'auth_ssosaml') }}
      </div>
    </div>

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

<style lang="scss">
.tui-auth_ssosaml-manageIdps {
  @include tui-stack-vertical(var(--gap-4));

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  &__title {
    margin: 0;
  }

  &__cards {
    @include tui-stack-vertical(var(--gap-4));
  }
}
</style>
