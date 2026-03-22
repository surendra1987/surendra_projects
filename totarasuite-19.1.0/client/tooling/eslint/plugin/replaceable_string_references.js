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

const {
  checkCallSafe,
  stringCalls,
  vueStringCalls,
} = require('./replaceable_string_helpers');
const { findVariable } = require('eslint-utils');

/** @type {import('eslint').Rule.RuleModule} */
module.exports = {
  create: context => {
    return {
      CallExpression(node) {
        if ((node.callee.type === 'Identifier')) {
          const scope = context.getScope(node);
          const ref = findVariable(scope, node.callee.name);
          if (ref && ref.defs[0]) {
            const def = ref.defs[0];
            if (def.node.type === 'ImportSpecifier') {
              /** @type {import('estree').ImportSpecifier & import('eslint').Rule.NodeParentExtension} */
              const defNode = def.node;
              if (
                defNode.parent.type === 'ImportDeclaration' &&
                (defNode.parent.source.value === 'tui/i18n' ||
                  (defNode.parent.source.value.endsWith('./i18n') &&
                    /\/tui\/src\//.test(context.getFilename())))
              ) {
                if (stringCalls.includes(defNode.imported.name)) {
                  checkCallSafe(node, context, defNode.imported.name);
                }
              }
            }
          }
        } else if (
          (node.callee.type === 'MemberExpression' &&
          (node.callee.object.type === 'ThisExpression' ||
            (node.callee.object.type === 'Identifier' &&
              node.callee.object.name === '_vm')) &&
          node.callee.property.type === 'Identifier' &&
          vueStringCalls.includes(node.callee.property.name))
        ) {
          checkCallSafe(node, context, node.callee.property.name);
        }
      },
    };
  },
};
