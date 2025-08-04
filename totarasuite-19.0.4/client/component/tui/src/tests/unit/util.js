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

import {
  plainWrapperArray as plainWrapperArrayImpl,
  flushMicrotasks as flushMicrotasksImpl,
} from 'tui_test_utils';

/**
 * Convert a vue-test-utils WrapperArray to a plain array
 *
 * @deprecated since Totara 18.0 -- import from tui_test_utils instead
 * @param {WrapperArray} wrapperArray
 * @return {Array}
 */
export function plainWrapperArray(wrapperArray) {
  return plainWrapperArrayImpl(wrapperArray);
}

/**
 * Return a thenable resolving when all microtasks have been flushed.
 *
 * @deprecated since Totara 18.0 -- import from tui_test_utils instead
 * @returns {PromiseLike}
 */
export function flushMicrotasks() {
  return flushMicrotasksImpl();
}
