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

  @author ben fesili <ben.fesili@totara.com>
  @module totara_webhook
-->

<script setup>
import { computed, onMounted, ref } from 'vue';
import {} from 'totara_webhook/constants';
import { getString } from 'tui/i18n';
import { notify } from 'tui/notifications';
import apollo from 'tui/apollo/client';
import { useQuery } from 'tui/apollo/composable';
import Button from 'tui/components/buttons/Button';
import LayoutOneColumn from 'tui/components/layouts/LayoutOneColumn';
import Table from 'tui/components/datatable/Table';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import Cell from 'tui/components/datatable/Cell';
import SortBar from 'tui/components/filters/SortBar';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownButton from 'tui/components/dropdown/DropdownButton';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import MoreIcon from 'tui/components/icons/More';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import Paging from 'tui/components/paging/Paging';
import Totara_webhookFilterBar from 'totara_webhook/components/Totara_webhookFilterBar';
import itemsQuery from 'totara_webhook/graphql/totara_webhooks';
import deleteMutation from 'totara_webhook/graphql/delete_totara_webhook';
import updateMutation from 'totara_webhook/graphql/update_totara_webhook';
// Sort
const sortOptions = [
  { id: 'created_at', label: getString('created_at', 'totara_webhook') },
  { id: 'updated_at', label: getString('updated_at', 'totara_webhook') },
  { id: 'name', label: getString('name', 'totara_webhook') },
];
const sortBy = ref('created_at');

// Filters
const defaultFilters = {
  bar: {
    search: '',
  },
  extra: {
    name: '',
    endpoint: '',
  },
};
const filters = ref(defaultFilters);
const queryFilters = computed(() =>
  Object.fromEntries(
    Object.entries(filters.value.extra).filter(([value]) => value)
  )
);

// Pagination
const page = ref(1);
const pageSize = ref(10);

function updatePage(newPage) {
  page.value = newPage;
}

function updatePageSize(size) {
  page.value = 1;
  pageSize.value = size;
}

// Results
const { result, loading } = useQuery(
  itemsQuery,
  () => ({
    input: {
      filters: queryFilters.value,
      sort: [{ column: sortBy.value, direction: 'DESC' }],
      pagination: {
        page: page.value,
        limit: pageSize.value,
      },
    },
  }),
  {
    fetchPolicy: 'network-only',
  }
);

const totara_webhooks = computed(() => result.value?.totara_webhooks);
const rows = computed(() => totara_webhooks.value?.items ?? []);

// Deletion
const deleting = ref(false);
const deletingId = ref(false);
const deleteOpen = ref(false);

function showDelete(row) {
  deleteOpen.value = true;
  deletingId.value = row.id;
}

async function performDelete() {
  deleting.value = true;
  try {
    const id = deletingId.value;
    await apollo.mutate({
      mutation: deleteMutation,
      variables: {
        reference: { id },
      },
      refetchQueries: ['totara_webhook_totara_webhooks'],
    });
  } finally {
    deleting.value = false;
  }
  deleteOpen.value = false;
}

async function changeStatus(row) {
  //get the current status and flip it
  const newStatus = !row.status;
  const id = row.id;
  await apollo.mutate({
    mutation: updateMutation,
    variables: {
      input: {
        reference: { id },
        status: newStatus,
      },
    },
    refetchQueries: ['totara_webhook_totara_webhooks'],
  });
}

// Flash messages
onMounted(() => {
  const url = new URL(window.location);
  const notifyFor = url.searchParams.get('notify');

  if (notifyFor) {
    if (notifyFor === 'created') {
      notify({
        message: this.$str('totara_webhook_created', 'totara_webhook'),
      });
    } else if (notifyFor === 'saved') {
      notify({ message: this.$str('totara_webhook_saved', 'totara_webhook') });
    }

    url.searchParams.delete('notify');
    window.history.replaceState(null, null, url);
  }
});
defineProps({
  canManage: Boolean,
});
</script>

