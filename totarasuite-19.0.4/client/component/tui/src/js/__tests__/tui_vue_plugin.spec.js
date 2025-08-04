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

import tuiVuePlugin from 'tui/tui_vue_plugin';

let createInst;

beforeEach(() => {
  const app = {
    config: { globalProperties: {} },
    directive: jest.fn(),
    component: jest.fn(),
    mixin(options) {
      Object.entries(options.computed).forEach(([key, value]) => {
        Object.defineProperty(this.config.globalProperties, key, {
          get: value,
        });
      });
    },
  };
  tuiVuePlugin.install(app);
  const gp = app.config.globalProperties;
  function Inst() {}
  Inst.prototype = gp;
  createInst = () => {
    return new Inst();
  };
});

describe('Vue#uid', () => {
  it('returns a consistent per-component string', () => {
    const inst1 = createInst();
    expect(inst1.uid).toBe(inst1.uid);
    const inst2 = createInst();
    expect(inst2.uid).toBe(inst2.uid);
    expect(inst1.uid).not.toBe(inst2.uid);
  });
});

test('Vue#$id() returns uid appended with a string', () => {
  const vm = createInst();
  expect(vm.$id()).toBe(vm.uid);
  expect(vm.$id('foo')).toBe(vm.uid + '-foo');
  expect(vm.$idRef('foo')).toBe('#' + vm.uid + '-foo');
});

test('Vue#$window points to window global if window defined', () => {
  const vm = createInst();
  expect(vm.$window).toBe(window);
});
