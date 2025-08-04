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
 * @module totara_catalog
 */

import { ref } from 'vue';

export let useFilteredResultsValues = null;

export function resetUseFilteredResultsValues() {
  useFilteredResultsValues = {
    filterDefs: ref([]),
    browseFilterDef: ref(null),
    filterValue: ref({}),
    showingResults: ref(true),
    sortDef: ref(null),
    sortValue: ref(null),
    results: ref({}),
    firstNewResultIndex: ref(null),
    loadingResults: ref(false),
    canLoadMore: ref(true),
    loadMore: jest.fn(),
    loadingMore: ref(false),
    showEmptyState: ref(false),
  };
}

resetUseFilteredResultsValues();

export default function useFilteredResults() {
  return useFilteredResultsValues;
}
