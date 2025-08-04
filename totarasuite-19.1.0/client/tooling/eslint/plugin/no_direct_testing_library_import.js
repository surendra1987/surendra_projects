/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * Totara Enterprise Extensions is provided only to Totara
 * Learning Solutions LTD's customers and partners, pursuant to
 * the terms and conditions of a separate agreement with Totara
 * Learning Solutions LTD or its affiliate.
 *
 * If you do not have an agreement with Totara Learning Solutions
 * LTD, you may not access, use, modify, or distribute this software.
 * Please contact [licensing@totara.com] for more information.
 *
 * @author Simon Chester <simon.chester@totara.com>
 * @module tui
 */

const prefix = '@testing-library/';

/** @type {import('eslint').Rule.RuleModule} */
module.exports = {
  create: context => ({
    ImportDeclaration(node) {
      if (
        node.source.type === 'Literal' &&
        node.source.value.startsWith(prefix)
      ) {
        const path = node.source.value;
        const subpath = path.substring(prefix.length);
        if (subpath === 'dom' || subpath === 'vue') {
          context.report({
            node: node.source,
            message:
              'testing-library functionality should be imported from tui_test_utils/vtl',
          });
        }
      }
    },
  }),
};
