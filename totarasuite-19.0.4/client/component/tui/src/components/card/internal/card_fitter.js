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

import { computed, onMounted, onUpdated, ref } from 'vue';
import { isRtl } from 'tui/i18n';

/**
 * @typedef {object} CardFitterOptions
 * @property {import('vue').Ref<HTMLElement>} measuringEl
 * @property {import('vue').Ref<Array>} items
 */

/**
 * @param {CardFitterOptions} options
 */
export function useCardFitter({ measuringEl, items }) {
  /** @type {import('vue').Ref<'initial'|'measuring'|'idle'>} */
  const state = ref('initial');
  /** Number of cards that fit on a single page */
  const fittingCards = ref(1);
  const measureCount = ref(1);
  const availableWidth = ref(100);

  // used as a starting point for card fitting, does not need to be accurate
  const widthEstimate = 500;

  /** Cards to render in measurement element, or null if the measurement element should not be shown */
  const measuringItems = computed(() => {
    switch (state.value) {
      case 'initial':
        return [];
      case 'measuring':
        return items.value[0]
          ? Array(measureCount.value).fill(items.value[0])
          : 0;
      default:
        return null;
    }
  });

  function getInitialMeasureCount() {
    return Math.ceil(availableWidth.value / widthEstimate);
  }

  /**
   * Remeasure -- e.g. in the case of container being resized
   */
  function remeasure() {
    state.value = 'initial';
  }

  function updated() {
    if (!measuringEl.value) return;
    switch (state.value) {
      case 'initial': {
        availableWidth.value = measuringEl.value.offsetWidth;
        measureCount.value = getInitialMeasureCount();
        state.value = 'measuring';
        break;
      }
      case 'measuring': {
        // handle if we didn't hit the full width of the measurement element
        if (measuringEl.value.scrollWidth <= measuringEl.value.offsetWidth) {
          if (measureCount.value < getInitialMeasureCount() * 4) {
            measureCount.value *= 2;
            break;
          }
        }

        let fitting = 0;
        for (const [i, child] of Array.from(
          measuringEl.value.children
        ).entries()) {
          if (isRtl() && child.offsetLeft < 0) {
            break;
          }
          if (
            !isRtl() &&
            child.offsetLeft + child.offsetWidth > availableWidth.value
          ) {
            break;
          }

          fitting = i + 1;
        }
        fittingCards.value = Math.max(fitting, 1);
        state.value = 'idle';
        break;
      }
    }
  }

  onMounted(updated);
  onUpdated(updated);

  return {
    fittingCards,
    measuringItems,
    remeasure,
  };
}
