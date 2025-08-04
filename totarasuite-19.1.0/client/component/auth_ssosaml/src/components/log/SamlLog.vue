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
  <div>
    <LogTable :rows="rows" :loading="loading" />

    <Paging
      v-if="logEntries && logEntries.total > 0"
      class="tui-auth_ssosaml-samlLog__paging"
      :page="page"
      :items-per-page="pageSize"
      :total-items="logEntries.total"
      @page-change="handlePageChange"
      @count-change="handlePageSizeChange"
    />
  </div>
</template>

<script>
import Paging from 'tui/components/paging/Paging';
import LogTable from 'auth_ssosaml/components/log/SamlLogTable';
import logEntriesQuery from 'auth_ssosaml/graphql/saml_log';

export default {
  components: {
    Paging,
    LogTable,
  },

  props: {
    idpId: { type: Number, required: true },
  },

  data() {
    return {
      page: 1,
      pageSize: 50,
    };
  },

  apollo: {
    logEntries: {
      query: logEntriesQuery,
      fetchPolicy: 'network-only',
      variables() {
        return {
          input: {
            idp_id: this.idpId,
            pagination: {
              page: this.page,
              limit: this.pageSize,
            },
          },
        };
      },
      update: result => result.entries,
    },
  },

  computed: {
    loading() {
      return this.$apollo.loading;
    },

    rows() {
      return (this.logEntries && this.logEntries.items) || [];
    },
  },

  methods: {
    handlePageChange(page) {
      this.page = page;
    },

    handlePageSizeChange(size) {
      this.page = 1;
      this.pageSize = size;
    },
  },
};
</script>

<style lang="scss">
.tui-auth_ssosaml-samlLog {
  &__paging {
    margin-top: var(--gap-4);
  }
}
</style>
