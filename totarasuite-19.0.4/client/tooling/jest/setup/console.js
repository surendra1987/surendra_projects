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

const util = require('util');
const chalk = require('chalk');

/**
 * Array of [message, stack] tuples.
 *
 * @type {Array<[string, string]>}
 */
let messages = [];

const upperFirst = str => str.slice(0, 1).toUpperCase() + str.slice(1);

['log', 'warn', 'error'].forEach(method => {
  const original = global.console[method];
  global.console['debug' + upperFirst(method)] = original;
  global.console[method] = (...args) => {
    const message = util.format(...args);
    const stack = new Error().stack;
    messages.push([message, stack.substring(stack.indexOf('\n') + 1)]);
  };
});

function flushMessages() {
  if (messages.length > 0) {
    const formatted = messages.map(([message, stack]) => {
      const single = messages.length === 1;
      stack = stack.substring(stack.indexOf('\n') + 1);
      return (
        message +
        '\n' +
        // avoid Jest rendering the trace weirdly if there's more than one message
        (single ? stack : chalk.gray(stack))
      );
    });

    const message =
      `Expected test not to print to console.\n\n` +
      `If the call is expected, mock it out with jest.spyOn(console, 'log').mockImplementation().`;

    throw new Error(`${message}\n\n${formatted.join('\n\n')}`);
  }
}

beforeEach(() => {
  if (messages.length > 0) {
    messages = [];
  }
});

afterEach(() => {
  flushMessages();
});
