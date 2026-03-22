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
import Grid from '../Grid';
import GridItem from '../GridItem';
import { h } from 'vue';

const props = {
  id: 'grid',
  direction: 'vertical',
  maxUnits: '16',
  stackAt: 960,
};

const slots = {
  default: function() {
    return h(GridItem, {
      props: {
        units: 8,
        order: 2,
        grows: true,
        shrinks: false,
        overflows: true,
        hyphens: false,
        sizeData: {
          gutterSize: '12px',
          maxGridUnits: 16,
          numberOfSuppliedGridItems: 8,
        },
      },
    });
  },
};

function factory() {
  return shallowMount(Grid, {
    props,
    slots,
  });
}

describe('Grid', () => {
  describe('Grid with default gridTag', () => {
    it('gridClasses method returns Array of default classes', () => {
      const wrapper = factory();
      expect(Array.isArray(wrapper.vm.gridClasses())).toBe(true);
    });

    it('gridClasses method returns Array of default and additional classes', () => {
      const wrapper = factory();
      let defaultCount = wrapper.vm.gridClasses().length,
        additionalClasses = ['customClass', 'anotherClass'];
      expect(wrapper.vm.gridClasses(additionalClasses).length).toBe(
        defaultCount + additionalClasses.length
      );
    });
  });
});