<template>
  <LayoutOneColumn :title="$str('manage_totara_webhooks', 'totara_webhook')">
    <template v-slot:header-buttons>
      <Button
        v-if="canManage"
        variant="primary"
        :href="$url('/totara/webhook/totara_webhook/edit.php')"
        :text="$str('create', 'core')"
      />
    </template>

    <template v-slot:content>
      <div class="tui-totara_webhookIndex__content">
        <Totara_webhookFilterBar
          v-model:value="filters"
          :default-value="defaultFilters"
        />

        <SortBar v-model:sort-by="sortBy" :options="sortOptions">
          <template v-slot:start>
            {{
              totara_webhooks
                ? $str(
                    totara_webhooks.total === 1 ? 'listitem' : 'listitemplural',
                    'totara_core',
                    totara_webhooks.total
                  )
                : ''
            }}
          </template>
        </SortBar>

        <Table
          :data="rows"
          :loading-preview="loading"
          :loading-preview-rows="pageSize"
        >
          <template v-slot:header-row>
            <HeaderCell>
              {{ $str('name', 'totara_webhook') }}
            </HeaderCell>
            <HeaderCell>
              {{ $str('endpoint', 'totara_webhook') }}
            </HeaderCell>
            <HeaderCell>
              {{ $str('totara_webhook_status', 'totara_webhook') }}
            </HeaderCell>
            <HeaderCell>
              {{ $str('webhook_dispatch_timing', 'totara_webhook') }}
            </HeaderCell>
            <HeaderCell v-if="canManage" align="end">
              <span class="sr-only">
                {{ $str('actions', 'totara_webhook') }}
              </span>
            </HeaderCell>
          </template>

          <template v-slot:row="{ row }">
            <Cell :column-header="$str('name', 'totara_webhook')">
              <a
                :href="
                  $url('/totara/webhook/totara_webhook/view.php', {
                    id: row.id,
                  })
                "
              >
                {{ row.name }}
              </a>
            </Cell>
            <Cell :column-header="$str('endpoint', 'totara_webhook')">
              {{ `https://${row.endpoint}` }}
            </Cell>
            <Cell
              :column-header="$str('totara_webhook_status', 'totara_webhook')"
            >
              {{
                row.status
                  ? $str('totara_webhook_status_enabled', 'totara_webhook')
                  : $str('totara_webhook_status_disabled', 'totara_webhook')
              }}
            </Cell>
            <Cell
              :column-header="$str('webhook_dispatch_timing', 'totara_webhook')"
            >
              {{
                row.immediate
                  ? $str('webhook_immediate', 'totara_webhook')
                  : $str('webhook_scheduled', 'totara_webhook')
              }}
            </Cell>
            <Cell
              v-if="canManage"
              :column-header="$str('actions', 'totara_webhook')"
              align="end"
            >
              <Dropdown position="bottom-right">
                <template v-slot:trigger="{ toggle, isOpen }">
                  <div class="tui-totara_webhookIndex__moreButtonWrap">
                    <Button
                      variant="link"
                      size="sm"
                      :aria-expanded="isOpen"
                      :aria-label="
                        $str('actions_for_x', 'totara_webhook', row.name)
                      "
                      @click="toggle"
                    >
                      <template v-slot:icon>
                        <MoreIcon :size="300" />
                      </template>
                    </Button>
                  </div>
                </template>
                <DropdownButton @click="changeStatus(row)">
                  {{
                    row.status
                      ? $str('totara_webhook_disable_webhook', 'totara_webhook')
                      : $str('totara_webhook_enable_webhook', 'totara_webhook')
                  }}
                </DropdownButton>
                <DropdownItem
                  :href="
                    $url('/totara/webhook/totara_webhook/edit.php', {
                      id: row.id,
                    })
                  "
                >
                  {{ $str('edit', 'core') }}
                </DropdownItem>
                <DropdownButton @click="showDelete(row)">
                  {{ $str('delete', 'core') }}
                </DropdownButton>
              </Dropdown>
            </Cell>
          </template>
        </Table>

        <Paging
          v-if="totara_webhooks?.total > 0"
          :page="page"
          :items-per-page="pageSize"
          :total-items="totara_webhooks?.total ?? 0"
          @page-change="updatePage"
          @count-change="updatePageSize"
        />
      </div>
    </template>

    <template v-slot:modals>
      <ConfirmationModal
        :open="deleteOpen"
        :title="$str('delete', 'core')"
        :confirm-button-text="$str('delete', 'core')"
        :loading="deleting"
        @confirm="performDelete"
        @cancel="deleteOpen = false"
      >
        {{ $str('delete_totara_webhook_confirm_message', 'totara_webhook') }}
      </ConfirmationModal>
    </template>
  </LayoutOneColumn>
</template>

<style lang="scss">
.tui-totara_webhookIndex {
  &__content {
    display: flex;
    flex-flow: column;
    gap: var(--gap-4);
  }

  &__moreButtonWrap {
    display: flex;
  }
}
</style>
