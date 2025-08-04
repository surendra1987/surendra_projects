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

import { mount, shallowMount } from '@vue/test-utils';
import CourseSetForm from 'totara_program/components/manage_program/content/CourseSetForm';
import { Uniform } from 'tui/components/uniform';

// Mock out the charLength property as otherwise it supports 'full'
jest.mock('tui/components/form/form_common', () => {
  return {
    charLengthProp: {
      type: [String, Number],
      validator: () => true,
    },
  };
});

describe('CourseSetForm', () => {
  let wrapper;

  let customFields = [
    { id: '1', name: 'Monkey 1' },
    { id: '2', name: 'Monkey 2' },
    { id: '3', name: 'Monkey 3' },
    { id: '4', name: 'Monkey 4' },
    { id: '5', name: 'Monkey 5' },
    { id: '6', name: 'Monkey 6' },
  ];

  it('Minimum time round trip works', async () => {
    let formData = {
      label: 'A new course set',
      completiontype: '5',
      mincourses: 0,
      coursesumfield: '0',
      coursesumfieldtotal: 0,
      timeallowed: 0,
    };

    let expectedData = {
      label: 'A new course set',
      completion_type: '5',
      min_courses: 0,
      course_sum_field: '0',
      course_sum_field_total: 0,
      time_allowed: 0,
      courses: [],
    };

    wrapper = mount(CourseSetForm, {
      props: {
        formData,
        customFields,
        serverCourses: [],
      },
      global: { stubs: ['CourseAdder', 'passthrough'] },
    });

    await wrapper.getComponent(Uniform).vm.submit();
    expect(wrapper.emitted('submit')[0][0]).toEqual(expectedData);
    let periodElement = wrapper.get('[name="period"]');
    expect(periodElement.element.value).toBe('6');

    // Confirm days come back as days
    formData.timeallowed = 3600 * 24 * 3;
    expectedData.time_allowed = formData.timeallowed;

    wrapper = mount(CourseSetForm, {
      props: {
        formData,
        customFields,
        serverCourses: [],
      },
      global: { stubs: ['CourseAdder', 'passthrough'] },
    });
    await wrapper.getComponent(Uniform).vm.submit();
    expect(wrapper.emitted('submit')[0][0]).toEqual(expectedData);
    periodElement = wrapper.get('[name="period"]');
    expect(periodElement.element.value).toBe('2');

    // Confirm weeks come back as weeks
    formData.timeallowed = 3600 * 24 * 21;
    expectedData.time_allowed = formData.timeallowed;

    wrapper = mount(CourseSetForm, {
      props: {
        formData,
        customFields,
        serverCourses: [],
      },
      global: { stubs: ['CourseAdder', 'passthrough'] },
    });
    await wrapper.getComponent(Uniform).vm.submit();
    expect(wrapper.emitted('submit')[0][0]).toEqual(expectedData);
    periodElement = wrapper.get('[name="period"]');
    expect(periodElement.element.value).toBe('3');

    // Confirm months come back as months
    formData.timeallowed = 3600 * 24 * 60;
    expectedData.time_allowed = formData.timeallowed;

    wrapper = mount(CourseSetForm, {
      props: {
        formData,
        customFields,
        serverCourses: [],
      },
      global: { stubs: ['CourseAdder', 'passthrough'] },
    });
    await wrapper.getComponent(Uniform).vm.submit();
    expect(wrapper.emitted('submit')[0][0]).toEqual(expectedData);
    periodElement = wrapper.get('[name="period"]');
    expect(periodElement.element.value).toBe('4');

    // Confirm years come back as years
    formData.timeallowed = 3600 * 24 * 730;
    expectedData.time_allowed = formData.timeallowed;

    wrapper = mount(CourseSetForm, {
      props: {
        formData,
        customFields,
        serverCourses: [],
      },
      global: { stubs: ['CourseAdder', 'passthrough'] },
    });
    await wrapper.getComponent(Uniform).vm.submit();
    expect(wrapper.emitted('submit')[0][0]).toEqual(expectedData);
    periodElement = wrapper.get('[name="period"]');
    expect(periodElement.element.value).toBe('5');
  });

  it('Changing the period dropdown works as expected', async () => {
    const DAYSECS = 24 * 3600;

    let formData = {
      label: 'A new course set',
      completiontype: '5',
      mincourses: 0,
      coursesumfield: '0',
      coursesumfieldtotal: 0,
      timeallowed: 0,
    };

    let expectedData = {
      label: 'A new course set',
      completion_type: '5',
      min_courses: 0,
      course_sum_field: '0',
      course_sum_field_total: 0,
      time_allowed: 0,
      courses: [],
    };

    wrapper = mount(CourseSetForm, {
      props: {
        formData,
        customFields,
        serverCourses: [],
      },
      global: { stubs: ['CourseAdder', 'passthrough'] },
    });

    await wrapper.getComponent(Uniform).vm.submit();
    expect(wrapper.emitted('submit')[0][0]).toEqual(expectedData);
    let periodElement = wrapper.get('[name="period"]');
    let amountElement = wrapper.get('[name="amount"]');
    expect(periodElement.element.value).toBe('6');

    // Checking days
    await periodElement.setValue('2');
    await amountElement.setValue('2');
    await wrapper.getComponent(Uniform).vm.submit();
    expect(wrapper.emitted('submit')[1][0].time_allowed).toEqual(2 * DAYSECS);

    // Checking weeks
    await periodElement.setValue('3');
    await amountElement.setValue('1');
    await wrapper.getComponent(Uniform).vm.submit();
    expect(wrapper.emitted('submit')[2][0].time_allowed).toEqual(7 * DAYSECS);

    // Checking months
    await periodElement.setValue('4');
    await amountElement.setValue('3');
    await wrapper.getComponent(Uniform).vm.submit();
    expect(wrapper.emitted('submit')[3][0].time_allowed).toEqual(90 * DAYSECS);

    // Checking years
    await periodElement.setValue('5');
    await amountElement.setValue('2');
    await wrapper.getComponent(Uniform).vm.submit();
    expect(wrapper.emitted('submit')[4][0].time_allowed).toEqual(730 * DAYSECS);
  });

  it('Adding courses to the course set works', () => {
    let formData = {
      label: 'A new course set',
      completiontype: '5',
      mincourses: 2,
      coursesumfield: '0',
      coursesumfieldtotal: 0,
      timeallowed: 0,
    };

    wrapper = shallowMount(CourseSetForm, {
      props: {
        formData,
        customFields,
        serverCourses: [],
      },
      global: { stubs: ['passthrough'] },
    });
    let course = {
      id: 5,
      category: { full_path: 'a/b/c' },
      fullname: 'some course',
      image: 'blah.jpg',
    };
    let expected = {
      id: course.id,
      fullname: course.fullname,
      image: course.image,
    };

    let course2 = {
      id: 95,
      category: { full_path: 'a/b/c' },
      fullname: 'a second course',
      image: 'blahblah.jpg',
    };
    let expected2 = {
      id: course2.id,
      fullname: course2.fullname,
      image: course2.image,
    };

    wrapper.vm.addCourses([course]);
    expect(wrapper.vm.courses.length).toBe(1);
    expect(wrapper.vm.courses[0]).toEqual(expected);

    wrapper.vm.addCourses([course, course2]);
    expect(wrapper.vm.courses.length).toBe(2);
    expect(wrapper.vm.courses[0]).toEqual(expected);
    expect(wrapper.vm.courses[1]).toEqual(expected2);
  });

  it('Ordering of courses is correct', () => {
    let formData = {
      label: 'A new course set',
      completiontype: '5',
      mincourses: 2,
      coursesumfield: '0',
      coursesumfieldtotal: 0,
      timeallowed: 0,
    };
    const c1 = { id: 1, sortorder: '3' };
    const c2 = { id: 2, sortorder: '2' };
    const c3 = { id: 3, sortorder: '1' };
    const c4 = { id: 4, sortorder: '4' };

    let wrapper = shallowMount(CourseSetForm, {
      props: {
        formData,
        customFields,
        serverCourses: [c1, c2, c3, c4],
      },
    });

    expect(wrapper.vm.courses).toEqual([c3, c2, c1, c4]);
  });

  it('moving works as expected', async () => {
    let formData = {
      label: 'A new course set',
      completiontype: '5',
      mincourses: 2,
      coursesumfield: '0',
      coursesumfieldtotal: 0,
      timeallowed: 0,
    };
    const c1 = { id: 1, sortorder: '3' };
    const c2 = { id: 2, sortorder: '2' };
    const c3 = { id: 3, sortorder: '1' };
    const c4 = { id: 4, sortorder: '4' };

    let wrapper = shallowMount(CourseSetForm, {
      props: {
        formData,
        customFields,
        serverCourses: [c1, c2, c3, c4],
      },
    });

    expect(wrapper.vm.courses).toEqual([c3, c2, c1, c4]);
    wrapper.vm.moveCourse(c1, 1);
    expect(wrapper.vm.courses).toEqual([c3, c1, c2, c4]);

    wrapper.vm.moveCourse(c1, 3);
    expect(wrapper.vm.courses).toEqual([c3, c2, c4, c1]);

    let expectedData = [
      { id: 3, sortorder: '0' },
      { id: 2, sortorder: '1' },
      { id: 4, sortorder: '2' },
      { id: 1, sortorder: '3' },
    ];
    await wrapper.vm.submit({ amount: '5', period: '6' });
    expect(wrapper.emitted('submit')[0][0].courses).toEqual(expectedData);
  });

  it('remove course as expected', async () => {
    let formData = {
      label: 'A new course set',
      completiontype: '5',
      mincourses: 2,
      coursesumfield: '0',
      coursesumfieldtotal: 0,
      timeallowed: 0,
    };
    const c1 = { id: 1, sortorder: '3' };
    const c2 = { id: 2, sortorder: '2' };
    const c3 = { id: 3, sortorder: '1' };
    const c4 = { id: 4, sortorder: '4' };

    let wrapper = shallowMount(CourseSetForm, {
      props: {
        formData,
        customFields,
        serverCourses: [c1, c2, c3, c4],
      },
    });

    expect(wrapper.vm.courses).toEqual([c3, c2, c1, c4]);
    wrapper.vm.removeCourse(1);
    expect(wrapper.vm.courses).toEqual([c3, c2, c4]);
  });
});
