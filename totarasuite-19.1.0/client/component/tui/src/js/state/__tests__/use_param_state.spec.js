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
import useParamState from '../use_param_state';

function setUrl(url) {
  window.location.href = url;
  window.dispatchEvent(new PopStateEvent('popstate'));
}

describe('useParamState', () => {
  let mockOptions;
  let mockParams;

  beforeEach(() => {
    mockOptions = {
      fromParams: jest.fn(({ parsed }) => parsed),
      toParams: jest.fn(x => ({
        key1: x.key1,
        key2: x.key2,
        key3: x.key3,
      })),
    };

    mockParams = {
      key1: 'value1',
      key2: 'value2',
      key3: ['1'],
    };

    Object.defineProperty(window, 'location', {
      value: {
        href: 'https://example.com/?key1=value1&key2=value2&key3[]=1',
      },
      writable: true,
    });
    Object.defineProperty(window, 'history', {
      value: {
        pushState: jest.fn((a, b, url) => {
          window.location.href = url.toString();
        }),
        replaceState: jest.fn((a, b, url) => {
          window.location.href = url.toString();
        }),
      },
      writable: true,
    });
  });

  afterEach(() => {
    jest.clearAllMocks();
  });

  it('reads state from URL', () => {
    const [state] = withSetup(() => useParamState(mockOptions));

    expect(mockOptions.fromParams).toHaveBeenCalledWith({ parsed: mockParams });
    expect(state.value).toEqual(mockParams);
  });

  it('handles popstate', () => {
    const [state] = withSetup(() => useParamState(mockOptions));

    setUrl('https://example.com/?key1=2');

    expect(mockOptions.fromParams).toHaveBeenCalledWith({
      parsed: { key1: '2' },
    });
    expect(state.value).toEqual({ key1: '2' });
  });

  it.each([
    [
      'direct set',
      state => {
        state.value.key2 = 'hello';
      },
      'https://example.com/?key1=value1&key2=hello&key3[0]=1',
    ],
    [
      'replacement',
      state => {
        state.value = { key2: 'hello' };
      },
      'https://example.com/?key2=hello',
    ],
    [
      'replace',
      state => {
        state.replace({ key2: 'hello' });
      },
      'https://example.com/?key2=hello',
    ],
    [
      'replace fn',
      state => {
        state.replace(old => ({ key1: old.key1, key2: 'hello' }));
      },
      'https://example.com/?key1=value1&key2=hello',
    ],
  ])(
    'replaces URL when state is modified (%s)',
    async (name, updater, newUrl) => {
      const [state] = withSetup(() => useParamState(mockOptions));

      updater(state);

      await flushMicrotasks();

      expect(window.history.replaceState).toHaveBeenCalledWith(
        null,
        null,
        expect.any(URL)
      );
      expect(
        window.history.replaceState.mock.calls.at(-1)[2].toString()
      ).toEqual(newUrl);
    }
  );

  it.each([
    [
      'push',
      state => {
        state.push({ key2: 'hello' });
      },
      'https://example.com/?key2=hello',
    ],
    [
      'push fn',
      state => {
        state.push(old => ({ key1: old.key1, key2: 'hello' }));
      },
      'https://example.com/?key1=value1&key2=hello',
    ],
  ])(
    'pushes history entry when .push() is called (%s)',
    async (name, updater, newUrl) => {
      const [state] = withSetup(() => useParamState(mockOptions));

      updater(state);

      await flushMicrotasks();

      expect(window.history.pushState).toHaveBeenCalledWith(
        null,
        null,
        expect.any(URL)
      );
      expect(window.history.pushState.mock.calls.at(-1)[2].toString()).toEqual(
        newUrl
      );
    }
  );

  it('transforms state via toParams and fromParams', async () => {
    setUrl('https://example.com/');

    const fromParams = jest.fn(({ parsed }) => ({
      page: parsed.page ? Number(parsed.page) - 1 : 0,
    }));
    const toParams = jest.fn(state => ({
      page: state.page === 0 ? null : state.page + 1,
    }));
    const [state] = withSetup(() =>
      useParamState({
        fromParams,
        toParams,
      })
    );

    expect(state.value).toEqual({ page: 0 });

    state.value.page = 10;
    await flushMicrotasks();
    expect(window.location.href).toBe('https://example.com/?page=11');

    setUrl('https://example.com/?page=5');
    expect(state.value.page).toBe(4);

    setUrl('https://example.com/');
    expect(state.value.page).toBe(0);
  });

  it('preserves unknown params', async () => {
    window.location.href += '&foo=bar';
    const [state] = withSetup(() => useParamState(mockOptions));

    state.value.key1 = 'test';
    await flushMicrotasks();

    const sp = new URL(window.location.href).searchParams;
    expect(sp.get('key1')).toBe('test');
    expect(sp.get('foo')).toBe('bar');
  });

  it('supports exclusive param control', async () => {
    window.location.href += '&foo=bar';
    const [state] = withSetup(() =>
      useParamState({
        ...mockOptions,
        exclusive: true,
      })
    );

    state.value.key1 = 'test';
    await flushMicrotasks();

    const sp = new URL(window.location.href).searchParams;
    expect(sp.get('key1')).toBe('test');
    expect(sp.get('foo')).toBe(null);
  });

  it('attaches and detatches popstate event listener', () => {
    Object.defineProperty(window, 'addEventListener', { value: jest.fn() });
    Object.defineProperty(window, 'removeEventListener', { value: jest.fn() });

    const [, app] = withSetup(() => useParamState(mockOptions));

    expect(window.addEventListener).toHaveBeenCalledOnceWith(
      'popstate',
      expect.toBeFunction()
    );
    expect(window.removeEventListener).not.toHaveBeenCalled();

    app.unmount();

    expect(window.removeEventListener).toHaveBeenCalledOnceWith(
      'popstate',
      expect.toBeFunction()
    );
  });

  it('lets you get the URL for a state change', async () => {
    const [state] = withSetup(() => useParamState(mockOptions));

    expect(state.urlFor({ ...state.value, key1: 'kumara' })).toBe(
      'https://example.com/?key1=kumara&key2=value2&key3[0]=1'
    );

    await flushMicrotasks();

    // actual location should not have changed
    expect(window.location.href).toBe(
      'https://example.com/?key1=value1&key2=value2&key3[]=1'
    );
  });

  it('supports push then direct set', async () => {
    // regression test for TL-43181:
    // race condition could cause modifications to .value to not affect the URL
    // if .push() or .replace() was called immediately before
    const [state] = withSetup(() => useParamState(mockOptions));

    state.push({ key1: 'a' });
    state.value.key2 = 'b';
    await flushMicrotasks();

    const sp = new URL(window.location.href).searchParams;
    expect(sp.get('key1')).toBe('a');
    expect(sp.get('key2')).toBe('b');
  });
});
