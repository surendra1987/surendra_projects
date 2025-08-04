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
    :validate="validate"
    @change="handleChange"
    @submit="submit"
  >
    <FormRowStack spacing="large">
      <FormRow
        v-if="logoEditable"
        :label="$str('formbrand_label_logo', 'totara_tui')"
        :is-stacked="true"
      >
        <ImageUploadSetting
          :key="key"
          :metadata="fileData.sitelogo"
          :aria-describedby="$id('formbrand-logo-details')"
          :aria-label-extension="$str('formbrand_label_logo', 'totara_tui')"
          :context-id="contextId"
          :show-delete="showDelete(fileData.sitelogo)"
          @update="saveImage"
          @delete="resetImage"
        />
        <FormRowDetails :id="$id('formbrand-logo-details')">
          {{ $str('formbrand_details_logo', 'theme_inspire') }}
        </FormRowDetails>
      </FormRow>

      <FormRow
        v-if="logomarkEditable"
        :label="$str('formbrand_label_logomark', 'theme_inspire')"
        :is-stacked="true"
      >
        <ImageUploadSetting
          :key="key"
          :metadata="fileData.sitelogomark"
          :aria-describedby="$id('formbrand-logomark-details')"
          :aria-label-extension="
            $str('formbrand_label_logomark', 'theme_inspire')
          "
          :context-id="contextId"
          :show-delete="showDelete(fileData.sitelogomark)"
          @update="saveImage"
          @delete="resetImage"
        />
        <FormRowDetails :id="$id('formbrand-logomark-details')">
          {{ $str('formbrand_details_logomark', 'theme_inspire') }}
        </FormRowDetails>
      </FormRow>

      <!-- Not allowing alt text change if image can't be changed -->
      <FormRow
        v-if="logoEditable"
        :label="$str('formbrand_label_logoalttext', 'totara_tui')"
        :is-stacked="true"
        :aria-describedby="$id('formbrand-logoalttext-details')"
      >
        <FormText :name="['formbrand_field_logoalttext', 'value']" required />
        <FormRowDetails :id="$id('formbrand-logoalttext-details')">
          {{ $str('formbrand_details_logoalttext', 'theme_inspire') }}
        </FormRowDetails>
      </FormRow>

      <FormRow
        v-if="faviconEditable"
        :label="$str('formbrand_label_favicon', 'totara_tui')"
        :is-stacked="true"
      >
        <ImageUploadSetting
          :key="key"
          :metadata="fileData.sitefavicon"
          :aria-describedby="$id('formbrand-favicon-details')"
          :aria-label-extension="$str('formbrand_label_favicon', 'totara_tui')"
          :context-id="contextId"
          :show-delete="showDelete(fileData.sitefavicon)"
          @update="saveImage"
          @delete="resetImage"
        />
        <FormRowDetails :id="$id('formbrand-favicon-details')">
          {{ $str('formbrand_details_favicon', 'theme_inspire') }}
        </FormRowDetails>
      </FormRow>

      <FormRow
        v-if="navBgColorEditable"
        :label="$str('formbrand_label_navbg', 'theme_inspire')"
        :is-stacked="true"
        :aria-describedby="
          $id('formbrand-navbg-details') + ' ' + $id('formbrand-navbg-defaults')
        "
      >
        <FormColor
          :name="['nav-bg-color', 'value']"
          :validations="v => [v.required(), v.colorValueHex()]"
        />
        <FormRowDefaults :id="$id('formbrand-navbg-defaults')">
          {{
            theme_settings.getCSSVarDefault(
              mergedProcessedCssVariableData,
              'nav-bg-color'
            )
          }}
        </FormRowDefaults>
      </FormRow>

      <FormRow
        v-if="navTextColorEditable"
        :label="$str('formbrand_label_navtext', 'theme_inspire')"
        :is-stacked="true"
        :aria-describedby="
          $id('formbrand-navtext-details') +
            ' ' +
            $id('formbrand-navtext-defaults')
        "
      >
        <FormColor
          :name="['nav-text-color', 'value']"
          :validations="v => [v.required(), v.colorValueHex()]"
        />
        <FormRowDefaults :id="$id('formbrand-navtext-defaults')">
          {{
            theme_settings.getCSSVarDefault(
              mergedProcessedCssVariableData,
              'nav-text-color'
            )
          }}
        </FormRowDefaults>
      </FormRow>

      <FormRow
        v-if="navSelectedColorEditable"
        :label="$str('formbrand_label_navselected', 'theme_inspire')"
        :is-stacked="true"
        :aria-describedby="
          $id('formbrand-navselected-details') +
            ' ' +
            $id('formbrand-navselected-defaults')
        "
      >
        <FormColor
          :name="['nav-selected-color', 'value']"
          :validations="v => [v.required(), v.colorValueHex()]"
        />
        <FormRowDefaults :id="$id('formbrand-navselected-defaults')">
          {{
            theme_settings.getCSSVarDefault(
              mergedProcessedCssVariableData,
              'nav-selected-color'
            )
          }}
        </FormRowDefaults>
        <FormRowDetails :id="$id('formbrand-navselected-details')">
          {{ $str('formbrand_details_navselected', 'theme_inspire') }}
        </FormRowDetails>
      </FormRow>

      <FormRow
        :label="$str('formbrand_label_displaynavicons', 'theme_inspire')"
        :is-stacked="true"
        :aria-describedby="$id('formbrand-displaynavicons-defaults')"
      >
        <InputSizedText>
          <FormCheckbox
            :name="['formbrand_field_displaynavicons', 'value']"
            :aria-label="
              $str('formbrand_label_displaynavicons', 'theme_inspire')
            "
          >
            {{ $str('enabled', 'totara_core') }}
          </FormCheckbox>
        </InputSizedText>
      </FormRow>

      <FormRow :label="$str('formbrand_label_expandnav', 'theme_inspire')">
        <FormRadioGroup
          :name="['formbrand_field_expandnav', 'value']"
          :aria-label="$str('formbrand_label_expandnav', 'theme_inspire')"
        >
          <Radio value="expand">{{
            $str('formbrand_option_expandnav_expand', 'theme_inspire')
          }}</Radio>
          <Radio value="collapse">{{
            $str('formbrand_option_expandnav_collapse', 'theme_inspire')
          }}</Radio>
        </FormRadioGroup>
        <FormRowDetails :id="$id('formbrand-expandnav-details')">
          {{ $str('formbrand_details_expandnav', 'theme_inspire') }}
        </FormRowDetails>
      </FormRow>

      <FormRow actions>
        <ButtonGroup>
          <Button
            :styleclass="{ primary: true }"
            :text="$str('save', 'totara_core')"
            :aria-label="
              $str(
                'saveextended',
                'totara_core',
                $str('tabbrand', 'totara_tui') +
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
  FormColor,
  FormText,
  FormCheckbox,
  FormRadioGroup,
} from 'tui/components/uniform';
import ImageUploadSetting from 'tui/components/theme_settings/ImageUploadSetting';
import FormRowDetails from 'tui/components/form/FormRowDetails';
import FormRowDefaults from 'tui/components/form/FormRowDefaults';
import InputSizedText from 'tui/components/form/InputSizedText';
import Radio from 'tui/components/form/Radio';
import Button from 'tui/components/buttons/Button';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';

