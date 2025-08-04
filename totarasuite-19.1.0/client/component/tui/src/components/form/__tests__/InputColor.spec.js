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
 * @author Dave Wallace <dave.wallace@totaralearning.com>
 * @module tui
 */

import { shallowMount } from '@vue/test-utils';
import InputColor from '../InputColor';
import { axe } from 'jest-axe';

const goodValue = '#FF0000';

describe('InputColor', () => {
  it('should not have an a11y violations', async () => {
    const wrapper = shallowMount(InputColor, {
      props: {
        ariaDescribedby: '#ariaDescription',
        ariaLabel: 'aria label',
        ariaLabelledby: '#ariaLabel',
        disabled: true,
        id: 'inputid',
        maxlength: 7,
        name: 'colorinput',
        readonly: true,
        required: true,
        value: goodValue,
      },
    });
    const results = await axe(wrapper.element);
    expect(results).toHaveNoViolations();
  });
});
