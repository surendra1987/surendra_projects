<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Dave Wallace <dave.wallace@totara.com>
  @package tui
-->

<template>
  <Uniform
    v-if="initialValuesSet"
    :initial-values="initialValues"
    :errors="errorsForm"
    @change="handleChange"
    @submit="submit"
  >
    <FormRowStack spacing="large">
      <FormRow
        v-if="customFooterEditable"
        :label="$str('formcustom_label_customfooter', 'totara_tui')"
        :is-stacked="true"
        :aria-describedby="$id('formcustom-customfooter-details')"
      >
        <FormTextarea
          :name="['formcustom_field_customfooter', 'value']"
          :rows="rows('formcustom_field_customfooter', 6, 20)"
          char-length="full"
        />
        <FormRowDetails :id="$id('formcustom-customfooter-details')">
          {{ $str('formcustom_details_customfooter', 'totara_tui') }}
        </FormRowDetails>
      </FormRow>

      <FormRow
        v-if="customCssEditable"
        :label="$str('formcustom_label_customcss', 'totara_tui')"
        :is-stacked="true"
        :aria-describedby="$id('formcustom-customcss-details')"
      >
        <FormTextarea
          :name="['formcustom_field_customcss', 'value']"
          spellcheck="false"
          :rows="rows('formcustom_field_customcss', 6, 30)"
          char-length="full"
        />
        <FormRowDetails :id="$id('formcustom-customcss-details')">
          {{ $str('formcustom_details_customcss', 'totara_tui') }}
        </FormRowDetails>
      </FormRow>
    </FormRowStack>

    <FormRowStack spacing="large">
      <Collapsible
        :label="$str('formcustom_group_notifications', 'theme_inspire')"
        :initial-state="false"
      >
        <FormRowStack spacing="large">
          <FormRow
            v-slot="{ id }"
            :label="
              $str('formcustom_label_notificationshtmlheader', 'theme_inspire')
            "
            :is-stacked="true"
          >
            <FormField
              v-slot="{ value, update, labelId }"
              :name="['formcustom_field_notificationshtmlheader', 'value']"
              char-length="full"
            >
              <Editor
                :id="id"
                :value="value"
                :default-format="htmlFormat"
                :context-id="contextIdNumber"
                :usage-identifier="{
                  component: 'theme_inspire',
                  area: 'formcustom_notifications_htmlheader',
                }"
                :aria-describedby="
                  $id('formcustom-notifications-htmlheader-details')
                "
                :aria-labelledby="labelId"
                variant="standard"
                :lock-format="true"
                @input="update"
              />
            </FormField>
            <FormRowDetails
              :id="$id('formcustom-notifications-htmlheader-details')"
            >
              {{
                $str(
                  'formcustom_details_notificationshtmlheader',
                  'theme_inspire'
                )
              }}
            </FormRowDetails>
          </FormRow>

          <FormRow
            v-slot="{ id }"
            :label="
              $str('formcustom_label_notificationshtmlfooter', 'theme_inspire')
            "
          >
            <FormField
              v-slot="{ value, update, labelId }"
              :name="['formcustom_field_notificationshtmlfooter', 'value']"
              char-length="full"
            >
              <Editor
                :id="id"
                :value="value"
                :default-format="htmlFormat"
                :context-id="contextIdNumber"
                :usage-identifier="{
                  component: 'theme_inspire',
                  area: 'formcustom_notifications_htmlfooter',
                }"
                :aria-describedby="
                  $id('formcustom-notifications-htmlfooter-details')
                "
                :aria-labelledby="labelId"
                variant="standard"
                :lock-format="true"
                @input="update"
              />
            </FormField>
            <FormRowDetails
              :id="$id('formcustom-notifications-htmlfooter-details')"
            >
              {{
                $str(
                  'formcustom_details_notificationshtmlfooter',
                  'theme_inspire'
                )
              }}
            </FormRowDetails>
          </FormRow>

          <FormRow
            :label="
              $str('formcustom_label_notificationstextfooter', 'theme_inspire')
            "
            :is-stacked="true"
            :aria-describedby="
              $id('formcustom-notifications-textfooter-details')
            "
            :aria-label="
              $str('formcustom_label_notificationstextfooter', 'theme_inspire')
            "
          >
            <FormTextarea
              :name="['formcustom_field_notificationstextfooter', 'value']"
              spellcheck="false"
              :rows="rows('formcustom_field_notificationstextfooter', 8, 30)"
              char-length="full"
            />
            <FormRowDetails
              :id="$id('formcustom-notifications-textfooter-details')"
            >
              {{
                $str(
                  'formcustom_details_notificationstextfooter',
                  'theme_inspire'
                )
              }}
            </FormRowDetails>
          </FormRow>
          <FormRow>
            <InputSet>
              <Button
                :styleclass="{ primary: false }"
                :text="$str('test_email_notification', 'totara_core')"
                :disabled="isSending"
                @click="sendEmailNotification"
              />
              <InfoIconButton
                class="tui-settingsformcustom__testEmailInfoButton"
                :is-help-for="$str('test_email_notification', 'totara_core')"
              >
                {{ $str('test_email_notification_help', 'totara_core') }}
              </InfoIconButton>
            </InputSet>
          </FormRow>
        </FormRowStack>
      </Collapsible>

      <FormRow actions>
        <ButtonGroup>
          <Button
            :styleclass="{ primary: true }"
            :text="$str('save', 'totara_core')"
            :aria-label="
              $str(
                'saveextended',
                'totara_core',
                $str('tabcustom', 'totara_tui') +
                  ' ' +
                  $str('settings', 'totara_core')
              )
            "
            :disabled="isSaving"
            type="submit"
          />
        </ButtonGroup>
      </FormRow>
    </FormRowStack>
  </Uniform>