// Mixins
import FileMixin, { fileMixinData } from 'tui/mixins/settings_form_file_mixin';

export default {
  components: {
    Uniform,
    FormRow,
    FormRowStack,
    FormRowDetails,
    FormRowDefaults,
    FormColor,
    FormText,
    ImageUploadSetting,
    FormCheckbox,
    InputSizedText,
    FormRadioGroup,
    Radio,
    Button,
    ButtonGroup,
  },

  mixins: [FileMixin],

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
     * Array of Objects, each describing the properties for fields that are part
     * of this Form. There is only an Object present in this Array if it was
     * present in Theme JSON data mapping (not GraphQL query), and the values
     * within each Object are defaults, not previously saved data.
     */
    mergedDefaultCssVariableData: {
      type: Object,
      default: function() {
        return {};
      },
    },
    /**
     *  Array of Objects, each describing the properties for fields that are part
     * of this Form. There is only an Object present in this Array if it was
     * present in Theme JSON data mapping (not GraphQL query), and the values
     * within each Object have processed/resolved values.
     */
    mergedProcessedCssVariableData: {
      type: Array,
      default: function() {
        return [];
      },
    },

    /**
     *  Saving state, controlled by parent component GraphQl mutation handling
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
      ...fileMixinData(),
      initialValues: {
        formbrand_field_logoalttext: {
          value: null,
          type: 'text',
        },
        formbrand_field_displaynavicons: {
          value: true,
          type: 'boolean',
        },
        formbrand_field_expandnav: {
          value: 'expand',
          type: 'text',
        },
        'nav-bg-color': {
          value: null,
          type: null, // supplied by Theme-based JSON metadata
        },
        'nav-text-color': {
          value: null,
          type: null, // supplied by Theme-based JSON metadata
        },
        'nav-selected-color': {
          value: null,
          type: null, // supplied by Theme-based JSON metadata
        },
      },
      fileData: {
        sitefavicon: null,
        sitelogo: null,
        sitelogomark: null,
      },
      initialValuesSet: false,
      errorsForm: null,
      valuesForm: null,
      resultForm: null,
      theme_settings: theme_settings,
      contextIdNumber: parseInt(this.contextId),
    };
  },

  computed: {
    logoEditable() {
      return this.canEditSetting('sitelogo');
    },
    logomarkEditable() {
      return this.canEditSetting('sitelogomark');
    },
    faviconEditable() {
      return this.canEditSetting('sitefavicon');
    },
    navBgColorEditable() {
      return this.canEditSetting('nav-bg-color');
    },
    navTextColorEditable() {
      return this.canEditSetting('nav-text-color');
    },
    navSelectedColorEditable() {
      return this.canEditSetting('nav-selected-color');
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
      this.mergedProcessedCssVariableData,
      this.savedFormFieldData,
      this.valuesForm || [],
    ]);
    this.initialValues = this.theme_settings.getResolvedInitialValues(
      mergedFormData
    );
    this.initialValuesSet = true;
    this.$emit('mounted', {
      category: 'brand',
      values: this.formatDataForMutation(this.initialValues),
    });
  },

  methods: {
    validate() {
      const errors = {};
      return errors;
    },

    handleChange(values) {
      this.valuesForm = values;
      if (this.errorsForm) {
        this.errorsForm = null;
      }
    },

    /**
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
        form: 'brand',
        fields: [],
        files: [],
      };

      // handle non-image upload form fields
      Object.keys(currentValues).forEach(field => {
        data.fields.push({
          name: field,
          type: currentValues[field].type,
          value: String(currentValues[field].value),
        });
      });

      // image upload form field data formatting as it is handled
      // differently to other form fields in our GraphQL mutation
      Object.keys(this.fileData).forEach(file => {
        if (this.fileData[file]) {
          data.files.push(this.fileData[file]);
        }
      });

      return data;
    },
  },
};
</script>
