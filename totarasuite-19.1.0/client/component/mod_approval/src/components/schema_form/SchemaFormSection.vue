<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2021 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Simon Chester <simon.chester@totaralearning.com>
  @module mod_approval
-->

<script>
import { h } from 'vue';
import { FormRow, FormRowStack } from 'tui/components/uniform';
import { getFieldDef } from '../../js/internal/schema_form/defs';
import { isVisible, applyRules } from '../../js/internal/schema_form/schema';
import FieldValidator from '../../js/internal/schema_form/FieldValidator';
import FormRowDetails from 'tui/components/form/FormRowDetails';

export default {
  components: {
    FormRow,
    FormRowStack,
    FormRowDetails,
  },

  inject: ['reformScope'],

  props: {
    spacing: {
      type: String,
      default: 'large',
    },

    fields: Array,

    inputCharLength: String,

    enableValidation: {
      type: Boolean,
      default: true,
    },
  },

  computed: {
    validationDeps() {
      return {
        fields: this.fields,
        enableValidation: this.enableValidation,
      };
    },
  },

  watch: {
    validationDeps: {
      handler() {
        // re-register validator to force update -- will not automatically re-run just because our data changed
        this.reformScope.unregister('validator', null, this.$_validate);
        this.reformScope.register('validator', null, this.$_validate);
      },
    },
  },

  created() {
    this.fieldValidator = new FieldValidator();
    this.reformScope.register('validator', null, this.$_validate);
    this.reformScope.register('changeListener', [], this.$_handleFormChange);
  },

  beforeUnmount() {
    if (this.reformScope) {
      this.reformScope.unregister('validator', null, this.$_validate);
      this.reformScope.unregister(
        'changeListener',
        [],
        this.$_handleFormChange
      );
    }
  },

  methods: {
    $_handleFormChange() {
      // force update so conditions are recalculated
      this.$forceUpdate();
    },

    /**
     * Validate form
     *
     * @param {object} value
     * @returns {object} errors
     */
    $_validate(value) {
      const { fields, enableValidation } = this.validationDeps;
      if (!enableValidation) {
        return null;
      }

      const getValue = key => value[key];
      const fieldValidationContext = { getValue };

      const result = {};

      if (fields) {
        fields.forEach(field => {
          const handle = field;
          field = applyRules(field, getValue);
          const visible = isVisible(field, getValue);
          // skip validating if field is disabled or hidden
          if (field.disabled || !visible) {
            return;
          }
          const validator = this.fieldValidator.getValidator(handle, field);
          if (validator) {
            result[field.key] = validator(
              value[field.key],
              fieldValidationContext
            );
          }
        });
      }

      return result;
    },
  },

  render() {
    const renderField = field => {
      const spec = getFieldDef(field.type);
      const comp = spec && spec.fieldComponent;

      if (!comp) {
        console.warn(
          `Missing component for form field ${field.key} of type ${field.type}`
        );
        return;
      }

      const props = { field };
      if (this.inputCharLength) {
        props.charLength = this.inputCharLength;
      }

      return h(comp, props);
    };

    const renderRow = field => {
      field = applyRules(field, this.reformScope.getValue);
      const spec = getFieldDef(field.type);
      const row = spec && spec.rowComponent;
      const descId = field.instruction ? this.$id(field.key + '-desc') : '';
      if (row) {
        return h(row, {
          key: field.key,
          heading: field.label,
        });
      }
      return h(
        FormRow,
        {
          key: field.key,
          label: field.label,
          required: field.required,
          ariaDescribedby: descId,
        },
        {
          default: () => [
            this.$slots.field
              ? this.$slots.field({ field, renderField })
              : renderField(field),
            field.instruction
              ? h(FormRowDetails, { id: descId }, () => [field.instruction])
              : null,
          ],
          'help-message':
            field.help || field.help_html
              ? () =>
                  field.help_html
                    ? h('div', { innerHTML: field.help_html })
                    : field.help
              : null,
        }
      );
    };

    return h(
      FormRowStack,
      {
        props: {
          spacing: this.spacing,
        },
      },
      () =>
        this.fields &&
        this.fields.map(field => {
          if (!isVisible(field, this.reformScope.getValue)) {
            return;
          }
          return this.$slots.row
            ? this.$slots.row({ field, renderRow })
            : renderRow(field);
        })
    );
  },
};
</script>
