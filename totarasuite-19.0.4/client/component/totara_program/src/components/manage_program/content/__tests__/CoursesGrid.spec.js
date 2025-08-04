/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @module totara_program
 */

import { shallowMount } from '@vue/test-utils';
import CoursesGrid from 'totara_program/components/manage_program/content/CoursesGrid';

const stubs = {
  passthrough: {
    render() {
      return this.$slots.default && this.$slots.default();
    },
  },
};

describe('CoursesGrid', () => {
  it('computed courses works properly', () => {
    let wrapper = shallowMount(CoursesGrid, {
      props: {
        courses: [{ id: 1 }, { id: 42 }, { id: 345 }, { id: 3 }, { id: 2 }],
      },
      stubs,
    });

    expect(wrapper.vm.existing).toEqual([1, 42, 345, 3, 2]);

    wrapper = shallowMount(CoursesGrid, {
      props: {
        courses: [],
      },
      stubs,
    });
    expect(wrapper.vm.existing).toEqual([]);
  });
});
