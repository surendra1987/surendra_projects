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
import { unique } from 'tui/util';
import Loader from 'tui/components/loading/Loader';
import { Uniform } from 'tui/components/uniform';
import SchemaFormSectionAdmin from 'mod_approval/components/schema_form/SchemaFormSectionAdmin';
import { loadSchemaData } from 'mod_approval/schema_form';
import { getFieldDef } from '../../js/internal/schema_form/defs';
import { getAllFields } from '../../js/internal/schema_form/schema';
import { produce } from 'tui/immutable';

export default {
  components: {
    Uniform,
    SchemaFormSectionAdmin,
  },

  props: {
    display: {
      type: Boolean,
      default: true,
    },
    sections: Array,
    spacing: String,
    fullWidth: Boolean,
  },

  emits: ['loaded'],

  data() {
    return {
      loaded: false,
      formState: { values: {} },
    };
  },

  mounted() {
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
      const schema = { sections: this.sections };
      await loadSchemaData(schema);
      // load component requirements
      const fieldTypes = unique(getAllFields(schema).map(x => x.type));
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

    handleStateUpdate(state) {
      this.formState = state;
      // Clear out form state after every change to prevent setting/changing
      // the value of any inputs (not all inputs support readonly).
      // Vue doesn't support real controlled inputs, so we need to let the
      // inputs see the new value being passed in before reverting it.
      // Otherwise -- no change, no rerender with the value from the prop.
      this.$nextTick(() => {
        if (Object.keys(this.formState.values).length > 0) {
          this.formState = produce(this.formState, draft => {
            draft.values = {};
          });
        }
      });
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
        SchemaFormSectionAdmin,
        {
          key: section.key,
          fields: section.fields,
          spacing: this.spacing,
          fullWidth: this.fullWidth,
        },
        {
          rows: this.$slots.rows,
          row: this.$slots.row,
          field: this.$slots.field,
          actions: this.$slots.actions,
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
        state: this.formState,
        'onUpdate:state': this.handleStateUpdate,
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
