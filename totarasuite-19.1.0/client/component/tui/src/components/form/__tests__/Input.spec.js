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

import { shallowMount } from '@vue/test-utils';
import Input from '../Input';

describe('Input', () => {
  it('supports autofocus', () => {
    const eventFunc = jest.fn();
    const wrapper = shallowMount(Input, {
      props: {
        type: 'text',
        autofocus: true,
        onInput: eventFunc,
        onChange: eventFunc,
        onSubmit: eventFunc,
      },
      attachTo: document.body,
    });
    expect(wrapper.element).toBe(document.activeElement);
  });
});
