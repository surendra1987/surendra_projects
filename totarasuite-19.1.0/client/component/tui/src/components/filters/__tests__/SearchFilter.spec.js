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
 * @author Kevin Hottinger <kevin.hottinger@totaralearning.com>
 * @module tui
 */

import { nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import SearchFilter from '../SearchFilter';
import { axe } from 'jest-axe';

describe('SearchFilter', () => {
  it('should not have any accessibility violations', async () => {
    const wrapper = mount(SearchFilter, {
      props: {
        id: 'tempid',
        dropLabel: false,
        ariaLabel: 'label',
        label: 'bla',
      },
      global: {
        stubs: ['SearchIcon'],
      },
    });
    const results = await axe(wrapper.element, {
      rules: {
        region: { enabled: false },
      },
    });
    expect(results).toHaveNoViolations();
  });

  it('should have a clear icon when characters have been entered', async () => {
    const wrapper = mount(SearchFilter, {
      props: {
        id: 'tempid',
        dropLabel: false,
        ariaLabel: 'label',
        label: 'bla',
      },
      attachTo: document.body,
    });

    expect(
      wrapper.find('.tui-searchFilter__group-clearContainer').exists()
    ).toBeFalse();

    wrapper.setProps({ value: 'A' });
    await nextTick();
    expect(
      wrapper.find('.tui-searchFilter__group-clearContainer').exists()
    ).toBeTrue();

    wrapper.setProps({ value: 'A Name' });
    await nextTick();
    expect(
      wrapper.find('.tui-searchFilter__group-clearContainer').exists()
    ).toBeTrue();

    wrapper.find('.tui-searchFilter__group-clearContainer').element.click();
    await nextTick();
    expect(wrapper.emitted('clear')).toEqual([[]]);
    expect(wrapper.emitted('input')).toEqual([['']]);

    expect(wrapper.find('input').element).toBe(document.activeElement);
  });
});
