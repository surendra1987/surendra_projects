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
  <FormScope :path="path">
    <Tabs>
      <Tab
        id="settings"
        :name="$str('advanced_settings', 'auth_ssosaml')"
        :label-extra-text="
          settingsError ? ' (' + $str('error', 'core') + ')' : null
        "
        always-render
      >
        <template v-if="settingsError" v-slot:label-extra>
          <InvalidIcon state="alert" :alt="$str('error', 'core')" />
        </template>

        <FormRowStack spacing="large">
          <FormRow
            :label="$str('new_users', 'auth_ssosaml')"
            :helpmsg="$str('new_users_help', 'auth_ssosaml')"
          >
            <InputSizedText>
              <FormCheckbox name="createUsers">
                {{ $str('automatically_create', 'auth_ssosaml') }}
              </FormCheckbox>
            </InputSizedText>
          </FormRow>

          <FormRow
            :label="$str('existing_users_label', 'auth_ssosaml')"
            :helpmsg="$str('existing_users_help', 'auth_ssosaml')"
          >
            <FormField
              v-slot="{ attrs, labelId, value, update, touch }"
              name="existingUsers"
            >
              <ExistingUsers
                v-bind="attrs"
                :aria-labelledby="labelId"
                :value="value"
                @input="
                  update($event);
                  touch();
                "
              />
            </FormField>
          </FormRow>

          <FormRow :label="$str('saml_nameid_format', 'auth_ssosaml')">
            <FormSelect name="nameIdFormat" :options="nameIdFormatOptions" />
          </FormRow>

          <FormRow :label="$str('saml_entity_id', 'auth_ssosaml')">
            <FormText name="entityId" :validations="v => [v.maxLength(1024)]" />
            <FormRowDefaults>
              {{ defaultEntityId }}
            </FormRowDefaults>
          </FormRow>

          <FormRow :label="$str('logout_behavior', 'auth_ssosaml')">
            <InputSizedText>
              <FormCheckbox name="logoutIdp">
                {{ $str('logout_at_idp', 'auth_ssosaml') }}
              </FormCheckbox>
            </InputSizedText>
          </FormRow>

          <FormRow
            :label="$str('redirect_after_logout', 'auth_ssosaml')"
            :helpmsg="$str('redirect_after_logout_help', 'auth_ssosaml')"
          >
            <FormText name="logoutUrl" :validate="validateLogoutUrl" />
          </FormRow>

          <FormRow
            :label="$str('delimiter', 'auth_ssosaml')"
            :helpmsg="$str('delimiter_help', 'auth_ssosaml')"
          >
            <FormText name="attributeDelimiter" placeholder="," />
            <FormRowDefaults>
              {{ $str('default_delimiter', 'auth_ssosaml') }}
            </FormRowDefaults>
          </FormRow>

          <FormRow
            :label="$str('signatures', 'auth_ssosaml')"
            :helpmsg="$str('signatures_help', 'auth_ssosaml')"
          >
            <FormCheckboxGroup name="signatures" use-object>
              <Checkbox value="signMetadata">
                {{ $str('saml_sign_metadata', 'auth_ssosaml') }}
              </Checkbox>
              <Checkbox value="signAuthnRequests">
                {{ $str('saml_authnrequests_signed', 'auth_ssosaml') }}
              </Checkbox>
              <Checkbox value="wantsAssertionsSigned">
                {{ $str('saml_wants_assertions_signed', 'auth_ssosaml') }}
              </Checkbox>
            </FormCheckboxGroup>
          </FormRow>

          <FormRow
            :label="$str('setting_login_hide', 'auth_ssosaml')"
            :helpmsg="$str('setting_login_hide_help', 'auth_ssosaml')"
          >
            <InputSizedText>
              <FormCheckbox name="loginHide">
                {{ $str('hide', 'core') }}
              </FormCheckbox>
            </InputSizedText>
          </FormRow>

          <FormRow
            :label="$str('setting_debug', 'auth_ssosaml')"
            :helpmsg="$str('setting_debug_help', 'auth_ssosaml')"
          >
            <InputSizedText>
              <FormCheckbox name="debug">
                {{ $str('turn_on', 'auth_ssosaml') }}
              </FormCheckbox>
            </InputSizedText>
          </FormRow>
        </FormRowStack>
      </Tab>

      <Tab
        id="mappings"
        :name="$str('field_mappings', 'auth_ssosaml')"
        :label-extra-text="
          mappingsError ? ' (' + $str('error', 'core') + ')' : null
        "
        always-render
      >
        <template v-if="mappingsError" v-slot:label-extra>
          <InvalidIcon state="alert" :alt="$str('error', 'core')" />
        </template>
        <MappingEdit
          path="fieldMaps"
          :user-id-field="userIdField"
          :field-info="fieldInfo"
        />
      </Tab>
    </Tabs>
  </FormScope>
</template>

<script>
import Checkbox from 'tui/components/form/Checkbox';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';
import InputSizedText from 'tui/components/form/InputSizedText';
import Tab from 'tui/components/tabs/Tab';
import Tabs from 'tui/components/tabs/Tabs';
import InvalidIcon from 'tui/components/icons/Invalid';
import {
  FormCheckbox,
  FormCheckboxGroup,
  FormField,
  FormRow,
  FormRowStack,
  FormScope,
  FormSelect,
  FormText,
} from 'tui/components/uniform';
import MappingEdit from 'auth_ssosaml/components/fields/MappingEdit';
import ExistingUsers from 'auth_ssosaml/components/idp/settings/ExistingUsers';

export default {
  components: {
    Checkbox,
    FormRowDefaults,
    InputSizedText,
    Tab,
    Tabs,
    InvalidIcon,
    FormCheckbox,
    FormCheckboxGroup,
    FormField,
    FormRow,
    FormRowStack,
    FormScope,
    FormSelect,
    FormText,
    MappingEdit,
    ExistingUsers,
  },

  inject: ['reformScope'],

  props: {
    path: {
      type: String,
      required: true,
    },
    defaultEntityId: {
      type: String,
      required: true,
    },
    fieldInfo: {
      type: Object,
      required: true,
    },
    nameIdFormats: {
      type: Array,
      required: true,
    },
    userIdField: Object,
  },

  computed: {
    values() {
      return this.reformScope.getValue(this.path);
    },

    settingsError() {
      const errors = this.reformScope.getError([this.path]);
      return (
        Boolean(errors) &&
        Object.entries(errors).some(
          ([key, value]) => key != 'fieldMaps' && Boolean(value)
        )
      );
    },

    mappingsError() {
      const errors = this.reformScope.getError([this.path, 'fieldMaps']);
      return (
        Boolean(errors) &&
        errors.some(row => {
          if (!row) {
            return false;
          }
          if (typeof row === 'object') {
            return Object.values(row).some(x => Boolean(x));
          }
          return true;
        })
      );
    },

    nameIdFormatOptions() {
      return this.nameIdFormats.map(x => ({
        id: x,
        label: x.split(':').slice(-1)[0], // just render the last component
      }));
    },
  },

  methods: {
    validateLogoutUrl(url) {
      try {
        if (url) {
          new URL(url);
        }
      } catch (e) {
        return this.$str('invalidurl', 'error');
      }
    },
  },
};
</script>
