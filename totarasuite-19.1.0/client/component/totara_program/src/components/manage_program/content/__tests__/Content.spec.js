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
import Content from 'totara_program/components/manage_program/content/Content';

describe('Content', () => {
  it('Can remove unsaved course set', () => {
    let wrapper = shallowMount(Content, {
      props: {
        program: {
          coursesets: [],
        },
      },
    });

    expect(wrapper.vm.courseSets.length).toBe(0);

    wrapper.vm.addCourseSet();
    expect(wrapper.vm.courseSets.length).toBe(1);
    expect(wrapper.vm.courseSets[0].id).toBeNull();
    expect(wrapper.vm.editingCourseSet).toBeTrue();

    wrapper.vm.editChanged(false);
    expect(wrapper.vm.editingCourseSet).toBeFalse();

    wrapper.vm.editChanged(true);
    wrapper.vm.removeCourseSet(null);
    expect(wrapper.vm.editingCourseSet).toBeFalse();
    expect(wrapper.vm.courseSets.length).toBe(0);
  });

  it('Can remove saved courseSet', async () => {
    let wrapper = shallowMount(Content, {
      props: {
        program: {
          coursesets: [
            { id: 1, sortorder: 0 },
            { id: 5, sortorder: 1 },
            { id: 10, sortorder: 2 },
          ],
        },
      },
    });

    expect(wrapper.vm.courseSets.length).toBe(3);
    wrapper.vm.removeCourseSet(5);

    // trigger the watch
    expect(wrapper.vm.courseSets).toEqual([
      { id: 1, sortorder: 1 },
      { id: 10, sortorder: 2 },
    ]);
  });

  it('Can save new courseSet', () => {
    let wrapper = shallowMount(Content, {
      props: {
        program: {
          coursesets: [
            {
              id: 1,
              label: 'Course set 1',
              courses: [{ fullname: 'Course 1' }],
              completiontype: 'ALL',
              mincourses: 0,
              coursesumfield: 0,
              coursesumfieldtotal: 0,
              timeallowed: 0,
            },
            {
              id: 5,
              label: 'Course set 2',
              courses: [{ fullname: 'Course 1' }],
              completiontype: 'ALL',
              mincourses: 0,
              coursesumfield: 0,
              coursesumfieldtotal: 0,
              timeallowed: 0,
            },
            {
              id: 10,
              label: 'Course set 3',
              courses: [{ fullname: 'Course 1' }],
              completiontype: 'ALL',
              mincourses: 0,
              coursesumfield: 0,
              coursesumfieldtotal: 0,
              timeallowed: 0,
            },
            {
              id: null,
              label: 'Course set 4',
              courses: [{ fullname: 'Course 1' }],
              completiontype: 'ALL',
              mincourses: 0,
              coursesumfield: 0,
              coursesumfieldtotal: 0,
              timeallowed: 0,
            },
          ],
        },
      },
    });

    let partialCourseSet = {
      id: 12,
      label: 'Course set 4',
      courses: [{ fullname: 'Course 1' }],
      completiontype: 'ALL',
      mincourses: 0,
      coursesumfield: 0,
      coursesumfieldtotal: 0,
      timeallowed: 0,
    };
    expect(wrapper.vm.courseSets.length).toBe(4);

    wrapper.vm.updateCourseSet(partialCourseSet);
    expect(wrapper.vm.courseSets).toEqual([
      {
        id: 1,
        label: 'Course set 1',
        courses: [{ fullname: 'Course 1' }],
        completiontype: 'ALL',
        mincourses: 0,
        coursesumfield: 0,
        coursesumfieldtotal: 0,
        timeallowed: 0,
        sortorder: 1,
      },
      {
        id: 5,
        label: 'Course set 2',
        courses: [{ fullname: 'Course 1' }],
        completiontype: 'ALL',
        mincourses: 0,
        coursesumfield: 0,
        coursesumfieldtotal: 0,
        timeallowed: 0,
        sortorder: 2,
      },
      {
        id: 10,
        label: 'Course set 3',
        courses: [{ fullname: 'Course 1' }],
        completiontype: 'ALL',
        mincourses: 0,
        coursesumfield: 0,
        coursesumfieldtotal: 0,
        timeallowed: 0,
        sortorder: 3,
      },
      {
        id: 12,
        label: 'Course set 4',
        courses: [{ fullname: 'Course 1' }],
        completiontype: 'ALL',
        mincourses: 0,
        coursesumfield: 0,
        coursesumfieldtotal: 0,
        timeallowed: 0,
        sortorder: 4,
      },
    ]);
  });

  it('Can update already exist courseSet', () => {
    let wrapper = shallowMount(Content, {
      props: {
        program: {
          coursesets: [
            {
              id: 1,
              label: 'Course set 1',
              courses: [{ fullname: 'Course 1' }],
              completiontype: 'ALL',
              mincourses: 0,
              coursesumfield: 0,
              coursesumfieldtotal: 0,
              sortorder: 0,
              timeallowed: 0,
            },
            {
              id: 5,
              label: 'Course set 2',
              courses: [{ fullname: 'Course 1' }],
              completiontype: 'ALL',
              mincourses: 0,
              coursesumfield: 0,
              coursesumfieldtotal: 0,
              sortorder: 1,
              timeallowed: 0,
            },
            {
              id: 10,
              label: 'Course set 3',
              courses: [{ fullname: 'Course 1' }],
              completiontype: 'ALL',
              mincourses: 0,
              coursesumfield: 0,
              coursesumfieldtotal: 0,
              sortorder: 2,
              timeallowed: 0,
            },
          ],
        },
      },
    });

    let updatedCourseSet = {
      id: 10,
      label: 'Course set 4',
      courses: [{ fullname: 'Course 1' }, { fullname: 'Course 2' }],
      completiontype: 'ALL',
      mincourses: 0,
      coursesumfield: 0,
      coursesumfieldtotal: 0,
      sortorder: 2,
      timeallowed: 30,
    };
    // Before update.
    expect(wrapper.vm.courseSets.length).toBe(3);

    wrapper.vm.updateCourseSet(updatedCourseSet);

    // After update.
    expect(wrapper.vm.courseSets.length).toBe(3);
    expect(wrapper.vm.courseSets).toEqual([
      {
        id: 1,
        label: 'Course set 1',
        courses: [{ fullname: 'Course 1' }],
        completiontype: 'ALL',
        mincourses: 0,
        coursesumfield: 0,
        coursesumfieldtotal: 0,
        sortorder: 1,
        timeallowed: 0,
      },
      {
        id: 5,
        label: 'Course set 2',
        courses: [{ fullname: 'Course 1' }],
        completiontype: 'ALL',
        mincourses: 0,
        coursesumfield: 0,
        coursesumfieldtotal: 0,
        sortorder: 2,
        timeallowed: 0,
      },
      {
        id: 10,
        label: 'Course set 4',
        courses: [{ fullname: 'Course 1' }, { fullname: 'Course 2' }],
        completiontype: 'ALL',
        mincourses: 0,
        coursesumfield: 0,
        coursesumfieldtotal: 0,
        sortorder: 3,
        timeallowed: 30,
      },
    ]);
  });

  it('is last course set works', () => {
    let wrapper = shallowMount(Content, {
      props: {
        program: {
          coursesets: [{ id: 1, sortorder: '2' }],
        },
      },
    });

    expect(wrapper.vm.isLast(1)).toBeTrue();
    expect(wrapper.vm.isLast(5)).toBeFalse();

    wrapper = shallowMount(Content, {
      props: {
        program: {
          coursesets: [
            { id: 1, sortorder: '2' },
            { id: 2, sortorder: '1' },
            { id: 3, sortorder: '6' },
            { id: 4, sortorder: '100' },
            { id: 5, sortorder: '50' },
            { id: 6, sortorder: '4' },
            { id: 7, sortorder: '2' },
          ],
        },
      },
    });

    expect(wrapper.vm.isLast(4)).toBeTrue();
    expect(wrapper.vm.isLast(1)).toBeFalse();
    expect(wrapper.vm.isLast(7)).toBeFalse();
  });

  it('Can get Max and Min sort order from course sets', () => {
    let wrapper = shallowMount(Content, {
      props: {
        program: {
          coursesets: [
            {
              id: 1,
              label: 'Course set 1',
              sortorder: 1,
            },
            {
              id: 5,
              label: 'Course set 2',
              sortorder: 2,
            },
            {
              id: 10,
              label: 'Course set 3',
              sortorder: 3,
            },
            {
              id: 12,
              label: 'Course set 4',
              sortorder: 4,
            },
          ],
        },
      },
    });

    // Get Max and Min sort order from given course set.
    let maxSortOrder = wrapper.vm.maxSortOrder;
    let minSortOrder = wrapper.vm.minSortOrder;
    expect(maxSortOrder).toEqual(4);
    expect(minSortOrder).toEqual(1);
  });

  it('Can course set move', () => {
    let wrapper = shallowMount(Content, {
      props: {
        program: {
          coursesets: [
            {
              id: 1,
              label: 'Course set 1',
              sortorder: 1,
            },
            {
              id: 5,
              label: 'Course set 2',
              sortorder: 2,
            },
            {
              id: 10,
              label: 'Course set 3',
              sortorder: 3,
            },
            {
              id: 12,
              label: 'Course set 4',
              sortorder: 4,
            },
          ],
        },
      },
    });

    // Course set 10 (sort order 3) can move up and down.
    let result = wrapper.vm.canCourseSetMove(3);
    expect(result.canMoveUp).toBeTrue();
    expect(result.canMoveDown).toBeTrue();

    // Course set 1 (sort order 1) can only move down.
    result = wrapper.vm.canCourseSetMove(1);
    expect(result.canMoveUp).toBeFalse();
    expect(result.canMoveDown).toBeTrue();

    // Course set 12 (sort order 4) can only move up.
    result = wrapper.vm.canCourseSetMove(4);
    expect(result.canMoveUp).toBeTrue();
    expect(result.canMoveDown).toBeFalse();
  });
});
