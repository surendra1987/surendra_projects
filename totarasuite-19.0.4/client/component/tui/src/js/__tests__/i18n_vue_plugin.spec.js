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

import i18nVuePlugin from 'tui/i18n_vue_plugin';
import { getString, hasString } from 'tui/i18n';

let gp;

beforeEach(() => {
  const app = { config: { globalProperties: {} } };
  i18nVuePlugin.install(app);
  gp = app.config.globalProperties;
});

describe('Vue#str()', () => {
  it('calls i18n getString', () => {
    gp.$str('a', 'b', 'c');
    expect(getString).toHaveBeenCalledWith('a', 'b', 'c');
    gp.$str('c', 'd');
    expect(getString).toHaveBeenCalledWith('c', 'd');
    gp.$str('e');
    expect(getString).toHaveBeenCalledWith('e');
  });
});

describe('Vue#hasStr()', () => {
  it('calls i18n hasString', () => {
    hasString.mockImplementation((key, comp) => key == 'a' && comp == 'b');
    gp.$hasStr('a', 'b');
    expect(hasString).toHaveBeenCalledWith('a', 'b');
    gp.$hasStr('c', 'd');
    expect(hasString).toHaveBeenCalledWith('c', 'd');
    gp.$hasStr('e');
    expect(hasString).toHaveBeenCalledWith('e');
  });
});

describe('Vue#tryStr()', () => {
  it('calls getString if string is loaded or returns null otherwise', () => {
    hasString.mockImplementation((key, comp) => key == 'a' && comp == 'b');
    gp.$tryStr('a', 'b');
    expect(hasString).toHaveBeenCalledWith('a', 'b');
    gp.$tryStr('c', 'd');
    expect(hasString).toHaveBeenCalledWith('c', 'd');
    gp.$tryStr('e');
    expect(hasString).toHaveBeenCalledWith('e');
  });
});
