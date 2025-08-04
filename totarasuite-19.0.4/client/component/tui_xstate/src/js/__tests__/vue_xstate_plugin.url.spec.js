/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author Simon Tegg <simon.tegg@totaralearning.com>
 * @module tui_xstate
 * @jest-environment jsdom
 */

import { createApp, h } from 'vue';
import vueXstatePlugin from '../vue_xstate_plugin';
import createDebounceMachine from './fixtures/debounce_test_machine';
import { flushMicrotasks } from 'tui_test_utils';

beforeEach(() => {
  window.history.replaceState({}, null, '/test.html?application_id=1');
});

describe('vueXStatePlugin#xState', () => {
  test('mapQueryParamsToContext adds data to context defined in the url query state', async () => {
    const activeIndex = 3;
    window.history.pushState(
      {},
      null,
      `/test.html?active_index=${activeIndex}`
    );
    const Component = {
      data() {
        return { test: true };
      },
      xState: {
        machine() {
          return createDebounceMachine();
        },

        mapQueryParamsToContext({ active_index }) {
          return { activeIndex: parseInt(active_index, 10) };
        },
      },
      render: () => h('div'),
    };

    const app = createApp(Component);
    app.use(vueXstatePlugin);
    const inst = app.mount(document.createElement('div'));

    await flushMicrotasks();

    expect(inst.x).toBeObject();
    expect(inst.x.context.activeIndex).toBe(activeIndex);
  });

  test('mapStateToQueryParams syncs the state to the query params', () => {
    const Component = {
      data() {
        return { test: true };
      },
      xState: {
        machine() {
          return createDebounceMachine();
        },

        mapStateToQueryParams(statePaths, prevStatePaths) {
          if (
            statePaths.includes('debouncing') &&
            prevStatePaths.includes('idle')
          ) {
            return { debouncing: true };
          } else {
            return { debouncing: undefined };
          }
        },
      },
      render: () => h('div'),
    };

    const app = createApp(Component);
    app.use(vueXstatePlugin);
    const inst = app.mount(document.createElement('div'));

    inst.x.service.send('GO');

    expect(inst.x.state.value).toBe('debouncing');
    expect(window.location.search).toBe('?application_id=1&debouncing=true');

    inst.x.service.send('CANCEL');

    expect(inst.x.state.value).toBe('idle');
    expect(window.location.search).toBe('?application_id=1');
  });
});
