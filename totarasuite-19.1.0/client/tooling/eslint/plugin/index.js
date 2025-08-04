/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
 *
 * Totara Enterprise Extensions is provided only to Totara
 * Learning Solutions LTD's customers and partners, pursuant to
 * the terms and conditions of a separate agreement with Totara
 * Learning Solutions LTD or its affiliate.
 *
 * If you do not have an agreement with Totara Learning Solutions
 * LTD, you may not access, use, modify, or distribute this software.
 * Please contact [licensing@totaralearning.com] for more information.
 *
 * @author Simon Chester <simon.chester@totaralearning.com>
 * @module tui
 */

module.exports = {
  rules: {
    'no-tui-internal': {
      create: context => ({
        MemberExpression(node) {
          if (
            node.object.type == 'Identifier' &&
            node.object.name == 'tui' &&
            node.property.type == 'Identifier' &&
            node.property.name[0] == '_'
          ) {
            context.report(
              node.property,
              'Do not access internal TUI properties, as they may change without warning.'
            );
          }
        },
      }),
    },

    'no-weka-standalone-usage-identifier': require('./no_weka_standalone_usage_identifier'),

    'no-direct-testing-library-import': require('./no_direct_testing_library_import'),

    'replaceable-string-references': require('./replaceable_string_references'),

    'replaceable-string-references-vue': require('./replaceable_string_references_vue'),

    'no-v-model-without-prop-name-on-component': require('./no_v-model_without_prop_name_on_component'),

    'no-emit-input-without-update': require('./no_emit_input_without_update'),

    'no-styleclass': {
      create(context) {
        return context.parserServices.defineTemplateBodyVisitor({
          "VAttribute[key.name.name='bind'][key.argument.name='styleclass']": node => {
            context.report({
              node,
              message: 'styleclass props are deprecated',
            });
          },
        });
      },
    },
  },
};
