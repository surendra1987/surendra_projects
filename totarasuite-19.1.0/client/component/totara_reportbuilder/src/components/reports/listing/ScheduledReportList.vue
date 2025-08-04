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
  <Table :data="data" color-odd-rows :stack-at="800">
    <template v-slot:header-row>
      <HeaderCell size="3" valign="center">
        {{ $str('reportname', 'totara_reportbuilder') }}
      </HeaderCell>
      <HeaderCell size="2" valign="center">
        {{ $str('savedsearch', 'totara_reportbuilder') }}
      </HeaderCell>
      <HeaderCell size="2" valign="center">
        {{ $str('format', 'totara_reportbuilder') }}
      </HeaderCell>
      <HeaderCell v-if="showExportToFilesystem" size="2" valign="center">
        {{ $str('exportfilesystemoptions', 'totara_reportbuilder') }}
      </HeaderCell>
      <HeaderCell size="4" valign="center">
        {{ $str('schedule', 'totara_reportbuilder') }}
      </HeaderCell>
      <HeaderCell v-if="showOptions" size="2" valign="center">
        {{ $str('options', 'totara_reportbuilder') }}
      </HeaderCell>
    </template>

    <template v-slot:row="{ row }">
      <Cell
        size="3"
        :column-header="$str('reportname', 'totara_reportbuilder')"
      >
        <template v-slot:default>
          {{ row.name }}
        </template>
      </Cell>
      <Cell
        size="2"
        :column-header="$str('savedsearch', 'totara_reportbuilder')"
      >
        <template v-slot:default>
          {{ row.data }}
        </template>
      </Cell>
      <Cell size="2" :column-header="$str('format', 'totara_reportbuilder')">
        <template v-slot:default>
          {{ row.format }}
        </template>
      </Cell>
      <Cell
        v-if="showExportToFilesystem"
        size="2"
        :column-header="$str('exportfilesystemoptions', 'totara_reportbuilder')"
      >
        <template v-slot:default>
          {{ row.export_to_fs_name }}
        </template>
      </Cell>
      <Cell size="4" :column-header="$str('schedule', 'totara_reportbuilder')">
        <template v-slot:default>
          <div v-html="row.schedule_html" />
        </template>
      </Cell>
      <Cell
        v-if="showOptions"
        size="2"
        :column-header="$str('options', 'totara_reportbuilder')"
      >
        <template v-slot:default>
          <a
            :href="$url('/totara/reportbuilder/scheduled.php', { id: row.id })"
          >
            <SettingsIcon :alt="$str('edit')" />
          </a>
          <a
            :href="
              $url('/totara/reportbuilder/deletescheduled.php', { id: row.id })
            "
          >
            <RemoveIcon :alt="$str('delete')" state="alert" />
          </a>
        </template>
      </Cell>
    </template>
  </Table>
</template>

<script>
import Cell from 'tui/components/datatable/Cell';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import Table from 'tui/components/datatable/Table';
import RemoveIcon from 'tui/components/icons/Remove';
import SettingsIcon from 'tui/components/icons/Settings';

export default {
  components: {
    Cell,
    HeaderCell,
    Table,
    RemoveIcon,
    SettingsIcon,
  },

  props: {
    data: Array,
    showExportToFilesystem: Boolean,
    showOptions: Boolean,
  },
};
</script>
