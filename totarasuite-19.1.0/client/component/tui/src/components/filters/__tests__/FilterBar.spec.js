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

import { mount } from '@vue/test-utils';
import FilterBar from '../FilterBar';
import { axe } from 'jest-axe';

describe('FilterBar', () => {
  it('should not have any accessibility violations', async () => {
    const wrapper = mount(FilterBar, {
      props: {
        title: 'title text',
        value: {
          optionA: 'aa',
          optionB: '',
          optionC: 'cc',
          optionD: '',
        },
      },
      global: {
        stubs: ['SliderIcon'],
      },
    });
    const results = await axe(wrapper.element, {
      rules: {
        region: { enabled: false },
      },
    });
    expect(results).toHaveNoViolations();
  });
});
