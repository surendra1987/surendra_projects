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
 * @author Simon Chester <simon.chester@totaralearning.com>
 * @module tui
 */

import { h, nextTick } from 'vue';
import { shallowMount } from '@vue/test-utils';
import PopoverTrigger from '../PopoverTrigger';

describe('PopoverTrigger', () => {
  it('triggers events', async () => {
    const changed = jest.fn();
    const wrapper = shallowMount(PopoverTrigger, {
      props: {
        triggers: ['click'],
        onOpenChanged: changed,
      },
      slots: {
        default() {
          return h('button');
        },
      },
    });
    const button = wrapper.find('button');
    expect(changed).not.toHaveBeenCalled();

    button.trigger('click');
    await nextTick();
    expect(changed).toHaveBeenCalledTimes(1);
    expect(changed.mock.calls[0][0]).toBe(true);

    button.trigger('click');
    await nextTick();
    expect(changed).toHaveBeenCalledTimes(2);
    expect(changed.mock.calls[1][0]).toBe(false);
  });
});
