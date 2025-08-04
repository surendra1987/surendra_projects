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
 * @author Alvin Smith <alvin.smith@totaralearning.com>
 * @module tui
 */

import { mount } from '@vue/test-utils';
import Dropdown from '../Dropdown';
import DropdownItem from '../DropdownItem';
import { axe } from 'jest-axe';

jest.mock('tui/dom/focus', () => {
  return {
    getFocusableElements: jest.fn(() => {
      return [
        { focus: jest.fn(), contains: () => false },
        { focus: jest.fn(), contains: () => false },
        { focus: jest.fn(), contains: () => false },
      ];
    }),
  };
});

let wrapper;

describe('Dropdown', () => {
  beforeEach(() => {
    const items = [DropdownItem, DropdownItem, DropdownItem];

    wrapper = mount(Dropdown, {
      slots: {
        default: items,
      },
    });
  });

  it('manage clicking outside accordingly', async () => {
    const el = document.createElement('div');
    const event = {
      target: el,
    };

    await wrapper.setData({ toggleOpen: true });
    wrapper.vm.$_clickedOutside({
      target: wrapper.vm.$refs.trigger,
    });
    expect(wrapper.vm.isOpen).toBeFalse();

    await wrapper.setData({ toggleOpen: true });
    await wrapper.setProps({ canClose: false });
    wrapper.vm.$_clickedOutside(event);
    expect(wrapper.vm.isOpen).toBeTruthy();
  });

  it('Escape key is handled as expected', async () => {
    await wrapper.setData({ toggleOpen: true });
    let spy = jest.fn();

    // Confirm closing works as expected
    await wrapper.setData({ toggleOpen: true });
    wrapper.vm.$_handleEscape({
      stopPropagation: spy,
      preventDefault: jest.fn(),
    });
    expect(wrapper.vm.toggleOpen).toBeFalse();
    expect(spy).toHaveBeenCalledTimes(1);

    // not closeable through escaoe
    await wrapper.setProps({ canClose: false });
    await wrapper.setData({ toggleOpen: true });
    wrapper.vm.$_handleEscape({
      stopPropagation: spy,
      preventDefault: jest.fn(),
    });
    expect(wrapper.vm.toggleOpen).toBeTrue();
    expect(spy).toHaveBeenCalledTimes(1);
  });

  it('should not have any accessibility violations', async () => {
    const results = await axe(wrapper.element, {
      rules: {
        region: { enabled: false },
      },
    });
    expect(results).toHaveNoViolations();
    expect(true).toBeTruthy();
  });

  it("Closed dropdown doesn't prevent default keypresses", async () => {
    let spy = jest.fn();
    let event = {
      key: 'Tab',
      preventDefault: spy,
    };

    // test keys don't interact
    ['ArrowDown', 'ArrowUp', 'Tab'].forEach(key => {
      event.key = key;
      wrapper.vm.$_keydown(event);
      expect(wrapper.vm.isOpen).toBe(false);
      expect(spy).not.toHaveBeenCalled();
    });
  });

  it('Keyboard Tab navigation works as expected', async () => {
    let spy = jest.fn();
    let event = {
      key: 'Tab',
      preventDefault: spy,
    };

    await wrapper.setData({ toggleOpen: true });
    expect(wrapper.vm.isOpen).toBeTrue();
    event.key = 'Tab';
    wrapper.vm.$_keydown(event);
    expect(wrapper.vm.activeNodeIndex).toBe(0);
    expect(spy).toHaveBeenCalledTimes(1);

    wrapper.vm.$_keydown(event);
    expect(wrapper.vm.isOpen).toBeFalse();
    // no change from previous value
    expect(spy).toHaveBeenCalledTimes(1);
  });
});
