/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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

/**
 * @param {CallExpression} node
 */
function getEmitCallEvent(node) {
  if (
    node.callee.type === 'MemberExpression' &&
    node.callee.property.name === '$emit'
  ) {
    const arg = node.arguments[0];
    if (arg?.type === 'Literal') {
      return arg.value;
    }
  }
  return null;
}

/** @type {import('eslint').Rule.RuleModule} */
module.exports = {
  create: context => {
    return {
      CallExpression(node) {
        // console.log(node);
        if (getEmitCallEvent(node) === 'input') {
          /** @type {import('estree').ExpressionStatement} */
          const inputES = node.parent;
          const siblings = inputES.parent.body;
          if (!Array.isArray(siblings)) {
            return;
          }
          const earlierNodes = siblings.slice(
            0,
            siblings.indexOf(inputES.parent)
          );
          const hasUpdateEmit = earlierNodes.some(
            node =>
              (node.type === 'ExpressionStatement' &&
              node.expression.type === 'CallExpression' &&
              getEmitCallEvent(node.expression)?.startsWith('update:'))
          );
          if (!hasUpdateEmit) {
            context.report({
              node,
              message:
                `v-model no longer listens to the 'input' event on components. ` +
                `Make sure you emit an 'update:value' or similar event before emitting the 'input' event.`,
            });
          }
        }
      },
    };
  },
};
