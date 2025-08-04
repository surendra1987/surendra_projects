/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Chester <simon.chester@totara.com>
 */

/* eslint-env node */
/* eslint-disable */

const stylelint = require('stylelint');
const ruleName = 'totara/no-deprecated-vars';

const orFormat = new Intl.ListFormat('en', { type: 'disjunction' });

const deprecatedVars = {
  '--font-body-large-size': {
    replacement: [
      '--font-body-lg-size',
      '@include font(body-xl)',
      '@include font(body-lg)',
    ],
  },
  '--font-body-small-size': {
    replacement: ['--font-body-sm-size', '@include font(body-sm)'],
  },
  '--font-body-x-small-size': {
    replacement: ['--font-body-sm-size', '@include font(body-sm)'],
  },
  '--font-body-xx-small-size': {
    replacement: ['--font-body-xs-size', '@include font(body-xs)'],
  },
  '--font-heading-large-size': {
    replacement: ['--font-display-lg-size', '@include font(display-lg)'],
  },
  '--font-heading-size': {
    replacement: [
      '<h1>',
      '--font-h1-size',
      '@include font(h1)',
      '--font-display-sm-size',
      '@include font(display-sm)',
    ],
  },
  '--font-heading-small-size': {
    replacement: [
      '<h3>',
      '--font-h3-size',
      '@include font(h3)',
      '--font-body-xl-size',
      '@include font(body-xl)',
    ],
  },
  '--font-heading-x-small-size': {
    replacement: [
      '<h4>',
      '--font-h4-size',
      '@include font(h4)',
      '--font-body-lg-size',
      '@include font(body-lg)',
    ],
  },
  '--font-body-large-line-height': {
    replacement: ['--font-body-lg-line-height'],
  },
  '--font-body-small-line-height': {
    replacement: ['--font-body-sm-line-height'],
  },
  '--font-body-x-small-line-height': {
    replacement: ['--font-body-sm-line-height'],
  },
  '--font-body-xx-small-line-height': {
    replacement: ['--font-body-xs-line-height'],
  },
  '--font-heading-large-line-height': {
    replacement: ['--font-display-lg-line-height'],
  },
  '--font-heading-line-height': {
    replacement: ['--font-h1-line-height', '--font-display-sm-line-height'],
  },
  '--font-heading-small-line-height': {
    replacement: ['--font-h3-line-height', '--font-body-xl-line-height'],
  },
  '--font-heading-x-small-line-height': {
    replacement: ['--font-h4-line-height', '--font-body-lg-line-height'],
  },
  '--font-size-1': { replacement: ['font-size-px(1)'] },
  '--font-size-2': { replacement: ['font-size-px(2)'] },
  '--font-size-4': { replacement: ['font-size-px(4)'] },
  '--font-size-8': { replacement: ['font-size-px(8)'] },
  '--font-size-10': { replacement: ['font-size-px(10)'] },
  '--font-size-11': { replacement: ['font-size-px(11)'] },
  '--font-size-12': { replacement: ['font-size-px(12)'] },
  '--font-size-13': { replacement: ['font-size-px(13)'] },
  '--font-size-14': { replacement: ['font-size-px(14)'] },
  '--font-size-15': { replacement: ['font-size-px(15)'] },
  '--font-size-16': { replacement: ['font-size-px(16)'] },
  '--font-size-18': { replacement: ['font-size-px(18)'] },
  '--font-size-20': { replacement: ['font-size-px(20)'] },
  '--font-size-22': { replacement: ['font-size-px(22)'] },
  '--font-size-24': { replacement: ['font-size-px(24)'] },
  '--font-size-30': { replacement: ['font-size-px(30)'] },
  '--font-size-32': { replacement: ['font-size-px(32)'] },
  '--font-size-40': { replacement: ['font-size-px(40)'] },
  '--font-size-48': { replacement: ['font-size-px(48)'] },
  '--font-size-50': { replacement: ['font-size-px(50)'] },
  '--font-size-heading-level-1': { replacement: ['--font-h1-size'] },
  '--font-size-heading-level-2': { replacement: ['--font-h2-size'] },
  '--font-size-heading-level-3': { replacement: ['--font-h3-size'] },
  '--font-size-heading-level-4': { replacement: ['--font-h4-size'] },
  '--font-size-heading-level-5': { replacement: ['--font-h5-size'] },
  '--font-size-heading-level-6': { replacement: ['--font-h6-size'] },
};

