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

import { mount } from '@vue/test-utils';
import FieldContextProvider from '../FieldContextProvider';
import { h } from 'vue';

const FieldContextReceiver = {
  inject: ['reformFieldContext'],
  render: () => null,
};

describe('FieldContextProvider', () => {
  it('provides context to field', () => {
    const wrapper = mount(FieldContextProvider, {
      props: {
        id: 'test-id',
        labelId: 'test-label-id',
        ariaDescribedby: 'test-aria-describedby',
      },
      slots: {
        default: () => h(FieldContextReceiver),
      },
    });

    const receiver = wrapper.findComponent(FieldContextReceiver).vm;

    expect(receiver.reformFieldContext.getId()).toBe('test-id');
    expect(receiver.reformFieldContext.getLabelId()).toBe('test-label-id');
    expect(receiver.reformFieldContext.getAriaDescribedby()).toBe(
      'test-aria-describedby'
    );
  });
});
