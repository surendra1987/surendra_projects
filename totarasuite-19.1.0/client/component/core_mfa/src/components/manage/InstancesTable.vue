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
  @module core_mfa
-->

<template>
  <Table :data="data">
    <template v-slot:header-row>
      <HeaderCell size="6" valign="center">
        {{ $str('factor', 'mfa') }}
      </HeaderCell>
      <HeaderCell v-if="showLabel" size="6" valign="center">
        {{ $str('name', 'core') }}
      </HeaderCell>
      <HeaderCell size="4" valign="center">
        {{ $str('added_date', 'mfa') }}
      </HeaderCell>
      <HeaderCell size="2" align="end" valign="center" />
    </template>

    <template v-slot:row="{ row }">
      <Cell size="6" :column-header="$str('factor', 'mfa')" valign="center">
        {{ row.factor_name }}
      </Cell>
      <Cell
        v-if="showLabel"
        size="6"
        :column-header="$str('name', 'core')"
        valign="center"
      >
        {{ row.label }}
      </Cell>
      <Cell size="4" :column-header="$str('added_date', 'mfa')" valign="center">
        {{ row.created_at_formatted }}
      </Cell>
      <Cell v-slot="{ isStacked }" size="2" align="end" valign="center">
        <ButtonIcon
          :aria-label="$str('remove_x', 'mfa', row.label || row.factor_name)"
          :text="isStacked ? $str('remove', 'core') : null"
          :styleclass="
            isStacked ? { small: true, stealth: true } : { transparent: true }
          "
          @click="$emit('remove', row)"
        >
          <RemoveIcon state="alert" />
        </ButtonIcon>
      </Cell>
    </template>
  </Table>
</template>

<script>
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import Cell from 'tui/components/datatable/Cell';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import Table from 'tui/components/datatable/Table';
import RemoveIcon from 'tui/components/icons/Remove';

export default {
  components: {
    ButtonIcon,
    Cell,
    HeaderCell,
    Table,
    RemoveIcon,
  },

  props: {
    data: { type: Array, required: true },
  },

  emits: ['remove'],

  computed: {
    showLabel() {
      return this.data.some(x => !!x.label);
    },
  },
};
</script>
