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

import { flushMicrotasks } from 'tui_test_utils';
import { formatParams } from 'tui/util';
import {
  notify,
  notifyParams,
  showPendingNotifications,
  testInternals,
} from '../notifications';

let container = null;

jest.mock('../tui', () => {
  return {
    mount(comp, props) {
      const c = {
        addNotification: jest.fn(),
      };
      container = c;
      props.ref.value = c;
    },
  };
});

jest.mock('../storage');

beforeEach(() => {
  testInternals.storage().clear();
  if (container) {
    container.addNotification.mockReset();
  }
});

describe('notify', () => {
  it('calls addNotification on container', async () => {
    await notify({ message: 'hi' });
    expect(container.addNotification).toHaveBeenCalledOnceWith({
      duration: 5000,
      message: 'hi',
      type: 'success',
    });
  });
});

describe('notifyParams', () => {
  it('provides one time use code and notification data as URL params', () => {
    const params = notifyParams({ message: 'foobar' });
    expect(Object.keys(params)).toEqual(['nf', 'nfd']);
    const token = testInternals.storage().get('token');
    expect(token).not.toBeNull();
    expect(params.nf).toBe(token);
    expect(JSON.parse(atob(params.nfd))).toEqual({ message: 'foobar' });
  });
});

describe('showPendingNotifications', () => {
  it('shows notifications from URL', async () => {
    Object.defineProperty(window, 'location', {
      configurable: true,
      value: new URL(
        'http://localhost/?' + formatParams(notifyParams({ message: 'hello' }))
      ),
    });
    showPendingNotifications();
    await flushMicrotasks();
    expect(container.addNotification).toHaveBeenCalledOnceWith({
      duration: 5000,
      message: 'hello',
      type: 'success',
    });
  });

  it("does not show anything if the URL params aren't present", async () => {
    Object.defineProperty(window, 'location', {
      configurable: true,
      value: new URL('http://localhost/'),
    });
    showPendingNotifications();
    await flushMicrotasks();
    expect(container.addNotification).not.toHaveBeenCalled();
  });

  it('does not show anything if the URL params are incorrect', async () => {
    Object.defineProperty(window, 'location', {
      configurable: true,
      value: new URL(
        'http://localhost/?' +
          formatParams({ ...notifyParams({ message: 'hello' }), nf: '0' })
      ),
    });
    showPendingNotifications();
    await flushMicrotasks();
    expect(container.addNotification).not.toHaveBeenCalled();
  });
});
