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

  @author Simon Tegg <simon.tegg@totaralearning.com>
  @module mod_approval
-->

<script>
import Loader from 'tui/components/loading/Loader';
import { getFieldDef } from '../../js/internal/schema_form/defs';
import { applyRules, getAllFields } from '../../js/internal/schema_form/schema';
import { loadSchemaData } from 'mod_approval/schema_form';
import { unique } from 'tui/util';
import { h } from 'vue';

export default {
  props: {
    schema: {
      type: Object,
      required: true,
    },
    value: Object,

    display: Boolean,
  },

  emits: ['loaded'],

  data() {
    return { loaded: false };
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

  mounted() {
    // start loading
    this.$_prepare();
  },

  methods: {
    /**
     * Load plugin view components.
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
        if (spec && spec.viewFieldComponent) {
          comps.push(spec.viewFieldComponent);
        }
      });

      this.loaded = true;
      this.$emit('loaded');
    },
  },

  render() {
    if (!this.display) {
      return h(Loader, { props: { loading: false } });
    }

    if (!this.loaded) {
      return h(Loader, { props: { loading: true } });
    }

    const renderFieldFrom = ({
      field,
      value,
      classModifiers,
      viewFieldComponent,
    }) => {
      const valueClasses = { 'tui-mod_approval-schemaView__value': true };
      Object.keys(classModifiers).forEach(modifier => {
        valueClasses[`tui-mod_approval-schemaView__value--${modifier}`] = true;
      });

      if (viewFieldComponent) {
        return h(viewFieldComponent, { value });
      } else {
        const def = getFieldDef(field.type);
        const displayValue =
          def && def.displayText
            ? def.displayText(value, field, { values: this.value })
            : value;
        return h(
          'span',
          { class: valueClasses },
          displayValue != null && displayValue !== false && displayValue != ''
            ? displayValue
            : this.$str('filter_na', 'mod_approval')
        );
      }
    };

    const renderRow = ({ field, rowComponent, renderField }) => {
      const labelChildren = [
        h('span', { class: 'tui-mod_approval-schemaView__label' }, field.label),
      ];

      if (field.required) {
        labelChildren.push(
          h(
            'span',
            {
              class: 'tui-mod_approval-schemaView__required',
              title: this.$str('required', 'core'),
            },
            [
              h('span', { 'aria-hiddden': true }, '*'),
              h('span', { class: 'sr-only' }, this.$str('required', 'core')),
            ]
          )
        );
      }

      return h(
        'div',
        {
          class: 'tui-mod_approval-schemaView__field',
        },
        rowComponent
          ? [h(rowComponent, { heading: field.label })]
          : [
              h(
                'div',
                { class: 'tui-mod_approval-schemaView__labelContainer' },
                labelChildren
              ),
              renderField(),
            ]
      );
    };

    const renderSection = section => {
      return h(
        'div',
        { key: section.key },
        section.fields.map(field => {
          let value = this.value[field.key];
          let viewFieldComponent;
          let rowComponent;
          field = applyRules(field, x => this.value[x]);
          const def = getFieldDef(field.type);

          if (def.visibility && def.visibility.view === false) {
            return null;
          }

          const classModifiers =
            def && def.displayClassModifiers ? def.displayClassModifiers() : {};

          if (def && def.viewFieldComponent) {
            viewFieldComponent = def.viewFieldComponent;
          }

          if (def && def.rowComponent) {
            rowComponent = def.rowComponent;
          }

          const fieldArgs = {
            field,
            value,
            classModifiers,
            viewFieldComponent,
          };

          const args = {
            field,
            value,
            classModifiers,
            rowComponent,
            renderField: this.$slots.field
              ? () => this.$slots.field(fieldArgs)
              : () => renderFieldFrom(fieldArgs),
          };
          return this.$slots.row ? this.$slots.row(args) : renderRow(args);
        })
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

    return h('div', {}, [
      this.$slots.sections
        ? this.$slots.sections({
            renderSection,
            renderSections,
            sections: this.sections,
          })
        : renderSections(this.sections),
    ]);
  },
};
</script>

<style lang="scss">
.tui-mod_approval-schemaView {
  &__field {
    display: flex;
    margin-top: var(--gap-6);
  }

  &__labelContainer {
    flex-basis: 50%;
  }

  &__label {
    min-width: 0;
    margin: 0;
    padding: 0 var(--gap-1) 0 0;
    font-weight: var(--label-weight);
  }

  &__required {
    color: var(--color-prompt-alert);
  }

  &__value {
    white-space: pre-line;

    &--bold {
      font-weight: bold;
    }
  }
}
</style>
