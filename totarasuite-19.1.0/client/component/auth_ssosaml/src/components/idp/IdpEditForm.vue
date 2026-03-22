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
  <Uniform
    v-model:state="state"
    :errors="errors"
    @submit="$emit('submit', $event)"
  >
    <FormRowStack spacing="large">
      <FormRow
        :label="$str('name', 'core')"
        :helpmsg="$str('idp_name_help', 'auth_ssosaml')"
        required
      >
        <FormText name="label" :validations="v => [v.required()]" />
      </FormRow>

      <FormRow
        v-slot="{ labelId }"
        :label="$str('idp_metadata', 'auth_ssosaml')"
        :helpmsg="$str('idp_metadata_help', 'auth_ssosaml')"
        required
      >
        <InputSet vertical :aria-labelledby="labelId" char-length="full">
          <FormField v-slot="{ attrs, value, update }" name="metadataSource">
            <ToggleSet
              v-bind="attrs"
              :value="value"
              :aria-label="$str('metadata_source', 'auth_ssosaml')"
              @input="update"
            >
              <ToggleButton
                value="URL"
                :text="$str('url', 'core')"
                :aria-label="$str('url', 'core')"
              />
              <ToggleButton
                value="XML"
                :text="$str('xml', 'auth_ssosaml')"
                :aria-label="$str('xml', 'auth_ssosaml')"
              />
            </ToggleSet>
          </FormField>
          <FormText
            v-if="values.metadataSource === 'URL'"
            name="metadataUrl"
            :validations="v => [v.required()]"
            :placeholder="
              $str('idp_metadata_url_field_placeholder', 'auth_ssosaml')
            "
            char-length="30"
          />
          <FormTextarea
            v-if="values.metadataSource === 'XML'"
            name="metadataBlob"
            :rows="5"
            :validations="v => [v.required()]"
            char-length="30"
          />
        </InputSet>
      </FormRow>

      <FormRow
        :label="$str('user_identifier', 'auth_ssosaml')"
        :helpmsg="$str('user_identifier_help', 'auth_ssosaml')"
        required
        content-type="other"
      >
        <InputSet split :stack-below="30" char-length="30">
          <FormRow :label="$str('idp_field', 'auth_ssosaml')" vertical subfield>
            <FormText
              :name="['userIdField', 'external']"
              :validations="v => [v.required(), v.maxLength(255)]"
            />
          </FormRow>
          <FormRow
            :label="$str('local_field', 'auth_ssosaml')"
            vertical
            subfield
          >
            <FormSelect
              :name="['userIdField', 'internal']"
              :options="totaraUserIdFieldOptions"
              :validations="v => [v.required()]"
            />
          </FormRow>
        </InputSet>
      </FormRow>

      <FormRow>
        <Button
          :text="
            advancedVisible
              ? $str('hide_advanced_settings', 'auth_ssosaml')
              : $str('show_advanced_settings', 'auth_ssosaml')
          "
          :styleclass="{ transparent: true }"
          @click="toggleAdvanced"
        />
      </FormRow>

      <div v-show="advancedVisible">
        <AdvancedSettings
          path="advancedSettings"
          :field-info="fieldInfo"
          :name-id-formats="nameIdFormats"
          :default-entity-id="defaultEntityId"
          :user-id-field="values.userIdField"
        />
      </div>

      <FormRow actions>
        <ButtonGroup>
          <Button
            type="submit"
            :text="$str('save', 'totara_core')"
            :styleclass="{ primary: 'true' }"
            :loading="submitting"
          />

          <ActionLink
            :text="$str('cancel', 'core')"
            :disabled="submitting"
            :href="$url(returnUrl)"
          />
        </ButtonGroup>
      </FormRow>
    </FormRowStack>
  </Uniform>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import InputSet from 'tui/components/form/InputSet';
import ActionLink from 'tui/components/links/ActionLink';
import ToggleSet from 'tui/components/toggle/ToggleSet';
import ToggleButton from 'tui/components/toggle/ToggleButton';
import {
  FormField,
  FormRow,
  FormRowStack,
  FormSelect,
  FormText,
  FormTextarea,
  Uniform,
} from 'tui/components/uniform';
import AdvancedSettings from 'auth_ssosaml/components/idp/AdvancedSettings';

export default {
  components: {
    Button,
    ButtonGroup,
    InputSet,
    ActionLink,
    ToggleSet,
    ToggleButton,
    FormField,
    FormRow,
    FormRowStack,
    FormSelect,
    FormText,
    FormTextarea,
    Uniform,
    AdvancedSettings,
  },

  props: {
    initialValues: Object,
    fieldInfo: {
      type: Object,
      required: true,
    },
    nameIdFormats: {
      type: Array,
      required: true,
    },
    submitting: Boolean,
    returnUrl: String,
    userIdFieldNames: {
      type: Object,
      required: true,
    },
    errors: Object,
    defaultEntityId: {
      type: String,
      required: true,
    },
  },

  emits: ['submit', 'update'],

  data() {
    return {
      state: {
        values: this.initialValues,
      },
      totaraUserIdFieldOptions: Object.entries(
        this.userIdFieldNames
      ).map(([id, label]) => ({ id, label })),
      advancedVisible: false,
    };
  },

  computed: {
    isNew() {
      return !this.idp;
    },

    values() {
      return this.state.values;
    },
  },

  watch: {
    values() {
      this.$emit('update', this.values);
    },
  },

  methods: {
    updateValues(values) {
      this.values = values;
      this.$emit('update', values);
    },

    toggleAdvanced() {
      this.advancedVisible = !this.advancedVisible;
    },
  },
};
</script>

<style lang="scss">
.tui-auth_ssosaml-idpEditForm {
  &__subtitle {
    margin-top: var(--gap-2);
  }
}
</style>
