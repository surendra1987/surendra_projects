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

import 'jest-canvas-mock';
import './matchers';
import { toHaveNoViolations } from 'jest-axe';
import '@testing-library/jest-dom';
import 'jest-extended/all';
import './setup/console';
import './setup/vue';

jest.mock('tui/apollo/client', () => null);
jest.mock('tui/util', () => ({
  ...jest.requireActual('tui/util'),
  debounce: x => x,
  throttle: x => x,
}));
jest.mock('tui/config');
jest.mock('tui/i18n');
jest.mock('tui/internal/lang_string_store');
jest.mock('tui/pending');
jest.mock('tui/storage');
jest.mock('tui/theme');
jest.mock('tui/tui');

expect.extend(toHaveNoViolations);

// temporarily disable aria-allowed-attr to match behavior of older jest-axe
jest.mock('jest-axe', () => {
  const actual = jest.requireActual('jest-axe');
  return {
    ...actual,
    axe: actual.configureAxe({
      rules: {
        'aria-allowed-attr': { enabled: false },
      },
    }),
  };
});

// Work around JSDOM throwing an error when jest-axe calls window.getComputedStyle(elt, pseudoElt)
// https://github.com/nickcolley/jest-axe/issues/147
const dummyStyle = global.getComputedStyle(document.createElement('div'));
const originalComputedStyle = global.getComputedStyle;
global.getComputedStyle = (elt, pseudoElt) =>
  pseudoElt ? dummyStyle : originalComputedStyle(elt);

global.ResizeObserver = class {
  observe() {}
  unobserve() {}
  disconnect() {}
};
