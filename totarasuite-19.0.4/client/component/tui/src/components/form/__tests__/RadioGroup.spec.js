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

import { h } from 'vue';
import { shallowMount } from '@vue/test-utils';
import RadioGroup from '../RadioGroup';

const PropsProviderStub = {
  props: ['provide'],
  render() {
    return h('div', [this.$slots.default()]);
  },
};

describe('RadioGroup', () => {
  it('passes selected info to children using PropsProvider', () => {
    const handleInput = jest.fn();

    const wrapper = shallowMount(RadioGroup, {
      slots: {
        default: ['test slot content'],
      },
      global: {
        stubs: {
          PropsProvider: PropsProviderStub,
        },
      },
      props: {
        name: 'city',
        value: 'foo',
        disabled: false,
        required: true,
        onInput: handleInput,
      },
    });

    const providerProps = wrapper.findComponent(PropsProviderStub).props();
    const info = { props: { value: 'foo' } };
    expect(providerProps.provide(info)).toMatchObject({
      props: {
        name: 'city',
        checked: true,
        disabled: null,
        required: true,
      },
    });

    const handleSelect = providerProps.provide(info).listeners.select;
    expect(handleInput).toHaveBeenCalledTimes(0);
    handleSelect('bar');
    expect(handleInput).toHaveBeenCalledWith('bar');
  });
});
