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
 * @module samples
 */
import { computed } from 'vue';
import { orderBy, groupBy } from 'tui/util';

export default function useFilteredResults({ samples, state, filter }) {
  const results = computed(() => {
    const { tuiComponent } = state.value;
    const normalisedFilter = filter.value.toLowerCase();
    return samples
      .filter(x => !tuiComponent || x.tuiComponent == tuiComponent)
      .map(x => ({
        sample: x,
        score: score(x.text.toLowerCase(), normalisedFilter),
      }))
      .filter(x => x.score != 0)
      .sort((a, b) => b.score - a.score)
      .map(x => x.sample);
  });

  const resultGroups = computed(() => {
    let groups = groupBy(results.value, x => x.tuiComponent);
    groups = Object.entries(groups).map(([name, results]) => ({
      name,
      results,
    }));
    return orderBy(groups, x =>
      x.name.startsWith('tui') ? '000' + x.name : x.name
    );
  });

  return { results, resultGroups };
}

const underscores = str => {
  str = str.replace(/(.)([A-Z][a-z]+)/g, '$1_$2');
  str = str.replace(/([a-z0-9])([A-Z])/g, '$1_$2');
  return str.toLowerCase();
};

/**
 * Generate a score for ranking a filter result
 *
 * @param {string} text Result text
 * @param {string} filter Filter text
 * @returns {Number} Score, higher is better
 */
function score(text, filter) {
  const index = text.indexOf(filter);

  if (index === -1) {
    return 0;
  }

  let substringScore = (text.length - index) / text.length;

  let subwordScore = 0;
  const words = underscores(text).split('_');
  for (var i = 0; i < words.length; i++) {
    if (words[i].startsWith(filter)) {
      subwordScore = (words.length - i) / words.length;
      break;
    }
  }

  return subwordScore * 10 + substringScore;
}
