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
            key: 'internal',
            size: '220px',
            label: $str('local_field', 'auth_ssosaml'),
          },
          {
            key: 'external',
            size: '220px',
            label: $str('idp_field', 'auth_ssosaml'),
          },
          {
            key: 'update',
            size: '220px',
            label: $str('update_local_field', 'auth_ssosaml'),
          },
          { key: 'remove', size: 'auto' },
        ]"
        :stack-at="750"
      >
        <template v-slot:header>
          <SimpleTableRow header>
            <template v-slot:col-remove>
              <div class="tui-auth_ssosaml-mappingEdit__invisibleRemove">
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

        <!-- user identifier mapping row -->
        <SimpleTableRow v-if="userIdField">
          <template v-slot:col-internal>
            <InputSizedText>
              {{ fieldName(userIdField.internal) }}
            </InputSizedText>
          </template>

          <template v-slot:col-external>
            <InputText
              char-length="full"
              :aria-label="
                $str(
                  'x_external_field',
                  'auth_ssosaml',
                  fieldName(userIdField.internal)
                )
              "
              :value="userIdField.external"
              disabled
            />
          </template>

          <template v-slot:col-update>
            <Select
              :aria-label="
                $str(
                  'x_update_local_field',
                  'auth_ssosaml',
                  fieldName(userIdField.internal)
                )
              "
              :options="updateOptions"
              value="CREATE"
              disabled
            />
          </template>
        </SimpleTableRow>

        <template v-for="(row, index) in items">
          <SimpleTableRow
            v-if="rowRendered(row)"
            :key="row.internal"
            :data="row"
          >
            <template v-slot:col-internal>
              <InputSizedText>
                {{ fieldName(row.internal) }}
              </InputSizedText>
            </template>

            <template v-slot:col-external>
              <FormText
                :name="[index, 'external']"
                char-length="full"
                :aria-label="
                  $str(
                    'x_external_field',
                    'auth_ssosaml',
                    fieldName(row.internal)
                  )
                "
                :placeholder="fieldName(row.internal)"
                :validations="v => [v.required(), v.maxLength(255)]"
              />
            </template>

            <template v-slot:col-update>
              <FormSelect
                :name="[index, 'update']"
                :aria-label="
                  $str(
                    'x_update_local_field',
                    'auth_ssosaml',
                    fieldName(row.internal)
                  )
                "
                :options="updateOptions"
                :disabled="isUpdateDisabled(row)"
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
        </template>
      </SimpleTable>

      <div class="tui-auth_ssosaml-mappingEdit__addFieldRow">
        <AddFieldsButton
          :mapping-options="Object.values(fieldInfo)"
          :added-mappings="existingInternalFields(items)"
          @add="addMappings($event, push)"
        />
      </div>
    </div>
  </FieldArray>
</template>

<script>
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import InputSizedText from 'tui/components/form/InputSizedText';
import InputText from 'tui/components/form/InputText';
import Select from 'tui/components/form/Select';
import RemoveIcon from 'tui/components/icons/Remove';
import { FieldArray, FormText, FormSelect } from 'tui/components/uniform';
import AddFieldsButton from 'auth_ssosaml/components/fields/AddFieldsButton';
import SimpleTable from 'auth_ssosaml/components/ui/simple_table/SimpleTable';
import SimpleTableRow from 'auth_ssosaml/components/ui/simple_table/SimpleTableRow';

export default {
  components: {
    ButtonIcon,
    InputSizedText,
    InputText,
    Select,
    RemoveIcon,
    FieldArray,
    FormText,
    FormSelect,
    AddFieldsButton,
    SimpleTable,
    SimpleTableRow,
  },

  props: {
    path: {
      type: [String, Array],
      required: true,
    },
    fieldInfo: {
      type: Object,
      required: true,
    },
    userIdField: Object,
  },

  data() {
    return {
      updateOptions: [
        {
          id: 'CREATE',
          label: this.$str('update_local_field_create', 'auth_ssosaml'),
        },
        {
          id: 'LOGIN',
          label: this.$str('update_local_field_login', 'auth_ssosaml'),
        },
      ],
    };
  },

  methods: {
    addMappings(mappings, push) {
      mappings.forEach(internal => {
        push({ internal, external: '', update: 'CREATE' });
      });
    },

    fieldName(field) {
      return this.fieldInfo[field] && this.fieldInfo[field].label;
    },

    rowRendered(row) {
      // mappings matching the user identifier are hidden, and removed in the submit logic
      if (this.userIdField && this.userIdField.internal === row.internal) {
        return false;
      }
      return true;
    },

    existingInternalFields(items) {
      const fields = items.map(x => x.internal);
      if (this.userIdField) {
        const field = this.userIdField.internal;
        if (!fields.includes(field)) {
          fields.push(field);
        }
      }
      return fields;
    },

    isUpdateDisabled(row) {
      return row.internal === 'username';
    },
  },
};
</script>

<style lang="scss">
.tui-auth_ssosaml-mappingEdit {
  &__invisibleRemove {
    height: 1px;
    visibility: hidden;
  }

  &__addFieldRow {
    margin-top: var(--gap-2);
  }
}
</style>