const deprecatedMixins = {
  'tui-font-body': { replacement: ['@include font(body)'] },
  'tui-font-body-small': { replacement: ['@include font(body-sm)'] },
  'tui-font-body-x-small': { replacement: ['@include font(body-sm)'] },
  'tui-font-body-xx-small': { replacement: ['@include font(body-xs)'] },
  'tui-font-body-disabled': {
    replacement: ['color: var(--color-text-disabled)'],
  },
  'tui-font-body-placeholder': {
    replacement: ['color: var(--color-text-hint)'],
  },
  'tui-font-heavy': { replacement: ['font-weight: bold'] },
  'tui-font-monospace': {
    replacement: ['font-family: var(--font-family-monospace)'],
  },
  'tui-font-link': { element: 'a' },
  'tui-font-link-small': { element: 'a', withFont: 'body-sm' },
  'tui-font-link-large': { element: 'a', withFont: 'body-lg' },
  'tui-font-heading-medium': { replacement: ['@include font(h1)'] },
  'tui-font-heading-small': { replacement: ['@include font(h3)'] },
  'tui-font-heading-small-regular': { replacement: ['@include font(h3)'] },
  'tui-font-heading-x-small': { replacement: ['@include font(h4)'] },
  'tui-font-heading-label': {
    replacement: [
      '@include font(h4)',
      '@include font(h5)',
      'font-weight: var(--label-weight)',
    ],
  },
  'tui-font-heading-label-small': {
    replacement: [
      '@include font(h6)',
      '@include font(body-sm, var(--label-weight))',
    ],
  },
  'tui-font-heading-page-title': { replacement: ['@include font(h1)'] },
  'tui-font-heading-page-title-small': { replacement: ['@include font(h2)'] },
  'tui-font-hint': { replacement: ['@include text-hint()'] },
};

function formatMixinReplacement(details) {
  let str = '';
  if (details.replacement) {
    str += `Use ${orFormat.format(details.replacement)} instead. `;
  }

  if (details.element) {
    const article = /^[aeiou]/.test(details.replacement) ? 'an' : 'a';
    str += `Use ${article} <${details.element}> tag`;
    if (details.withFont) {
      str += ` with class "${details.withFont}" or mixin @include font(${details.withFont})`;
    }
    str += ' instead. ';
  }

  return str.trim();
}

const messages = stylelint.utils.ruleMessages(ruleName, {
  deprecatedMixin(name, details) {
    return `Mixin ${name} is deprecated. ${formatMixinReplacement(details)}`;
  },
  deprecatedVar(name, details) {
    return (
      `CSS variable ${name} is deprecated. ` +
      `Use ${orFormat.format(details.replacement)} instead.`
    );
  },
});
const plugin = stylelint.createPlugin(ruleName, on => {
  return (root, result) => {
    if (!on) {
      return;
    }

    // check var usages
    root.walkDecls(decl => {
      const matches = [...decl.value.matchAll(/\bvar\(\s*(--font-[^)\s,]+)/gi)];
      for (const match of matches) {
        const depInfo = deprecatedVars[match[1]];
        if (depInfo) {
          stylelint.utils.report({
            ruleName,
            result,
            node: decl,
            message: messages.deprecatedVar(match[1], depInfo),
          });
        }
      }
    });

    // check mixin usages
    root.walkAtRules('include', rule => {
      if (deprecatedMixins[rule.params]) {
        const mixin = deprecatedMixins[rule.params];
        stylelint.utils.report({
          ruleName,
          result,
          node: rule,
          message: messages.deprecatedMixin(rule.params, mixin),
        });
      }
    });
  };
});

plugin.ruleName = ruleName;
plugin.messages = messages;

module.exports = plugin;
