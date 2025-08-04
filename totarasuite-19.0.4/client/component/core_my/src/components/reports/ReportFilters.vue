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
  @module core_my
-->

<template>
  <FilterBar v-if="showFilter" :title="'Filter results'">
    <template v-slot:filters-left="{ stacked }">
      <SelectFilter
        :value="filters.tenant"
        :label="$str('filter_reports', 'totara_reportbuilder')"
        :show-label="true"
        :options="tenantOptions"
        :stacked="stacked"
        @input="update('tenant', $event)"
      />
    </template>
  </FilterBar>
</template>

<script>
import FilterBar from 'tui/components/filters/FilterBar';
import SelectFilter from 'tui/components/filters/SelectFilter';
import { TenantFilterValue } from 'core_my/constants';

export default {
  components: {
    FilterBar,
    SelectFilter,
  },

  props: {
    filters: { type: Object, required: true },
    tenants: { type: Array, required: true },
  },

  emits: ['update:filters'],

  computed: {
    showFilter() {
      return this.hasSelectableTenants;
    },

    hasSelectableTenants() {
      return this.tenants.length > 1;
    },

    tenantOptions() {
      const hasSite = this.tenants.some(x => x.id === TenantFilterValue.NONE);
      return [
        { id: TenantFilterValue.ANY, label: this.$str('all') },
        hasSite && {
          id: TenantFilterValue.NONE,
          label: this.$str('all_site_reports', 'totara_reportbuilder'),
        },
        ...this.tenants
          .filter(x => x.id != TenantFilterValue.NONE)
          .map(x => ({ id: x.id, label: x.name })),
      ].filter(x => x);
    },
  },

  methods: {
    update(field, value) {
      this.$emit('update:filters', {
        ...this.filters,
        [field]: value,
      });
    },
  },
};
</script>
