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
 * Please contact [licensing@totaralearning.com] for more information.
 *
 * @author Brian Barnes <brian.barnes@totara.com>
 * @module tui
 */

import { mount } from '@vue/test-utils';
import ProgressTrackerNav from '../ProgressTrackerNav';

describe('ProgressTrackerNav', () => {
  it('isVertical should remain true if forceVertical is set', () => {
    let wrapper = mount(ProgressTrackerNav, {
      props: {
        forceVertical: true,
        items: [
          {
            id: 1,
            description: 'Basic knowledge description',
            label: 'Basic knowledge',
          },
          {
            id: 2,
            description: 'Competent with supervision description',
            label: 'Competent with supervision',
          },
        ],
      },
      computed: {
        minWidth() {
          return 1800;
        },
      },
      stubs: ['CloseButton'],
    });

    expect(wrapper.vm.isVertical).toBeTrue();
    wrapper.vm.resize();
    expect(wrapper.vm.isVertical).toBeTrue();

    wrapper = mount(ProgressTrackerNav, {
      propsData: {
        items: [
          {
            id: 1,
            description: 'Basic knowledge description',
            label: 'Basic knowledge',
          },
          {
            id: 2,
            description: 'Competent with supervision description',
            label: 'Competent with supervision',
          },
        ],
      },
      computed: {
        minWidth() {
          return 1800;
        },
      },
      stubs: ['CloseButton'],
    });
    expect(wrapper.vm.isVertical).toBeFalse();
    wrapper.vm.resize();
    expect(wrapper.vm.isVertical).toBeTrue();
  });
});
