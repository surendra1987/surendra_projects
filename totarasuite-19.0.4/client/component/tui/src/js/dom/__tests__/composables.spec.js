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

import { flushMicrotasks, withSetup } from 'tui_test_utils';
import { useResizeObserver } from '../composables';
import { ref } from 'vue';

describe('useResizeObserver', () => {
  beforeEach(() => {
    global.ResizeObserver = jest.fn(() => ({
      observe: jest.fn(),
      unobserve: jest.fn(),
      disconnect: jest.fn(),
    }));
  });

  it('reports changes to the size of an element', () => {
    const div = document.createElement('div');
    const elRef = ref(div);
    const callback = jest.fn();
    withSetup(() => useResizeObserver(elRef, callback));

    const ro = ResizeObserver.mock.results[0].value;
    expect(ro.observe).toHaveBeenCalledOnceWith(div);

    const roCallback = ResizeObserver.mock.calls[0][0];
    roCallback([
      {
        borderBoxSize: [{ inlineSize: 100, blockSize: 200 }],
      },
    ]);

    expect(callback).toHaveBeenCalledOnceWith(
      { width: 100, height: 200 },
      null
    );

    roCallback([
      {
        borderBoxSize: [{ inlineSize: 150, blockSize: 250 }],
      },
    ]);

    expect(callback).toHaveBeenCalledWith(
      { width: 150, height: 250 },
      { width: 100, height: 200 }
    );
  });

  it('handles element ref changing', async () => {
    const div1 = document.createElement('div');
    const div2 = document.createElement('div');
    const elRef = ref(null);
    const callback = jest.fn();
    withSetup(() => useResizeObserver(elRef, callback));

    const ro = ResizeObserver.mock.results[0].value;
    expect(ro.observe).not.toHaveBeenCalled();

    elRef.value = div1;
    await flushMicrotasks();
    expect(ro.observe).toHaveBeenCalledOnceWith(div1);

    ro.observe.mockClear();
    ro.disconnect.mockClear();

    elRef.value = div2;
    await flushMicrotasks();
    expect(ro.disconnect).toHaveBeenCalled();
    expect(ro.observe).toHaveBeenCalledOnceWith(div2);

    ro.observe.mockClear();
    ro.disconnect.mockClear();

    elRef.value = null;
    await flushMicrotasks();
    expect(ro.disconnect).toHaveBeenCalled();
    expect(ro.observe).not.toHaveBeenCalled();
  });
});
