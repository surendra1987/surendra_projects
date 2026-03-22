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
  @author Simon Tegg <simon.tegg@totaralearning.com>
  @module mod_approval
-->

<script>
import { h } from 'vue';
import { Uniform } from 'tui/components/uniform';
import SchemaFormSection from 'mod_approval/components/schema_form/SchemaFormSection';
import Loader from 'tui/components/loading/Loader';
import { result, unique } from 'tui/util';
import { getFieldDef } from '../../js/internal/schema_form/defs';
import { getAllFields } from '../../js/internal/schema_form/schema';
import { loadSchemaData } from 'mod_approval/schema_form';

export default {
  components: {
    Uniform,
    SchemaFormSection,
    Loader,
  },

  props: {
    schema: {
      type: Object,
      required: true,
    },

    /*
     * Initial values for form fields.
     */
    initialValues: {
      type: [Object, Function],
      default: () => ({}),
    },

    value: Object,

    vertical: Boolean,

    display: {
      type: Boolean,
      default: true,
    },

    inputWidth: {
      type: String,
      default: 'limited',
    },

    inputCharLength: String,

    spacing: {
      type: String,
      validator: x => !x || x == 'large',
    },

    enableValidation: {
      type: Boolean,
      default: true,
    },
  },

  emits: ['loaded', 'change', 'submit', 'validation-changed', 'reform-mounted'],

  data() {
    return {
      calculatedInitialValues: {},
      loaded: false,
    };
  },

  computed: {
    sections() {
      const result = [];

      result.push({
        key: 'root',
        title: this.schema.title,
        description: this.schema.description,
        fields: this.schema.fields,
      });

      if (this.schema.sections) {
        this.schema.sections.forEach(section => {
          result.push({
            key: section.key,
            label: section.label,
            fields: section.fields,
            line: section.line,
          });
        });
      }

      return result;
    },
  },

  created() {
    let values = this.$_calcInitialValues(this.schema, {});

    if (this.initialValues) {
      const passedValues = result(this.initialValues);
      Object.entries(passedValues).forEach(([key, value]) => {
        if (value != null) {
          values[key] = value;
        }
      });
    }

    this.calculatedInitialValues = values;
  },

  mounted() {
    // start loading
    this.$_prepare();
  },

  methods: {
    /**
     * Set up the form.
     *
     * - make sure we have the components we need available
     * - load resources for the components
     *
     * @private
     */
    async $_prepare() {
      await loadSchemaData(this.schema);

      // load component requirements
      const fieldTypes = unique(getAllFields(this.schema).map(x => x.type));
      const comps = [];
      fieldTypes.forEach(type => {
        const spec = getFieldDef(type);
        if (spec && spec.rowComponent) {
          comps.push(spec.rowComponent);
        }
        if (spec && spec.fieldComponent) {
          comps.push(spec.fieldComponent);
        }
      });

      this.loaded = true;
      this.$emit('loaded');
    },

    /**
     * Submit form.
     * @param {object} section
     * @public
     * @returns {Promise}
     */
    submit() {
      return this.$refs.uniform.submit();
    },

    /**
     * Try and submit form, without firing event.
     *
     * @public
     * @returns {Promise}
     */
    trySubmit() {
      return this.$refs.uniform.trySubmit();
    },

    /**
     * Focus the first invalid field.
     *
     * @public
     */
    focusFirstInvalid() {
      return this.$refs.uniform.focusFirstInvalid();
    },

    /**
     * Reset form to initial state.
     *
     * @public
     */
    reset() {
      this.$refs.uniform.reset();
    },

    /**
     * Focus the form
     *
     * @public
     */
    focus() {
      this.$refs.uniform.focus();
    },

    /**
     * Set the default values for the schema.
     * @private
     * @param {object} section
     * @param {object} values
     * @returns {object}
     */
    $_calcInitialValues(section, values) {
      if (Array.isArray(section.fields)) {
        section.fields.forEach(field => {
          if (field.default !== undefined) {
            values[field.key] = field.default;
          } else {
            values[field.key] = null;
          }
        });
      }

      // recursively walk through subsections
      if (Array.isArray(section.sections)) {
        section.sections.forEach(section => {
          if (section.fields) {
            this.$_calcInitialValues(section, values);
          }
        });
      }

      return values;
    },

    /**
     * Calculate values for computed form fields.
     *
     * @private
     * @param {object} values
     * @returns {object}
     */
    $_calculateValues(values) {
      values = Object.assign({}, values);
      getAllFields(this.schema).forEach(field => {
        const spec = getFieldDef(field.type);
        if (spec && spec.calculatedValue) {
          values[field.key] = spec.calculatedValue(values[field.key], field, {
            values,
          });
        }
      });
      return values;
    },

    handleChange(values) {
      values = this.$_calculateValues(values);
      this.$emit('change', values);
    },

    handleSubmit(values) {
      values = this.$_calculateValues(values);
      this.$emit('submit', values);
    },

    handleValidationChange(validationResult) {
      this.$emit('validation-changed', validationResult);
    },

    handleUniformMounted() {
      this.$emit('reform-mounted');
    },
  },

  render() {
    if (!this.display) {
      return h(Loader, { loading: false });
    }

    if (!this.loaded) {
      return h(Loader, { loading: true });
    }

    const renderSection = section => {
      return h(
        SchemaFormSection,
        {
          key: section.key,
          spacing: this.spacing,
          fields: section.fields,
          enableValidation: this.enableValidation,
          inputCharLength: this.inputCharLength,
        },
        {
          row: this.$slots.row,
          field: this.$slots.field,
        }
      );
    };

    const renderSections = sections =>
      sections.map(section =>
        this.$slots.section
          ? this.$slots.section({
              section,
              renderSection,
            })
          : renderSection(section)
      );

    return h(
      Uniform,
      {
        ref: 'uniform',
        initialValues: this.calculatedInitialValues,
        vertical: this.vertical,
        inputWidth: this.inputWidth,
        onChange: this.handleChange,
        onSubmit: this.handleSubmit,
        onValidationChanged: this.handleValidationChange,
        onVnodeMounted: this.handleUniformMounted,
      },
      () => [
        this.$slots.sections
          ? this.$slots.sections({
              renderSection,
              renderSections,
              sections: this.sections,
            })
          : renderSections(this.sections),
        this.$slots.below && this.$slots.below(),
      ]
    );
  },
};
</script>
