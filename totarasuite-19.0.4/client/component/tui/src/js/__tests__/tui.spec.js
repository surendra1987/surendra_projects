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

/* eslint-disable tui/no-tui-internal */

import { h } from 'vue';
import tui from 'tui/tui';
import TotaraModuleStore from 'tui/internal/TotaraModuleStore';
import { memoize } from 'tui/util';

jest.unmock('tui/tui');
jest.mock('tui/internal/TotaraModuleStore');
jest.mock('tui/apollo/internal/tui_apollo_vue_plugin', () => ({
  default: {
    install() {},
  },
}));

const modules = TotaraModuleStore.mock.instances[0];

const mockComp = memoize(name => {
  if (name == 'invalid') {
    const error = new Error("Cannot find module '" + name + "'");
    error.code = 'MODULE_NOT_FOUND';
    throw error;
  }
  if (name == 'tui/components/errors/ErrorBoundary') {
    return {
      $_name: name,
      render() {
        return this.$slots.default();
      },
    };
  }
  return {
    $_name: name,
    render: h => h('div', name),
  };
});

modules.require.mockImplementation(name => ({
  default: mockComp(name),
  __esModule: true,
}));
modules.import.mockImplementation(async name => modules.require(name));
modules.default.mockImplementation(m => (m.__esModule ? m.default : m));

beforeEach(() => {
  modules.require.mockClear();
});

test('require proxies to modules.require', () => {
  tui.require('foo');
  expect(modules.require).toHaveBeenCalledWith('foo');
});

test('import proxies to modules.import', async () => {
  await tui.import('bar');
  expect(modules.import).toHaveBeenCalledWith('bar');
});

describe('mount', () => {
  async function mountHelper(comp, data) {
    const wrapper = document.createElement('div');
    const el = document.createElement('span');
    wrapper.append(el);
    await tui.mount(comp, data, el);
    return wrapper;
  }

  it('mounts the provided component', async () => {
    const wrapper = await mountHelper({ render: () => h('div', 'hello') });
    expect(wrapper.innerHTML).toInclude('<div>hello</div>');
  });

  it('wraps the provided component in an error boundary', () => {
    expect.assertions(1);
    mountHelper({
      created() {
        let vm = this.$parent;
        while (vm) {
          if (vm.$options.$_name == 'tui/components/errors/ErrorBoundary') {
            expect(vm.$options.$_name).toBe(
              'tui/components/errors/ErrorBoundary'
            );
          }
          vm = vm.$parent;
        }
      },
      render: () => h('div', 'hello'),
    });
  });
});

describe('scan', () => {
  const tuiMount = tui.mount;
  beforeEach(() => {
    tui.mount = jest.fn(() => {});
  });
  afterEach(() => {
    tui.mount = tuiMount;
  });

  it('calls tui.mount for every component matching [data-tui-component]', async () => {
    var el1 = document.createElement('div');
    var el2 = document.createElement('div');
    el2.setAttribute('data-tui-component', 'foo');
    el2.setAttribute('data-tui-props', '{"a":2}');
    el1.append(el2);
    var el3 = document.createElement('div');
    el3.setAttribute('data-tui-component', 'bar');
    el1.append(el3);
    // var el4 = document.createElement('div');
    // el4.setAttribute('data-tui-component', 'invalid');
    // el1.append(el4);
    var el5 = document.createElement('div');
    el5.setAttribute('data-tui-component', 'baz');

    await tui.scan(el1);

    expect(tui.mount).toHaveBeenCalledWith(
      mockComp('foo'),
      { a: 2 },
      expect.any(HTMLElement)
    );
    expect(tui.mount).toHaveBeenCalledWith(
      mockComp('bar'),
      null,
      expect.any(HTMLElement)
    );
  });

  it('finds elements in the document if no el is passed', async () => {
    var el = document.createElement('div');
    el.setAttribute('data-tui-component', 'foo');
    el.setAttribute('data-tui-props', '{"a":2}');
    document.body.append(el);

    await tui.scan();

    el.remove();

    expect(tui.mount).toHaveBeenCalledWith(
      mockComp('foo'),
      { a: 2 },
      expect.any(HTMLElement)
    );
  });
});
