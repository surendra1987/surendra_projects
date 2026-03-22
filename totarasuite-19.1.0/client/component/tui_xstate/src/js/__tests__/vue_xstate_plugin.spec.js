/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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

import vueXstatePlugin, { mixin } from '../vue_xstate_plugin';
import createDebounceMachine, {
  DEBOUNCE_MACHINE_ID,
} from './fixtures/debounce_test_machine';
import createParentMachine from './fixtures/parent_test_machine';
import { StateNode } from 'xstate';
import { flushMicrotasks } from 'tui_test_utils';
import { createApp, h, ref } from 'vue';

describe('vueXStatePlugin#xState', () => {
  it('throws an error without proper config', () => {
    const badConfig = { xState: {} };

    const app = createApp(badConfig);
    app.use(vueXstatePlugin);

    expect(() => {
      app.mount(document.createElement('div'));
    }).toThrow(`xState config needs a "machine()" or a "machineId" option`);
  });

  it('throws an error when given a non-existent machine id', () => {
    const badConfig = { xState: { machineId: 'does-not-exist' } };

    const app = createApp(badConfig);
    app.use(vueXstatePlugin);

    expect(() => {
      app.mount(document.createElement('div'));
    }).toThrow(`No machine with id: "does-not-exist" registered`);
  });

  it('does not throw an error when given a real machine creating function', () => {
    const config = {
      xState: {
        machine: () => createDebounceMachine(),
      },
      render: () => h('div'),
    };
    const dataSpy = jest.spyOn(mixin, 'data');
    const machineSpy = jest.spyOn(config.xState, 'machine');

    const app = createApp(config);
    app.use(vueXstatePlugin);
    app.mount(document.createElement('div'));

    expect(dataSpy).toHaveBeenCalledTimes(1);
    expect(dataSpy.mock.results[0]).toHaveProperty('type', 'return');
    expect(machineSpy).toHaveBeenCalled();
    expect(machineSpy.mock.results[0].value).toBeInstanceOf(StateNode);

    dataSpy.mockRestore();
    machineSpy.mockRestore();
  });

  it('attaches a registered machine to a child component with "machineId"', () => {
    const childRef = ref();
    const parentRef = ref();

    const Child = {
      data() {
        return {};
      },
      xState: { machineId: DEBOUNCE_MACHINE_ID },
      render: () => h('div'),
    };

    const Parent = {
      components: {
        Child,
      },
      data() {
        return { test: true };
      },
      xState: {
        machine: () => createDebounceMachine(),
      },
      render: () => h('div'),
    };

    const app = createApp({
      render: () => [
        h(Child, { ref: childRef }),
        h(Parent, { ref: parentRef }),
      ],
    });
    app.use(vueXstatePlugin);
    app.mount(document.createElement('div'));

    expect(parentRef.value.x).toBeObject();
    expect(childRef.value.x).toBeObject();
  });

  it('registers a child machine instantiated inside a parent machine', () => {
    const childRef = ref();
    const parentRef = ref();
    const parentMachineId = 'parent';
    const Child = {
      data() {
        return {};
      },
      xState: { machineId: DEBOUNCE_MACHINE_ID },
      render: () => h('div'),
    };

    const Parent = {
      data() {
        return { test: true };
      },
      xState: {
        machine() {
          return createParentMachine({
            id: parentMachineId,
          });
        },
      },
      render: () => h('div'),
    };

    const app = createApp({
      render: () => [
        h(Child, { ref: childRef }),
        h(Parent, { ref: parentRef }),
      ],
    });
    app.use(vueXstatePlugin);
    app.mount(document.createElement('div'));

    expect(parentRef.value.x).toBeObject();
    expect(childRef.value.x).toBeObject();
  });

  test('escalates and handles an explicity handled Error from a child machine', async () => {
    const Component = {
      data() {
        return { test: true };
      },
      xState: {
        machine() {
          return createParentMachine({
            id: 'parent-with-child-error',
            context: { willError: true },
          });
        },
      },
      render: () => h('div'),
    };

    const app = createApp(Component);
    app.use(vueXstatePlugin);
    const inst = app.mount(document.createElement('div'));

    await flushMicrotasks();

    expect(inst.x.context.hasErrored).toBeTrue();
  });
});
