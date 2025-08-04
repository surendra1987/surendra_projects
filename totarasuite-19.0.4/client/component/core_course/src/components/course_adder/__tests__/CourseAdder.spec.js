/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Brian Barnes <brian.barnes@totara.com>
 * @module core_course
 */
import { shallowMount } from '@vue/test-utils';
import CourseAdder from '../CourseAdder';

describe('CourseAdder', () => {
  it('filter reset button should only show when filters are applied', () => {
    let adder = shallowMount(CourseAdder, {
      props: {},
      global: {
        mocks: {
          $apollo: {
            addSmartQuery() {},
            queries: {
              courses: {
                loading: false,
              },
            },
          },
        },
      },
    });

    expect(adder.vm.hasFilters).toBeFalse();

    adder.setData({ filters: { search: 'test' } });
    expect(adder.vm.hasFilters).toBeTrue();

    adder.setData({ selectedCategory: { name: 'junk', id: '5' } });
    expect(adder.vm.hasFilters).toBeTrue();

    adder.setData({ filters: { search: '' } });
    expect(adder.vm.hasFilters).toBeTrue();

    adder.setData({ selectedCategory: null });
    expect(adder.vm.hasFilters).toBeFalse();

    adder.setData({
      selectedCategory: { name: 'another', id: '3' },
      filters: { search: 'a' },
    });
    expect(adder.vm.hasFilters).toBeTrue();

    adder.vm.resetFilters();
    expect(adder.vm.hasFilters).toBeFalse();
  });

  it('filters are applied correctly', async () => {
    let adder = shallowMount(CourseAdder, {
      props: {},
      global: {
        mocks: {
          $apollo: {
            addSmartQuery() {},
            queries: {
              courses: {
                loading: false,
              },
            },
          },
        },
      },
    });

    // Base case
    expect(adder.vm.coreFilters()).toEqual({
      pagination: {
        limit: 20,
      },
      filters: {
        category_id: '0',
        search: '',
      },
    });

    // Search filter
    await adder.setData({
      filters: {
        search: 'sam',
      },
    });
    expect(adder.vm.coreFilters()).toEqual({
      pagination: {
        limit: 20,
      },
      filters: {
        search: 'sam',
        category_id: '0',
      },
    });

    // category
    await adder.setData({ selectedCategory: { id: '4' } });
    expect(adder.vm.coreFilters()).toEqual({
      pagination: {
        limit: 20,
      },
      filters: {
        search: 'sam',
        category_id: '4',
      },
    });

    adder.vm.resetFilters();
    expect(adder.vm.coreFilters()).toEqual({
      pagination: {
        limit: 20,
      },
      filters: {
        search: '',
        category_id: '0',
      },
    });

    // Add aditional filters
    await adder.setProps({
      customQueryFilters: {
        completion_tracked: false,
      },
    });
    expect(adder.vm.coreFilters()).toEqual({
      pagination: {
        limit: 20,
      },
      filters: {
        category_id: '0',
        search: '',
        completion_tracked: false,
      },
    });
  });
});
