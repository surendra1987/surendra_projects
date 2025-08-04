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

import { render, waitFor } from 'tui_test_utils/vtl';
import useFilteredResults from '../use_filtered_results';
import { produce } from 'tui/immutable';
import { navigateToParams, getLastSetParams } from 'tui/state/use_param_state';
import { ref } from 'vue';
import itemsQuery from 'totara_catalog/graphql/items';

jest.mock('tui/state/use_param_state');

const item = i => ({
  itemid: i,
  featured: false,
  title: `Item ${i}`,
  logo: null,
  image: null,
  redirecturl: null,
  objecttype: 'course',
  type_label: 'Course',
  hero_data_type: null,
  hero_data_text: null,
  hero_data_icon: null,
  description_enabled: false,
  description: null,
  progress_bar_enabled: false,
  progress_bar: null,
  text_placeholders_enabled: false,
  text_placeholders: null,
  icon_placeholders_enabled: false,
  icon_placeholders: null,
});

const filter = i => ({
  __typename: 'totara_catalog_filter',
  key: `filter${i}`,
  title: `Filter ${i}`,
  type: 'multi',
  options: [],
});

const option = (name, extra) => ({
  __typename: 'totara_catalog_option',
  id: name,
  label: name,
  active: false,
  ...extra,
});

const initialFilterConfig = {
  filters: [filter(10), filter(11)],
  browse_filter: filter(12),
  sort: {
    key: 'orderbykey',
    title: 'Sort by',
    options: [option('volume'), option('weight', { active: true })],
  },
};

const alwaysShowResults = false;

const typicalResponse = {
  totara_catalog_items: {
    items: [item(1), item(2)],
    limitfrom: 30,
    maxcount: 30,
    filters: [filter(20), filter(21)],
    browse_filter: filter(22),
    sort: {
      key: 'orderbykey',
      title: 'Sort by',
      options: [option('volume', { active: true }), option('weight')],
    },
    is_filtered: true,
    endofrecords: false,
  },
};

function createMockRequest(query) {
  return {
    query: itemsQuery,
    variables: {
      input: {
        query_structure: JSON.stringify({
          ...query,
          limitfrom: query.limitfrom || 0,
        }),
      },
    },
  };
}

function factory(options = {}) {
  let result;
  render(
    {
      setup() {
        result = useFilteredResults(
          ref(options.initialFilterConfig || initialFilterConfig),
          ref(alwaysShowResults)
        );
      },
      render() {
        return null;
      },
    },
    {
      global: {
        mockQueries: options.mockQueries || [],
      },
    }
  );
  return { result };
}

describe('useFilteredResults', () => {
  it('stores filter in URL', async () => {
    const { result } = factory({
      mockQueries: [
        {
          request: createMockRequest({
            filter10: 'abc',
            catalog_fts: 'foobar',
            orderbykey: 'colour',
          }),
          result: { data: typicalResponse },
        },
        {
          request: createMockRequest({
            filter10: 'abc',
            catalog_fts: 'baz',
            orderbykey: 'colour',
          }),
          result: { data: typicalResponse },
        },
      ],
    });

    expect(result.filterValue.value).toEqual({});

    // updating URL should update filter
    navigateToParams({
      catalog_fts: 'foobar',
      filter10: 'abc',
      orderbykey: 'colour',
    });

    await waitFor(() => {
      expect(result.filterValue.value).toEqual({
        search: 'foobar',
        filter10: 'abc',
      });
      expect(result.sortValue.value).toBe('colour');
    });

    // Updating filter should update URL
    result.filterValue.value = { ...result.filterValue.value, search: 'baz' };

    await waitFor(() => {
      expect(getLastSetParams()).toEqual({
        catalog_fts: 'baz',
        filter10: 'abc',
        filter11: null,
        orderbykey: 'colour',
      });
    });
  });

  it('makes a query to get new items and filter states when filtering', async () => {
    const response = jest.fn(() => ({ data: typicalResponse }));
    const { result } = factory({
      mockQueries: [
        {
          request: createMockRequest({
            filter1: 'abc',
            catalog_fts: 'foobar',
            orderbykey: 'colour',
          }),
          result: response,
        },
      ],
    });

    result.filterValue.value = { search: 'foobar', filter1: 'abc' };
    result.sortValue.value = 'colour';

    await waitFor(() => {
      expect(response).toHaveBeenCalled();
    });
  });

  it('gives you the items and filters loaded from the API when filtering', async () => {
    const { result } = factory({
      mockQueries: [
        {
          request: createMockRequest({
            filter10: 'abc',
          }),
          result: { data: typicalResponse },
        },
      ],
    });

    expect(result.filterDefs.value).toEqual(initialFilterConfig.filters);
    expect(result.results.value).toBeFalsy();

    result.filterValue.value = { filter10: 'abc' };

    await waitFor(() => {
      expect(result.results.value).toEqual(
        typicalResponse.totara_catalog_items
      );
    });
  });

  it('lets you load more', async () => {
    const response1 = jest.fn(() => ({ data: typicalResponse }));
    const response2 = jest.fn(() => ({
      data: produce(typicalResponse, draft => {
        draft.totara_catalog_items.items = [item(3), item(4)];
      }),
    }));
    const response3 = jest.fn(() => ({ data: typicalResponse }));
    const { result } = factory({
      mockQueries: [
        {
          request: createMockRequest({
            filter10: 'abc',
          }),
          result: response1,
        },
        {
          request: createMockRequest({
            filter10: 'abc',
            limitfrom: 30,
          }),
          result: response2,
        },
        {
          request: createMockRequest({
            filter10: 'def',
          }),
          result: response3,
        },
      ],
    });

    result.filterValue.value = { filter10: 'abc' };
    await waitFor(() => {
      expect(response1).toHaveBeenCalled();
    });
    expect(result.results.value.items.length).toBe(2);
    expect(result.firstNewResultIndex.value).toBe(null);

    // load more
    result.loadMore();

    await waitFor(() => {
      expect(response2).toHaveBeenCalled();
    });
    expect(result.results.value.items.length).toBe(4);
    expect(result.firstNewResultIndex.value).toBe(2);

    // confirm firstNewResultIndex gets reset if filters change
    result.filterValue.value = { filter10: 'def' };
    await waitFor(() => {
      expect(response3).toHaveBeenCalled();
    });
    expect(result.results.value.items.length).toBe(2);
    expect(result.firstNewResultIndex.value).toBe(null);
  });

  it('defaults `sortValue` to the selected value from the server', async () => {
    const { result } = factory({
      mockQueries: [
        {
          request: createMockRequest({
            filter10: 'abc',
          }),
          result: {
            data: typicalResponse,
          },
        },
      ],
    });

    expect(result.sortValue.value).toBe('weight');

    result.filterValue.value = { filter10: 'abc' };

    await waitFor(() => {
      expect(result.sortValue.value).toBe('volume');
    });
  });

  it("defaults to text sort when the server doesn't provide sort options", () => {
    const { result } = factory({
      initialFilterConfig: produce(initialFilterConfig, draft => {
        draft.sort = null;
      }),
    });

    expect(result.sortValue.value).toBe('text');
  });
});
