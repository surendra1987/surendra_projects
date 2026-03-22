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

import { pull, url } from 'tui/util';
import { onMounted, onUnmounted, ref, watch } from 'vue';

const watchers = [];

let lastSetParams = null;

const useParamState = jest.fn(options => {
  const currentState = ref(options.fromParams({ parsed: {} }));

  watch(currentState, value => {
    lastSetParams = options.toParams(value);
  });

  function updateState(update) {
    currentState.value =
      typeof update === 'function' ? update(currentState.value) : update;
  }

  Object.defineProperties(currentState, {
    push: {
      value: update => {
        updateState(update);
      },
    },

    replace: {
      value: update => {
        updateState(update);
      },
    },

    urlFor: {
      value: x => url('http://localhost/', x),
    },
  });

  function readState(parsed) {
    currentState.value = options.fromParams({ parsed });
  }

  onMounted(() => watchers.push(readState));
  onUnmounted(() => pull(watchers, readState));

  return currentState;
});

export default useParamState;

export function navigateToParams(parsed) {
  watchers.forEach(x => x(parsed));
}

export function getLastSetParams() {
  return lastSetParams;
}

beforeEach(() => {
  lastSetParams = {};
  useParamState.mockClear();
});
