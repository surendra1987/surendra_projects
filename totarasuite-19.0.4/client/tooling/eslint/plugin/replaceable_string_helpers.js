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

/**
 * @param {import('estree').Node} node
 * @returns {boolean}
 */
const isStr = node => node.type === 'Literal' && typeof node.value === 'string';

/**
 * Check if call to str function is safely replaceable (first arg is string or ternary with strings, second arg is string)
 * @param {import('estree').CallExpression & import('eslint').Rule.NodeParentExtension} node
 * @param {import('eslint').Rule.RuleContext} context
 */
function checkCallSafe(node, context, name) {
  const [keyArg, compArg] = node.arguments;

  if (keyArg) {
    const valid =
      isStr(keyArg) ||
      (keyArg.type === 'ConditionalExpression' &&
        isStr(keyArg.consequent) &&
        isStr(keyArg.alternate));
    if (!valid) {
      context.report({
        node: keyArg,
        message: `The first parameter (key) to ${name} must be a string literal or ternary expression.`,
      });
    }
  }

  if (compArg && !isStr(compArg)) {
    context.report({
      node: compArg,
      message: `The second parameter (component) to ${name} must be a string literal.`,
    });
  }
}

// string calls to check -- we don't check langString because it explicitly supports dynamic strings
const stringCalls = ['getString', 'hasString'];

const vueStringCalls = ['$str', '$hasStr', '$tryStr'];

exports.checkCallSafe = checkCallSafe;
exports.stringCalls = stringCalls;
exports.vueStringCalls = vueStringCalls;
