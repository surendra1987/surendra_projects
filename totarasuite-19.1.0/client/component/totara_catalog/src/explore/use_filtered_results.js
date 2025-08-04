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

import { computed, ref, toRef, watch } from 'vue';
import { hasKeys } from 'tui/util';
import { produce } from 'tui/immutable';
import { NetworkStatus } from 'tui/apollo/client';
import { useQuery } from 'tui/apollo/composable';
import useParamState from 'tui/state/use_param_state';
import itemsQuery from 'totara_catalog/graphql/items';

export default function useFilteredResults(
  initialFilterConfig,
  alwaysShowResults
) {
  const state = useFilterUrlState({ initialFilterConfig });
  const showingResults = computed(() =>
    Boolean(
      alwaysShowResults.value ||
        state.value.search ||
        state.value.filters ||
        state.value.sort
    )
  );

  const itemsResult = useItemsResult({ state, showingResults });

  const filterConfig = useFilterConfig({
    initialFilterConfig,
    results: itemsResult.results,
    showingResults,
  });

  const { filterValue, sortValue } = useUiValues({ state, filterConfig });

  return {
    filterDefs: toRef(() => filterConfig.value.filters),
    browseFilterDef: toRef(() => filterConfig.value.browse_filter),
    filterValue,
    showingResults,
    sortDef: toRef(() => filterConfig.value.sort),
    sortValue,
    ...itemsResult,
  };
}

function findFilterDef(configResult, key) {
  return (
    configResult.filters.find(x => x.key == key) ??
    (configResult.browse_filter?.key == key ? configResult.browse_filter : null)
  );
}

/**
 * State -- contained in URL.
 *
 * @param {object} options
 * @param {object} options.initialFilterConfig Filter configuration with no filters active
 */
function useFilterUrlState({ initialFilterConfig }) {
  return useParamState({
    fromParams: ({ parsed }) => {
      const { catalog_fts, orderbykey, ...rest } = parsed;
      const state = {
        search: catalog_fts,
        sort: orderbykey,
        filters: {},
      };

      // read the rest of the params as filters, but only if they are defined
      // in the filter config
      for (let [key, value] of Object.entries(rest)) {
        const filterDef = findFilterDef(initialFilterConfig.value, key);
        if (filterDef) {
          // old format from old catalog
          if (filterDef.type != 'multi' && Array.isArray(value)) {
            value = value[0] ?? null;
          }

          state.filters[key] = Array.isArray(value)
            ? value.filter(x => x != null) // remove holes in array
            : value;
        }
      }

      // remove empty filters
      state.filters = minimiseFilterValue(state.filters);

      return state;
    },
    toParams: state => {
      const config = initialFilterConfig.value;
      const browseFilter = config.browse_filter;
      return {
        catalog_fts: state.search || null,
        orderbykey: state.sort,
        // ensure all filters are present in the object we pass back (as null if
        // not set), otherwise clearing filters does not work
        ...Object.fromEntries(
          config.filters.map(x => [x.key, state.filters?.[x.key] ?? null])
        ),
        // browse filter too
        ...(browseFilter
          ? {
              [initialFilterConfig.value.browse_filter.key]:
                state.filters?.[browseFilter.key],
            }
          : null),
      };
    },
  });
}

/**
 * Query results and filters from server.
 *
 * @param {object} options
 * @param {object} options.state
 * @param {boolean} options.showingResults
 */
