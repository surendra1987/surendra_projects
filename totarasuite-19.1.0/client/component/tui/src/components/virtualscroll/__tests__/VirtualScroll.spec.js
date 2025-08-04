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
 * @author Arshad Anwer <arshad.anwer@totaralearning.com>
 * @module tui
 */

import { mount } from '@vue/test-utils';
import VirtualScroll from '../VirtualScroll';
import { axe } from 'jest-axe';
import { h } from 'vue';

const props = {
  isLoading: false,
  dataList: [
    {
      id: '1',
      name: 'test 1',
    },
    {
      id: '2',
      name: 'test 2',
    },
  ],
  dataKey: 'id',
  ariaLabel: 'List',
};

function factory() {
  return mount(VirtualScroll, {
    props: props,
    slots: {
      item(props) {
        return h(
          'article',
          {
            attrs: {
              'aria-labelledby': `article-${props.item.id}`,
              'aria-setsize': props.setSize,
              'aria-posinset': props.posInSet,
              tabindex: '0',
            },
          },
          [h('div', { attrs: { id: `article-${props.item.id}` } }, 'hello')]
        );
      },

      footer() {
        return h('div', { class: 'loader' }, '');
      },
    },
  });
}

describe('VirtualScroll', () => {
  it('should not have any accessibility violations', async () => {
    const wrapper = factory();
    const results = await axe(wrapper.element, {
      rules: {
        region: { enabled: false },
      },
    });
    expect(results).toHaveNoViolations();
  });
});
