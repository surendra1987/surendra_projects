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
  <FieldArray v-slot="{ items, push, remove }" :path="path">
    <div>
      <SimpleTable
        :columns="[
          {
            key: 'totara',
            size: 'minmax(220px, 300px)',
            label: $str('local_field', 'auth_ssosaml'),
            valign: 'center',
          },
          {
            key: 'locked',
            size: 'minmax(220px, 300px)',
            label: $str('local_field_locking', 'auth_ssosaml'),
          },
          { key: 'remove', size: 'auto' },
        ]"
        :stack-at="500"
      >
        <template v-slot:header>
          <SimpleTableRow header>
            <template v-slot:col-remove>
              <div class="tui-auth_ssosaml-userAttributes__invisibleRemove">
                <ButtonIcon
                  aria-hidden="true"
                  aria-label=""
                  :styleclass="{ stealth: true }"
                >
                  <RemoveIcon />
                </ButtonIcon>
              </div>
            </template>
          </SimpleTableRow>
        </template>

        <SimpleTableRow v-for="(row, index) in items" :key="row.id" :data="row">
          <template v-slot:col-totara>
            {{ fieldName(row.id) }}
          </template>

          <template v-slot:col-locked>
            <FormSelect
              :name="[index, 'locked']"
              :options="[
                { id: 'UNLOCKED', label: $str('unlocked', 'auth') },
                {
                  id: 'UNLOCKED_IF_EMPTY',
                  label: $str('unlockedifempty', 'auth'),
                },
                { id: 'LOCKED', label: $str('locked', 'auth') },
              ]"
              char-length="full"
            />
          </template>

          <template v-slot:col-remove="{ isStacked }">
            <ButtonIcon
              :aria-label="$str('remove', 'core')"
              :text="isStacked ? $str('remove', 'core') : null"
              :styleclass="{ stealth: true }"
              @click="remove(index)"
            >
              <RemoveIcon state="alert" />
            </ButtonIcon>
          </template>
        </SimpleTableRow>
      </SimpleTable>

      <div class="tui-auth_ssosaml-userAttributes__addFieldRow">
        <AddFieldsButton
          :mapping-options="mappingOptions"
          :added-mappings="items.map(x => x.id)"
          @add="addFields($event, push)"
        />
      </div>
    </div>
  </FieldArray>
</template>

<script>
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import RemoveIcon from 'tui/components/icons/Remove';
import { FieldArray, FormSelect } from 'tui/components/uniform';
import AddFieldsButton from 'auth_ssosaml/components/fields/AddFieldsButton';
import SimpleTable from 'auth_ssosaml/components/ui/simple_table/SimpleTable';
import SimpleTableRow from 'auth_ssosaml/components/ui/simple_table/SimpleTableRow';

export default {
  components: {
    ButtonIcon,
    RemoveIcon,
    FieldArray,
    FormSelect,
    AddFieldsButton,
    SimpleTable,
    SimpleTableRow,
  },

  props: {
    path: String,
    fieldInfo: { type: Object, required: true },
  },

  computed: {
    mappingOptions() {
      return Object.values(this.fieldInfo)
        .filter(x => x.lockable)
        .map(({ id, label }) => ({ id, label }));
    },
  },

  methods: {
    addFields(fields, push) {
      fields.forEach(id => {
        push({ id, locked: 'UNLOCKED' });
      });
    },

    fieldName(field) {
      return this.fieldInfo[field] && this.fieldInfo[field].label;
    },
  },
};
</script>

<style lang="scss">
.tui-auth_ssosaml-userAttributes {
  &__invisibleRemove {
    height: 1px;
    visibility: hidden;
  }

  &__addFieldRow {
    margin-top: var(--gap-4);
  }
}
</style>
