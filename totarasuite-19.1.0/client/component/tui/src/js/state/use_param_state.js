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

import { formatParams, memoize, parseParams } from 'tui/util';
import { onMounted, onUnmounted, ref, watch } from 'vue';

/**
 * @typedef {{
 *   exclusive?: boolean,
 *   fromParams: ({ parsed: Record<string, any> }) => T;
 *   toParams: (state: T) => Record<string, string>;
 * }} UseParamStateOptions
 * @template T
 */

/**
 * @template T
 * @param {UseParamStateOptions<T>} options
 * @returns {import('vue').Ref<T> & { push: (patch: Partial<T>) => void, replace: (patch: Partial<T>) => void }}
 */
export default function useParamState(options) {
  let skipNextUrlSet = false;
  const currentState = ref(getUrlParams(options));

  watch(
    currentState,
    state => {
      if (skipNextUrlSet) {
        skipNextUrlSet = false;
        return;
      }

      setUrlParams(state, options);
    },
    {
      deep: true,
      // Force watcher to immediately be called on state mutation, instead of
      // being batched in the next microtask.
      // This is neccesary to avoid a race condition with skipNextUrlSet when
      // .push() or .replace() is called followed by immediately setting .value
      flush: 'sync',
    }
  );

  function readState() {
    skipNextUrlSet = true;
    currentState.value = getUrlParams(options);
  }

  onMounted(() => {
    window.addEventListener('popstate', readState);
  });

  onUnmounted(() => {
    window.removeEventListener('popstate', readState);
  });

  function updateState(update, replace) {
    update = typeof update === 'function' ? update(currentState.value) : update;
    skipNextUrlSet = true;
    currentState.value = update;
    setUrlParams(currentState.value, options, replace);
  }

  Object.defineProperties(currentState, {
    push: {
      value: update => {
        updateState(update, false);
      },
    },

    replace: {
      value: update => {
        updateState(update, true);
      },
    },

    urlFor: {
      value: x => getUrl(x, options).toString(),
    },
  });

  return currentState;
}

/**
 * @template T
 * @param {UseParamStateOptions<T>} options
 * @returns {T}
 */
function getUrlParams(options) {
  const url = new URL(window.location.href);
  return options.fromParams(createFromParamsOptions(url));
}

/**
 * Create lazy params object
 *
 * @param {URL} url
 * @returns {{ parsed: Record<string, any> }}
 */
function createFromParamsOptions(url) {
  return Object.defineProperties(
    {},
    {
      parsed: {
        get: memoize(() => parseParams(url.searchParams)),
        enumerable: true,
      },
    }
  );
}

/**
 * @template T
 * @param {T} value
 * @param {UseParamStateOptions<T>} options
 */
function getUrl(value, options) {
  const url = new URL(window.location.href);

  const params = options.toParams(value);

  const newParams = options.exclusive
    ? params
    : { ...parseParams(url.searchParams), ...params };

  url.search = formatParams(newParams);

  return url;
}

/**
 * @template T
 * @param {T} value
 * @param {UseParamStateOptions<T>} options
 * @param {boolean?} replace
 */
function setUrlParams(value, options, replace = true) {
  const url = getUrl(value, options);
  if (replace) {
    history.replaceState(null, null, url);
  } else {
    history.pushState(null, null, url);
  }
}
