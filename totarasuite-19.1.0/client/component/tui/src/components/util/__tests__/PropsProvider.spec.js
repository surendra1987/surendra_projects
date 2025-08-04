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
import { mount } from '@vue/test-utils';
import { plainWrapperArray } from 'tui_test_utils';
import PropsProvider from '../PropsProvider';

const SecondLevel = {
  props: ['j'],
  render() {
    return h('span', {}, [`j: ${this.j}`]);
  },
};

const TestComp = {
  props: ['i', 'j'],
  render() {
    return h('p', {}, [`${this.i}, ${this.j}`, h(SecondLevel)]);
  },
};

const PropsProviderWrap = {
  props: ['provide'],
  render() {
    return h('div', {}, [
      h(PropsProvider, this.$props, () => this.$slots.default()),
    ]);
  },
};

describe('PropsProvider', () => {
  it('passes props and events to direct children', async () => {
    const handler = jest.fn();
    const wrapper = mount(PropsProviderWrap, {
      slots: {
        default: () => [
          h(TestComp, { i: 1 }),
          'hi',
          h(TestComp, { i: 2 }),
          h(TestComp, { i: 3, j: 3 }),
        ],
      },
      props: {
        provide({ props }) {
          return {
            props: {
              j: (props.i || 0) + 1000,
              onClick: handler,
            },
          };
        },
      },
    });

    expect(
      plainWrapperArray(wrapper.findAllComponents(TestComp)).map(w => w.props())
    ).toMatchObject([
      { i: 1, j: 1001 },
      { i: 2, j: 1002 },
      { i: 3, j: 3 },
    ]);
    expect(
      plainWrapperArray(wrapper.findAllComponents(SecondLevel)).map(w =>
        w.props()
      )
    ).toMatchObject([{ j: undefined }, { j: undefined }, { j: undefined }]);

    expect(handler).toHaveBeenCalledTimes(0);
    wrapper.findComponent(TestComp).vm.$emit('click', 'foo');
    expect(handler).toHaveBeenCalledWith('foo');

    expect(wrapper.element).toMatchSnapshot();
  });

  it('merges multiple event handlers', () => {
    const handlerInline = jest.fn();
    const handlerProvided = jest.fn();
    const wrapper = mount(PropsProviderWrap, {
      slots: {
        default: () => [
          h(TestComp, { i: 1, onClick: handlerInline }),
          h(TestComp, { i: 2, onClick: handlerInline }),
          h(TestComp, { i: 3 }),
        ],
      },
      props: {
        provide({ props }) {
          return {
            props: {
              j: (props.i || 0) + 1000,
              ...(props.i != 2 ? { onClick: handlerProvided } : {}),
            },
          };
        },
      },
    });

    const comps = wrapper.findAllComponents(TestComp);

    comps.at(0).vm.$emit('click', 'first');
    expect(handlerInline).toHaveBeenCalledWith('first');
    expect(handlerProvided).toHaveBeenCalledWith('first');

    comps.at(1).vm.$emit('click', 'second');
    expect(handlerInline).toHaveBeenCalledWith('second');
    expect(handlerProvided).not.toHaveBeenCalledWith('second');

    comps.at(2).vm.$emit('click', 'third');
    expect(handlerInline).not.toHaveBeenCalledWith('third');
    expect(handlerProvided).toHaveBeenCalledWith('third');
  });
});
