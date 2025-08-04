<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Jaron Steenson <jaron.steenson@totaralearning.com>
  @module totara_competency
-->

<template>
  <div class="tui-competencySelfAssignmentTable">
    <h2 class="tui-competencySelfAssignmentTable__header">
      {{ $str('competencies', 'totara_competency', totalCompetencyCount) }}
    </h2>

    <SelectTable
      :value="value"
      :data="competencies"
      :expandable-rows="true"
      :select-all-enabled="true"
      checkbox-v-align="center"
      @input="
        e => {
          $emit('update:value', e);
          $emit('input', e);
        }
      "
    >
      <template v-slot:header-row>
        <ExpandCell :header="true" />
        <HeaderCell size="4">
          {{ $str('header_competency', 'totara_competency') }}
        </HeaderCell>
        <HeaderCell size="4">
          {{ $str('header_assignment_status', 'totara_competency') }}
        </HeaderCell>
        <HeaderCell size="4">
          {{ $str('header_assignment_reasons', 'totara_competency') }}
        </HeaderCell>
      </template>

      <template v-slot:row="{ row, expand, expandState }">
        <ExpandCell
          :aria-label="row.display_name"
          :empty="!row.description"
          :expand-state="expandState"
          @click="expand()"
        />
        <Cell
          size="4"
          :column-header="$str('header_competency', 'totara_competency')"
          valign="center"
        >
          {{ row.display_name }}
        </Cell>
        <Cell
          size="4"
          :column-header="$str('header_assignment_status', 'totara_competency')"
          valign="center"
        >
          {{ getAssignmentStatus(row) }}
        </Cell>
        <Cell
          size="4"
          :column-header="
            row.user_assignments.length > 0
              ? $str('header_assignment_reasons', 'totara_competency')
              : ''
          "
          valign="center"
        >
          <ul class="tui-competencySelfAssignmentTable__reasons">
            <li
              v-for="assignments in row.user_assignments"
              :key="assignments.id"
            >
              {{ assignments.reason_assigned }}
            </li>
          </ul>
        </Cell>
      </template>

      <template v-slot:expand-content="{ row }">
        <div class="tui-competencySelfAssignmentTable__expand">
          <h3>
            {{ row.display_name }}
          </h3>
          <h4 class="tui-competencySelfAssignmentTable__expand-subHeader">
            {{ $str('description', 'totara_competency') }}
          </h4>
          <div v-html="row.description" />
        </div>
      </template>
    </SelectTable>
  </div>
</template>

<script>
import Cell from 'tui/components/datatable/Cell';
import ExpandCell from 'tui/components/datatable/ExpandCell';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import SelectTable from 'tui/components/datatable/SelectTable';

export default {
  components: {
    Cell,
    ExpandCell,
    HeaderCell,
    SelectTable,
  },

  props: {
    value: {
      type: Array,
      required: true,
    },
    competencies: {
      type: Array,
      required: true,
    },
    totalCompetencyCount: {
      type: Number,
      required: true,
    },
  },

  emits: ['input', 'update:value'],

  data() {
    return {
      page: 0,
    };
  },

  methods: {
    getAssignmentStatus(competency) {
      if (this.isAssigned(competency)) {
        return this.$str('currently_assigned', 'totara_competency');
      }

      return this.$str('not_assigned', 'totara_competency');
    },
    isAssigned(competency) {
      return (
        Boolean(competency.user_assignments) &&
        competency.user_assignments.length > 0
      );
    },
  },
};
</script>

<style lang="scss">
.tui-competencySelfAssignmentTable {
  & > * + * {
    margin-top: var(--gap-2);
  }

  &__header {
    @include font(h3);
    margin: 0;
  }

  &__reasons {
    margin: 0;
    padding-left: 0;
    list-style: none;
  }

  &__expand {
    & > * + * {
      margin: var(--gap-2) 0 0;
    }
  }
}
</style>
