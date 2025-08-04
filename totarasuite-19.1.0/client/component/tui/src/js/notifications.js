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
 * Please contact [licensing@totara.com] for more information.
 *
 * @author Simon Chester <simon.chester@totara.com>
 * @module tui
 */

import { ref } from 'vue';
import tui from './tui';
import Container from 'tui/components/notifications/ToastContainer';
import { memoizeLoad, lazy } from './util';
import { WebStorageStore } from 'tui/storage';

const storage = lazy(() => new WebStorageStore('notify', window.localStorage));

/**
 * Get the toast container to add notifications to.
 *
 * One will be created if it does not exist.
 *
 * @private
 * @function
 * @returns {Promise<Vue>}
 */
const getContainer = memoizeLoad(async () => {
  const wrap = document.createElement('div');
  document.body.appendChild(wrap);
  const compRef = ref(null);
  tui.mount(Container, { ref: compRef }, wrap);
  await new Promise(r => setTimeout(r, 0));
  return compRef.value;
});

/**
 * @typedef {Object} NotificationOptions
 * @property {string} message Message to display.
 * @property {number|null} duration Duration to display the message, or null to display until dismissed.
 * @property {'info' | 'success' | 'warning' | 'error'} type Importance type of the notification.
 */

/**
 * Show a notification toast.
 *
 * @param {NotificationOptions} options
 */
export async function notify(options) {
  options = Object.assign(
    {
      duration: 5000,
      message: '...',
      type: 'success',
    },
    options
  );

  const container = await getContainer();
  container.addNotification(options);
}

/**
 * Generate URL params to trigger a notification on page load.
 *
 * The exact name of the params is an implementation detail and may change in
 * the future, apply all returned params to the URL, e.g.:
 *
 * url('/foo', { id: 1, ...notifyParams({ message: 'Created' }) }).
 *
 * This must be called immediately before redirecting to a new URL. It's not
 * possible to pre-generate a URL and use it later, as subsequent calls will
 * invalidate previously generated URLs.
 *
 * @param {NotificationOptions} options
 * @returns {object} URL params
 */
export function notifyParams(options) {
  // We generate a random ID and store it in localStorage in order to prevent
  // forgery of notification params.
  const token = Math.random()
    .toString(36)
    .slice(2, 8)
    .padEnd(6, '0');
  storage().set('token', token);
  return {
    nf: token,
    nfd: btoa(JSON.stringify(options)),
  };
}

/**
 * Check any pending notifications and show them.
 *
 * @internal
 */
export function showPendingNotifications() {
  // optimisation: since this is called on every page load, check for nf param
  // before parsing the URL for real
  if (!window.location.search.includes('nf=')) {
    return;
  }
  const url = new URL(window.location);
  const token = url.searchParams.get('nf');
  const data = url.searchParams.get('nfd');
  if (!token || !data) {
    return;
  }
  if (storage().get('token') === token) {
    try {
      notify(JSON.parse(atob(data)));
    } catch (e) {
      /* istanbul ignore next */
      console.error(e);
    }
  }
  // clean up storage and URL params
  storage().delete('token');
  url.searchParams.delete('nf');
  url.searchParams.delete('nfd');
  history.replaceState(history.state, '', url);
}

// expose for unit test
/* istanbul ignore next */
export const testInternals =
  process.env.NODE_ENV == 'test' ? { storage } : null;