</template>

<script>
import theme_settings from 'tui/lib/theme_settings';
import {
  Uniform,
  FormRow,
  FormRowStack,
  FormField,
  FormTextarea,
} from 'tui/components/uniform';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import Button from 'tui/components/buttons/Button';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import Editor from 'tui/components/editor/Editor';
import Collapsible from 'tui/components/collapsible/Collapsible';
import { Format } from 'tui/editor';
import InfoIconButton from 'tui/components/buttons/InfoIconButton';
import { notify } from 'tui/notifications';
import InputSet from 'tui/components/form/InputSet';

// GraphQL
import tuiSendEmailNotification from 'core/graphql/theme_settings_send_email_notification';

export default {
  components: {
    Uniform,
    FormField,
    FormRow,
    FormRowStack,
    FormRowDetails,
    FormTextarea,
    Button,
    ButtonGroup,
    Editor,
    InfoIconButton,
    InputSet,
    Collapsible,
  },

  props: {
    /**
     * Array of Objects, each describing the properties for fields that are part
     * of this Form. There is only an Object present in this Array if it came
     * from the server as it was previously saved
     */
    savedFormFieldData: {
      type: Array,
      default: function() {
        return [];
      },
    },

    /**
     * Saving state, controlled by parent component GraphQl mutation handling
     */
    isSaving: {
      type: Boolean,
      default: function() {
        return false;
      },
    },

    /**
     *  Context ID.
     */
    contextId: [Number, String],

    /**
     * Tenant ID or null if global/multi-tenancy not enabled.
     */
    selectedTenantId: Number,

    /**
     *  Customizable tenant settings
     */
    customizableTenantSettings: {
      type: [Array, String],
      required: false,
    },
  },

  emits: ['mounted', 'submit'],

  data() {
    return {
      initialValues: {
        formcustom_field_customcss: {
          value: '',
          type: 'text',
        },
        formcustom_field_customfooter: {
          value: '',
          type: 'text',
        },
        formcustom_field_notificationshtmlheader: {
          value: '',
          type: 'html',
        },
        formcustom_field_notificationshtmlfooter: {
          value: '',
          type: 'html',
        },
        formcustom_field_notificationstextfooter: {
          value: '',
          type: 'text',
        },
      },
      editorFields: {
        formcustom_field_notificationshtmlheader: {
          format: Format.HTML,
        },
        formcustom_field_notificationshtmlfooter: {
          format: Format.HTML,
        },
      },
      initialValuesSet: false,
      theme_settings: theme_settings,
      errorsForm: null,
      valuesForm: null,
      resultForm: null,
      isSending: false,
      htmlFormat: Format.HTML,
      contextIdNumber: parseInt(this.contextId),
    };
  },

  computed: {
    customFooterEditable() {
      return this.canEditSetting('formcustom_field_customfooter');
    },
    customCssEditable() {
      return this.canEditSetting('formcustom_field_customcss');
    },

    customHTMLHeaderEditable() {
      return this.canEditSetting('formcustom_field_notificationshtmlheader');
    },
    customHTMLFooterEditable() {
      return this.canEditSetting('formcustom_field_notificationshtmlfooter');
    },
    customPlaintextFooterEditable() {
      return this.canEditSetting('formcustom_field_notificationstextfooter');
    },
  },

  /**
   * Prepare data for consumption within Uniform
   **/
  mounted() {
    // Set the data for this Form based on (in order):
    // - use previously saved Form data from GraphQL query
    // - missing field data then supplied by Theme JSON mapping data
    // - then locally held state until (takes precedence until page is reloaded)
    let mergedFormData = this.theme_settings.mergeFormData(this.initialValues, [
      this.savedFormFieldData,
      this.valuesForm || [],
    ]);
    this.initialValues = this.theme_settings.getResolvedInitialValues(
      mergedFormData
    );
    this.initialValues = this.theme_settings.resolveEditorContentFields(
      this.initialValues,
      this.editorFields
    );

    this.initialValuesSet = true;
    this.$emit('mounted', { category: 'custom', values: this.initialValues });
  },

  methods: {
    handleChange(values) {
      this.valuesForm = values;
      if (this.errorsForm) {
        this.errorsForm = null;
      }
    },

    /**
     * Check whether the specific setting can be customized
     * @param {String} key
     * @return {Boolean}
     */
    canEditSetting(key) {
      if (!this.selectedTenantId) {
        return true;
      }

      if (!this.customizableTenantSettings) {
        return false;
      }

      if (Array.isArray(this.customizableTenantSettings)) {
        return this.customizableTenantSettings.includes(key);
      }

      return this.customizableTenantSettings === '*';
    },

    /**
     * Adjust the height of a textarea field as the user types, up to
     * a supplied limit, which then invokes a scrollbar
     **/
    rows(field, minLines, maxLines) {
      let text = '';
      if (this.valuesForm && field in this.valuesForm) {
        text = this.valuesForm[field].value;
      } else if (this.initialValues && field in this.initialValues) {
        text = this.initialValues[field].value;
      }
      let lines = (text.match(/\n/g) || []).length + 1;
      if (lines < minLines) {
        return minLines;
      }
      if (lines > maxLines) {
        return maxLines;
      }
      return lines;
    },

    /**
     * Handle submission of an embedded form.
     *
     * @param {Object} currentValues The submitted form data.
     */
    submit(currentValues) {
      if (this.errorsForm) {
        this.errorsForm = null;
      }
      this.resultForm = currentValues;

      let dataToMutate = this.formatDataForMutation(currentValues);
      this.$emit('submit', dataToMutate);
    },

    /**
     * Takes Form field data and formats it to meet GraphQL mutation expectations
     *
     * @param {Object} currentValues The submitted form data.
     * @return {Object}
     **/
    formatDataForMutation(currentValues) {
      let data = {
        form: 'custom',
        fields: [],
      };

      // handle non-image upload form fields
      Object.keys(currentValues).forEach(field => {
        if (!this.canEditSetting(field)) return;

        let value;
        if (
          Object.keys(this.editorFields).find(
            editorField => editorField === field
          )
        ) {
          value = currentValues[field].value.getContent();
          value = value ? value : '';
        } else {
          value = String(currentValues[field].value);
        }
        data.fields.push({
          name: field,
          type: currentValues[field].type,
          value: value,
        });
      });

      return data;
    },

    async sendEmailNotification() {
      this.isSending = true;
      const values = this.valuesForm || this.initialValues;

      try {
        const { data } = await this.$apollo.mutate({
          mutation: tuiSendEmailNotification,
          variables: {
            html_header: values[
              'formcustom_field_notificationshtmlheader'
            ].value.getContent(),
            html_footer: values[
              'formcustom_field_notificationshtmlfooter'
            ].value.getContent(),
            text_footer:
              values['formcustom_field_notificationstextfooter'].value,
            tenant_id: this.selectedTenantId,
          },
        });

        if (data['core_theme_settings_send_email_notification']) {
          notify({
            message: this.$str('settings_email_send_success', 'totara_tui'),
            type: 'success',
          });
        } else {
          notify({
            message: this.$str('settings_email_send_error', 'totara_tui'),
            type: 'error',
          });
        }
      } catch (e) {
        notify({
          message: this.$str('settings_email_send_error', 'totara_tui'),
          type: 'error',
        });
      }
      this.isSending = false;
    },
  },
};
</script>

<style lang="scss">
.tui-settingsFormBrand__testEmailInfoButton {
  align-self: center;
}
</style>
