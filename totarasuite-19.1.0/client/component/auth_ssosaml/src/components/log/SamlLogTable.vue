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
  @module tui
-->

<template>
  <Table
    :data="rows"
    expandable-rows
    expand-multiple-rows
    stealth-expanded
    :loading-preview="loading"
    :loading-preview-rows="pageSize"
  >
    <template v-slot:header-row>
      <ExpandCell :header="true" />
      <HeaderCell size="7">
        {{ $str('message_type', 'auth_ssosaml') }}
      </HeaderCell>
      <HeaderCell size="7">
        {{ $str('status', 'core') }}
      </HeaderCell>
      <HeaderCell size="7">
        {{ $str('date', 'core') }}
      </HeaderCell>
    </template>

    <template v-slot:row="{ row, expand, expandState }">
      <ExpandCell
        :aria-label="row.date"
        :expand-state="expandState"
        @click="expand()"
      />
      <Cell size="7" :column-header="$str('message_type', 'auth_ssosaml')">
        {{ row.type }}
      </Cell>
      <Cell size="7" :column-header="$str('status', 'core')">
        {{ row.status_label }}
      </Cell>
      <Cell size="7" :column-header="$str('date', 'core')">
        {{ row.date }}
      </Cell>
    </template>

    <template v-slot:expand-content="{ row }">
      <div class="tui-auth_ssosaml-logTable__rowContent">
        <NotificationBanner
          v-if="row.error"
          type="error"
          :message="row.error"
        />
        <NotificationBanner
          v-if="row.notice"
          type="warning"
          :message="row.notice"
        />

        <div
          v-if="row.content_request"
          class="tui-auth_ssosaml-logTable__message"
        >
          <div class="tui-auth_ssosaml-logTable__message-heading">
            <div class="tui-auth_ssosaml-logTable__message-title">
              {{ $str('log_request', 'auth_ssosaml') }}
            </div>
            <div class="tui-auth_ssosaml-logTable__message-date">
              {{ row.content_request_time }}
            </div>
          </div>
          <div
            class="tui-auth_ssosaml-logTable__message-xml"
            v-text="row.content_request"
          />
        </div>

        <div
          v-if="row.content_response"
          class="tui-auth_ssosaml-logTable__message"
        >
          <div class="tui-auth_ssosaml-logTable__message-heading">
            <div class="tui-auth_ssosaml-logTable__message-title">
              {{ $str('log_response', 'auth_ssosaml') }}
            </div>
            <div class="tui-auth_ssosaml-logTable__message-date">
              {{ row.content_response_time }}
            </div>
          </div>
          <div
            class="tui-auth_ssosaml-logTable__message-xml"
            v-text="row.content_response"
          />
        </div>
      </div>
    </template>
  </Table>
</template>

<script>
import Cell from 'tui/components/datatable/Cell';
import ExpandCell from 'tui/components/datatable/ExpandCell';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import Table from 'tui/components/datatable/Table';
import NotificationBanner from 'tui/components/notifications/NotificationBanner';

export default {
  components: {
    Cell,
    ExpandCell,
    HeaderCell,
    Table,
    NotificationBanner,
  },

  props: {
    rows: Array,
    loading: Boolean,
    pageSize: Number,
  },
};
</script>

<style lang="scss">
.tui-auth_ssosaml-logTable {
  &__rowContent {
    @include tui-stack-vertical(var(--gap-4));
  }

  &__message {
    border: 1px solid var(--card-border-color);
    border-radius: var(--card-border-radius);

    &-heading {
      @include font(h6);
      display: flex;
      justify-content: space-between;
      padding: var(--gap-2);
      background-color: var(--color-neutral-3);
      border-radius: calc(var(--card-border-radius) - 1px);
    }

    &-date {
      font-weight: normal;
    }

    &-xml {
      @include font(body-sm);
      padding: var(--gap-2);
      overflow: auto;
      font-family: var(--font-family-monospace);
      white-space: pre-wrap;
    }
  }
}
</style>
