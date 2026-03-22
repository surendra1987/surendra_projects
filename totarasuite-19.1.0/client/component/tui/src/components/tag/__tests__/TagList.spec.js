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
 * @author Tatsuhiro Kirihara <tatsuhiro.kirihara@totaralearning.com>
 * @module tui
 */

import { shallowMount } from '@vue/test-utils';
import TagList from '../TagList';
import { nextTick } from 'vue';

jest.unmock('tui/util');

const props = {
  tags: [{ id: 1, text: 'Tui' }],
};

describe('TagList', () => {
  it('debounching filter works as expected', async () => {
    jest.useFakeTimers();
    let filterListener = jest.fn();
    let wrapper = shallowMount(TagList, {
      props: {
        ...props,
        onFilter: filterListener,
      },
    });

    // Normal conditions
    wrapper.vm.itemName = 'x';
    await nextTick();
    expect(filterListener).toHaveBeenCalledWith('x');

    wrapper.vm.itemName = 'abc';
    await nextTick();
    expect(filterListener).toHaveBeenCalledWith('abc');

    // Debouncing conditions
    filterListener = jest.fn();
    wrapper = shallowMount(TagList, {
      props: {
        ...props,
        debounceFilter: true,
        onFilter: filterListener,
      },
    });

    wrapper.vm.itemName = 'y';
    await nextTick();
    expect(filterListener).not.toHaveBeenCalled();
    jest.runAllTimers();
    expect(filterListener).toHaveBeenCalledWith('y');

    wrapper.vm.itemName = 'ab';
    await nextTick();
    wrapper.vm.itemName = 'abc';
    await nextTick();
    wrapper.vm.itemName = 'abcd';
    await nextTick();
    wrapper.vm.itemName = 'abcde';

    // debounce has been set to 500
    jest.runAllTimers();
    expect(filterListener).toHaveBeenCalledTimes(2);
    expect(filterListener).not.toHaveBeenCalledWith('ab');
    expect(filterListener).not.toHaveBeenCalledWith('abc');
    expect(filterListener).not.toHaveBeenCalledWith('abcd');
    expect(filterListener).toHaveBeenCalledWith('abcde');
    wrapper.setProps({ items: ['a', 'b'] });
    await nextTick();
  });
});
