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
 * @module tui_test_utils
 */

import { createApp } from 'vue';

/**
 * Return a thenable resolving when all microtasks have been flushed.
 *
 * @returns {PromiseLike}
 */
export function flushMicrotasks() {
  return {
    then(resolve) {
      jest.requireActual('timers').setImmediate(resolve);
    },
  };
}

/**
 * Convert a vue-test-utils WrapperArray to a plain array
 *
 * @param {WrapperArray} wrapperArray
 * @return {Array}
 */
export function plainWrapperArray(wrapperArray) {
  const arr = [];
  for (var i = 0; i < wrapperArray.length; i++) {
    arr.push(wrapperArray.at(i));
  }
  return arr;
}

/**
 * Wrap composable in a component for testing.
 * 
 * https://vuejs.org/guide/scaling-up/testing#testing-composables
 *
 * @template T
 * @param {() => T} composable
 * @returns {[T, import('vue').App]}
 */
export function withSetup(composable) {
  let result;
  const app = createApp({
    setup() {
      result = composable();
      return () => {};
    },
  });
  app.mount(document.createElement('div'));
  return [result, app];
}
