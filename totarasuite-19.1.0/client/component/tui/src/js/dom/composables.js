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

import { onMounted, onUnmounted, watch } from 'vue';
import { Size } from 'tui/geometry';

/**
 * Composable that observes the width and height of an element.
 *
 * @param {import("vue").Ref<HTMLElement>} elRef
 * @param {(size: Size, oldSize: Size|null)} callback
 */
export function useResizeObserver(elRef, callback) {
  /** @type {ResizeObserver} */
  let resizeObserver;
  /** @type {Size} */
  let oldSize = null;
  onMounted(() => {
    resizeObserver = new ResizeObserver(([entry]) => {
      const domSize = entry.borderBoxSize[0];
      const size = new Size(domSize.inlineSize, domSize.blockSize);
      callback(size, oldSize);
      oldSize = size;
    });
    if (elRef.value) {
      resizeObserver.observe(elRef.value);
    }
  });

  onUnmounted(() => {
    resizeObserver.disconnect();
  });

  watch(elRef, value => {
    resizeObserver.disconnect();
    if (value) {
      resizeObserver.observe(value);
    }
  });
}
