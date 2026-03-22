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
import CourseSet from 'totara_program/components/manage_program/content/CourseSet';

describe('CourseSet', () => {
  it('sorted courses are sorted correctly', () => {
    let wrapper = shallowMount(CourseSet, {
      props: {
        courses: [1, 3, 4],
      },
    });
    expect(wrapper.vm.sortedCourses).toEqual([1, 3, 4]);

    wrapper = shallowMount(CourseSet, {
      props: {
        courses: [5, 3, 95],
      },
    });
    expect(wrapper.vm.sortedCourses).toEqual([5, 3, 95]);

    const c1 = { id: '5', sortorder: 1 };
    const c2 = { id: '6', sortorder: 2 };
    const c3 = { id: '85', sortorder: 3 };
    const c4 = { id: '12', sortorder: 4 };
    const c5 = { id: '546', sortorder: 5 };

    wrapper = shallowMount(CourseSet, {
      props: {
        courses: [c1, c2, c3, c4, c5],
      },
    });
    expect(wrapper.vm.sortedCourses).toEqual([c1, c2, c3, c4, c5]);

    wrapper = shallowMount(CourseSet, {
      props: {
        courses: [c1, c5, c2, c4, c3],
      },
    });
    expect(wrapper.vm.sortedCourses).toEqual([c1, c2, c3, c4, c5]);
  });

  it('changing the next set operator works as expected when not editing the last course set', async () => {
    const props = {
      id: 5,
      last: false,
      courses: [],
      editingOther: false,
      nextsetoperator: 'NEXTSETOPERATOR_THEN',
    };
    let mutate = jest.fn(() => {
      return {
        data: { totara_program_update_courseset_nextset_operator: true },
      };
    });
    let wrapper = shallowMount(CourseSet, {
      props,
      global: {
        mocks: {
          $apollo: {
            mutate,
          },
        },
      },
    });

    await wrapper.vm.toggleChange('NEXTSETOPERATOR_OR');
    expect(wrapper.emitted('change-next-set')[0]).toEqual([
      5,
      'NEXTSETOPERATOR_OR',
    ]);
  });

  it('changing the next set operator when a new items is added and saved', async () => {
    const props = {
      id: 5,
      last: true,
      courses: [],
      editingOther: false,
      nextsetoperator: 'NEXTSETOPERATOR_THEN',
    };
    let mutate = jest.fn(() => {
      return {
        data: { totara_program_update_courseset_nextset_operator: true },
      };
    });
    let wrapper = shallowMount(CourseSet, {
      props,
      global: {
        mocks: {
          $apollo: {
            mutate,
          },
        },
      },
    });

    await wrapper.setProps({ last: false, editingOther: true });
    expect(wrapper.vm.canChangeOperator).toBeFalse();

    await wrapper.vm.toggleChange('NEXTSETOPERATOR_OR');
    expect(wrapper.emitted('change-next-set')[0]).toEqual([
      5,
      'NEXTSETOPERATOR_OR',
    ]);
    // do what the handler should do
    await wrapper.setProps({ nextsetoperator: 'NEXTSETOPERATOR_OR' });
    expect(mutate).not.toHaveBeenCalled();
    expect(wrapper.emitted('change-next-set').length).toBe(1);

    await wrapper.setProps({
      editingOther: false,
    });
    expect(wrapper.emitted('change-next-set').length).toBe(2);
    expect(mutate).toHaveBeenCalled();
  });

  it('Cancelling adding a course set should reset nextcoursesetoperator lon last course set', async () => {
    const props = {
      id: 5,
      last: true,
      courses: [],
      editingOther: false,
      nextsetoperator: 'NEXTSETOPERATOR_THEN',
    };
    let mutate = jest.fn(() => {
      return {
        data: { totara_program_update_courseset_nextset_operator: true },
      };
    });
    let wrapper = shallowMount(CourseSet, {
      props,
      global: {
        mocks: {
          $apollo: {
            mutate,
          },
        },
      },
    });

    await wrapper.setProps({ last: false, editingOther: true });
    expect(wrapper.vm.canChangeOperator).toBeFalse();

    await wrapper.vm.toggleChange('NEXTSETOPERATOR_OR');
    expect(wrapper.emitted('change-next-set')[0]).toEqual([
      5,
      'NEXTSETOPERATOR_OR',
    ]);
    // do what the handler should do
    await wrapper.setProps({ nextsetoperator: 'NEXTSETOPERATOR_OR' });
    expect(mutate).not.toHaveBeenCalled();
    expect(wrapper.emitted('change-next-set').length).toBe(1);

    await wrapper.setProps({
      editingOther: false,
      last: true,
    });
    expect(wrapper.emitted('change-next-set')[1]).toEqual([
      5,
      'NEXTSETOPERATOR_THEN',
    ]);
    expect(wrapper.emitted('change-next-set').length).toBe(2);
    expect(mutate).not.toHaveBeenCalled();
  });

  it('Minimum score lozenge displays when expected', () => {
    let props = {
      courses: [],
      coursesumfield: 5,
      completiontype: 'SOME',
    };
    let wrapper = shallowMount(CourseSet, {
      props,
    });
    expect(wrapper.vm.showMinimumScore).toBe(true);

    props.completiontype = 'ANY';
    wrapper = shallowMount(CourseSet, {
      props,
    });
    expect(wrapper.vm.showMinimumScore).toBe(false);

    props.completiontype = 'OPTIONAL';
    wrapper = shallowMount(CourseSet, {
      props,
    });
    expect(wrapper.vm.showMinimumScore).toBe(false);

    props.completiontype = 'ALL';
    wrapper = shallowMount(CourseSet, {
      props,
    });
    expect(wrapper.vm.showMinimumScore).toBe(false);

    props.completiontype = 'SOME';
    props.coursesumfield = 0;
    wrapper = shallowMount(CourseSet, {
      props,
    });
    expect(wrapper.vm.showMinimumScore).toBe(false);

    wrapper = shallowMount(CourseSet, {
      props: {
        courses: [],
        completiontype: 'SOME',
      },
    });
    expect(wrapper.vm.showMinimumScore).toBe(false);
  });
});
