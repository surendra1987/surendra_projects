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

import { shallowMount } from '@vue/test-utils';
import Separator from '../Separator';

describe('Separator', () => {
  it('Checks v-else output exists', () => {
    const wrapper = shallowMount(Separator, {
      props: {
        id: 'separator',
        thick: true,
        spread: true,
      },
    });
    expect(wrapper.findAll('span').length).toBe(0);
  });

  // test slot-provided output
  it('Checks v-if output exists', () => {
    const wrapper = shallowMount(Separator, {
      slots: {
        default: '<span>ok</span>',
      },
    });
    let div = wrapper.find('div');
    expect(div.find('span').exists()).toBe(true);
  });
});
