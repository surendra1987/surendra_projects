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
  <LayoutOneColumn :title="$str('reports', 'totara_core')">
    <template v-slot:header-buttons>
      <div class="tui-core_my-reports__headerButtons">
        <Dropdown
          v-if="canCreateSite && canCreateInAnyTenant"
          position="bottom-right"
        >
          <template v-slot:trigger="{ toggle, isOpen }">
            <Button
              :text="$str('createreport', 'totara_reportbuilder')"
              :aria-expanded="isOpen.toString()"
              caret
              @click="toggle"
            />
          </template>

          <DropdownItem :href="$url('/totara/reportbuilder/create.php')">
            {{ $str('site_report', 'totara_reportbuilder') }}
          </DropdownItem>
          <DropdownButton @click="tenantCreate">
            {{ $str('tenant_report', 'totara_reportbuilder') }}
          </DropdownButton>
        </Dropdown>
        <ActionLink
          v-else-if="canCreateSite"
          :text="$str('createreport', 'totara_reportbuilder')"
          :href="$url('/totara/reportbuilder/create.php')"
        />
        <Button
          v-else-if="canCreateInAnyTenant"
          :text="$str('createreport', 'totara_reportbuilder')"
          @click="tenantCreate"
        />
      </div>
    </template>

    <template v-slot:content>
      <div class="tui-core_my-reports__content">
        <NotificationBanner v-if="error" type="error" :message="error" />

        <div class="tui-core_my-reports__reports">
          <ReportFilters v-model:filters="filters" :tenants="tenantOptions" />

          <ReportList
            :reports="renderedReports"
            :show-description="config.showDescription"
            :show-tenant-indicator="showTenantIndicator"
            :default-view="config.defaultView"
          />
        </div>

        <div
          v-if="showScheduledReports"
          class="tui-core_my-reports__scheduledReports"
        >
          <h2 class="tui-core_my-reports__sectionHeading">
            {{ $str('scheduledreports', 'totara_reportbuilder') }}
          </h2>
          <ScheduledReportList
            v-if="renderedScheduledReports.length > 0"
            :data="renderedScheduledReports"
            :show-export-to-filesystem="config.exportToFilesystem"
            show-options
          />
          <div v-else>
            {{ $str('noscheduledreports', 'totara_reportbuilder') }}
          </div>
          <ScheduledReportAdd
            class="tui-core_my-reports__scheduledReportAdd"
            :reports="renderedReports"
          />
        </div>
      </div>
    </template>

    <template v-slot:modals>
      <ModalPresenter
        :open="newTenantReportOpen"
        @request-close="newTenantReportOpen = false"
      >
        <NewTenantReportModal
          :tenants="tenants"
          @submit="handleNewTenantReportSubmit"
        />
      </ModalPresenter>
    </template>
  </LayoutOneColumn>
</template>

<script>
import ActionLink from 'tui/components/links/ActionLink';
import Button from 'tui/components/buttons/Button';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownButton from 'tui/components/dropdown/DropdownButton';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import LayoutOneColumn from 'tui/components/layouts/LayoutOneColumn';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import NotificationBanner from 'tui/components/notifications/NotificationBanner';
import ReportFilters from 'core_my/components/reports/ReportFilters';
import NewTenantReportModal from 'totara_reportbuilder/components/reports/NewTenantReportModal';
import ReportList from 'totara_reportbuilder/components/reports/listing/ReportList';
import ScheduledReportList from 'totara_reportbuilder/components/reports/listing/ScheduledReportList';
import ScheduledReportAdd from 'totara_reportbuilder/components/reports/listing/ScheduledReportAdd';
import { TenantFilterValue } from 'core_my/constants';

export default {
  components: {
    ActionLink,
    Button,
    Dropdown,
    DropdownButton,
    DropdownItem,
    LayoutOneColumn,
    ModalPresenter,
    NotificationBanner,
    ReportFilters,
    NewTenantReportModal,
    ReportList,
    ScheduledReportList,
    ScheduledReportAdd,
  },

  props: {
    reports: { type: Array, required: true },
    scheduledReports: Array,
    tenantOptions: { type: Array },
    error: String,
    config: { type: Object, default: () => ({}) },
    capabilities: { type: Object, default: () => ({}) },
  },

  emits: [],

  data() {
    const firstTenantOption = this.tenantOptions[0];
    return {
      filters: {
        tenant:
          !firstTenantOption || firstTenantOption.id === TenantFilterValue.NONE
            ? TenantFilterValue.ANY
            : firstTenantOption.id,
      },
      newTenantReportOpen: false,
    };
  },

  computed: {
    renderedReports() {
      return this.filterByTenantId(this.reports);
    },

    renderedScheduledReports() {
      return this.filterByTenantId(this.scheduledReports || []);
    },

    canCreateSite() {
      const site = this.tenantOptions.find(
        x => x.id === TenantFilterValue.NONE
      );
      return site && site.can_create;
    },

    canCreateInAnyTenant() {
      return this.tenants.some(x => x.can_create);
    },

    tenants() {
      return this.tenantOptions.filter(x => x.id !== TenantFilterValue.NONE);
    },

    showScheduledReports() {
      return (
        this.capabilities.createscheduledreports &&
        this.renderedReports.length > 0
      );
    },

    showTenantIndicator() {
      // Only show indicator if both site and tenant reports are available.
      // If that's not the case, there's no need to distinguish them.
      const hasSite = this.tenantOptions.some(
        x => x.id === TenantFilterValue.NONE
      );
      return hasSite && this.tenants.length > 0;
    },
  },

  methods: {
    filterByTenantId(list) {
      if (this.filters.tenant === TenantFilterValue.ANY) {
        return list;
      } else if (this.filters.tenant === TenantFilterValue.NONE) {
        return list.filter(x => !x.tenant_id);
      } else if (this.filters.tenant === TenantFilterValue.TENANT) {
        return list.filter(x => !!x.tenant_id);
      } else {
        return list.filter(x => x.tenant_id == this.filters.tenant);
      }
    },

    tenantCreate() {
      if (this.tenants.length === 1 && !this.canCreateSite) {
        window.location = this.$url('/totara/reportbuilder/create.php', {
          tenantid: this.tenants[0].id,
        });
      } else {
        this.newTenantReportOpen = true;
      }
    },

    handleNewTenantReportSubmit({ tenantId }) {
      window.location = this.$url('/totara/reportbuilder/create.php', {
        tenantid: tenantId,
      });
    },
  },
};
</script>

<style lang="scss">
.tui-core_my-reports {
  &__headerButtons {
    display: flex;
  }

  &__content {
    display: flex;
    flex-direction: column;
    gap: var(--gap-8);
  }

  &__reports {
    display: flex;
    flex-direction: column;
    gap: var(--gap-4);
  }

  &__scheduledReports {
    display: flex;
    flex-direction: column;
    gap: var(--gap-4);
  }

  &__sectionHeading {
    margin: 0;
  }

  &__scheduledReportAdd {
    margin-top: var(--gap-2);
  }
}
</style>
