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
  @module totara_reportbuilder
-->

<template>
  <div class="tui-totara_reportbuilder-reportsList" data-testid="report-list">
    <div class="tui-totara_reportbuilder-reportsList__overview">
      <div aria-live="polite">{{ itemsText }}</div>
      <div>
        <ToggleSet
          v-model:value="view"
          :aria-label="$str('view_toggle_label', 'totara_reportbuilder')"
        >
          <ToggleButton
            value="grid"
            :aria-label="$str('tile_view', 'totara_reportbuilder')"
          >
            <GridIcon />
          </ToggleButton>
          <ToggleButton
            value="list"
            :aria-label="$str('list_view', 'totara_reportbuilder')"
          >
            <ListIcon />
          </ToggleButton>
        </ToggleSet>
      </div>
    </div>

    <div
      v-if="reports.length > 0"
      :class="[
        'tui-totara_reportbuilder-reportsList__list',
        'tui-totara_reportbuilder-reportsList__list--' + view,
      ]"
    >
      <ReportItem
        v-for="report in reports"
        :key="report.id"
        :report="report"
        :show-description="showDescription"
        :show-tenant-indicator="showTenantIndicator"
        :view="view"
      />
    </div>
  </div>
</template>

<script>
import { WebStorageStore } from 'tui/storage';
import GridIcon from 'tui/components/icons/Grid';
import ListIcon from 'tui/components/icons/List';
import ToggleSet from 'tui/components/toggle/ToggleSet';
import ToggleButton from 'tui/components/toggle/ToggleButton';
import ReportItem from 'totara_reportbuilder/components/reports/listing/ReportItem';

const storage = new WebStorageStore(
  'totara_reportbuilder/listing',
  window.sessionStorage
);

export default {
  components: {
    GridIcon,
    ListIcon,
    ToggleSet,
    ToggleButton,
    ReportItem,
  },

  props: {
    reports: { type: Array, required: true },
    showDescription: Boolean,
    showTenantIndicator: Boolean,
    defaultView: String,
  },

  data() {
    return {
      view: storage.get('view') || this.defaultView || 'grid',
    };
  },

  computed: {
    itemsText() {
      const count = this.reports.length;
      return this.$str(
        count === 1 ? 'listitem' : 'listitemplural',
        'totara_core',
        count
      );
    },
  },

  watch: {
    view(val) {
      storage.set('view', val);
    },
  },
};
</script>

<style lang="scss">
.tui-totara_reportbuilder-reportsList {
  display: flex;
  flex-direction: column;
  gap: var(--gap-4);
  isolation: isolate;

  &__overview {
    display: flex;
    flex-wrap: wrap;
    gap: var(--gap-4);
    align-items: center;
    justify-content: space-between;
  }

  &__list--grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(rem-px(250), 1fr));
    gap: var(--gap-card-grid);
  }

  &__list--list {
    border-top: var(--border-width-thin) solid var(--color-border);
  }
}
</style>
