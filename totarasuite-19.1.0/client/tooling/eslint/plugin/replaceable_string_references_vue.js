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

const { checkCallSafe, vueStringCalls } = require('./replaceable_string_helpers');

module.exports = {
  create(context) {
    return context.parserServices.defineTemplateBodyVisitor({
      /** @param {import('estree').CallExpression & import('eslint').Rule.NodeParentExtension} node */
      'VExpressionContainer CallExpression'(node) {
        if (node.callee.type === 'Identifier' && vueStringCalls.includes(node.callee.name)) {
          checkCallSafe(node, context, node.callee.name);
        }
      },
    });
  },
};