function useItemsResult({ state, showingResults }) {
  const variables = computed(() => {
    const query = {
      ...state.value.filters,
      catalog_fts: state.value.search || undefined,
      orderbykey: state.value.sort,
      limitfrom: 0,
    };
    return { input: { query_structure: JSON.stringify(query) } };
  });

  const query = useQuery(itemsQuery, variables, {
    enabled: showingResults,
    keepPreviousResult: true,
    notifyOnNetworkStatusChange: true, // otherwise networkStatus will not be updated
  });

  const firstNewResultIndex = ref(null);

  watch(variables, () => {
    firstNewResultIndex.value = null;
  });

  const results = computed(() => query.result.value?.totara_catalog_items);
  const loadingResults = computed(() => query.loading.value);
  const loadingMore = computed(
    () => query.networkStatus.value == NetworkStatus.fetchMore
  );
  const showEmptyState = computed(
    () => query.result.value?.totara_catalog_items.items.length == 0
  );

  const canLoadMore = ref(false);
  watch(results, results => {
    canLoadMore.value = !results.endofrecords;
  });

  function loadMore() {
    if (query.loading.value) {
      return;
    }
    query.fetchMore({
      variables: {
        input: {
          query_structure: JSON.stringify({
            ...JSON.parse(query.variables.value.input.query_structure),
            limitfrom: results.value?.limitfrom ?? 0,
          }),
        },
      },
      updateQuery: (previousResult, { fetchMoreResult }) => {
        const newVal = fetchMoreResult.totara_catalog_items;
        if (newVal.items.length === 0) {
          // no more items to fetch
          canLoadMore.value = false;
          return previousResult;
        }
        // tell UI what the index of the first new item is so it can be focused
        firstNewResultIndex.value =
          previousResult.totara_catalog_items.items.length;
        // merge new items into result
        return produce(previousResult, draft => {
          draft.totara_catalog_items = {
            ...newVal,
            items: [...draft.totara_catalog_items.items, ...newVal.items],
          };
        });
      },
    });
  }

  return {
    results,
    firstNewResultIndex,
    loadingResults,
    loadingMore,
    loadMore,
    showEmptyState,
    canLoadMore,
  };
}

/**
 * Get computed filter config.
 *
 * @param {object} options
 * @param {object} options.initialFilterConfig
 * @param {object} options.results
 * @param {boolean} options.showingResults
 */
function useFilterConfig({ initialFilterConfig, results, showingResults }) {
  return computed(() => {
    // use updated value if we have it, otherwise use the standard filters we fetched on page load
    let filterConfig = initialFilterConfig.value;
    if (showingResults.value && results.value) {
      const { filters, browse_filter, sort } = results.value;
      filterConfig = { filters, browse_filter, sort };
    }

    // normalise '' id to null, as that is more convenient to deal with
    return produce(filterConfig, draft => {
      if (draft.filters) {
        for (const filter of draft.filters) {
          for (const option of filter.options ?? []) {
            if (option.id === '') {
              option.id = null;
            }
          }
        }
      }
      if (draft.browse_filter) {
        // remove '' id (the 'All' option) from browse filter because a reset button is added elsewhere
        draft.browse_filter.options = draft.browse_filter.options.filter(
          opt => opt.id !== ''
        );
      }
    });
  });
}

/**
 * Query results and filters from server.
 *
 * @param {object} options
 * @param {object} options.state
 * @param {object} options.filterConfig
 */
function useUiValues({ state, filterConfig }) {
  // processed to be more convenient for filtering UI
  const filterValue = computed({
    get: () => {
      return {
        search: state.value.search,
        ...state.value.filters,
      };
    },
    set: val => {
      const { search, ...filters } = val || {};
      state.replace({
        ...state.value,
        search,
        filters: minimiseFilterValue(filters),
      });
    },
  });

  const getDefaultSort = () =>
    (filterConfig.value.sort &&
      getActiveOption(filterConfig.value.sort.options)) ||
    'text';

  // if the user has selected a specific value, use that, otherwise pick a default
  const sortValue = computed({
    get: () => state.value.sort || getDefaultSort(),
    set: val => {
      // only save changed value if it is not the default.
      // that way, we can switch when the default changes
      // (e.g. when filtering by search/catalog_fts)
      state.replace({
        ...state.value,
        sort: val == getDefaultSort() ? null : val,
      });
    },
  });

  return { filterValue, sortValue };
}

/**
 * Remove empty filters.
 *
 * @param {object} filters
 * @returns {object}
 */
function minimiseFilterValue(filters) {
  if (!filters) {
    return null;
  }
  filters = Object.fromEntries(
    Object.entries(filters).filter(([, value]) => {
      return typeof value === 'object' ? hasKeys(value) : value != null;
    })
  );

  return hasKeys(filters) ? filters : null;
}

/**
 * Get the active option of a filter
 *
 * @param {Array<{ id: string, active: boolean }>} options
 * @returns {string}
 */
function getActiveOption(options) {
  return options.find(x => x.active)?.id;
}
