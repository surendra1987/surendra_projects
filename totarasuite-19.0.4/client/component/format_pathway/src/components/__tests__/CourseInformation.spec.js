/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Jack Humphrey <jack.humphrey@totara.com>
 * @module format_pathway
 */

import { shallowMount } from '@vue/test-utils';
import CourseInformation from '../CourseInformation';

describe('CourseInformation', () => {
  it('Enrol button should only show on Activity pages and when self enrolment is enabled', async () => {
    const wrapper = await shallowMount(CourseInformation, {
      props: {
        courseData: {},
        courseId: 1,
        courseInteractor: {
          can_enrol: true,
          is_site_guest: false,
          non_interactive_enrol_instance_enabled: true,
        },
        hasActiveActivity: false,
      },
      global: {
        mocks: {
          $apollo: { loading: false },
        },
      },
    });
    expect(wrapper.vm.displayEnrolButton).toBeFalse();

    await wrapper.setProps({
      courseInteractor: {
        can_enrol: false,
        is_site_guest: false,
        non_interactive_enrol_instance_enabled: true,
      },
      hasActiveActivity: true,
    });
    expect(wrapper.vm.displayEnrolButton).toBeFalse();

    await wrapper.setProps({
      courseInteractor: {
        can_enrol: true,
        is_site_guest: false,
        non_interactive_enrol_instance_enabled: true,
      },
    });
    expect(wrapper.vm.displayEnrolButton).toBeTrue();
  });

  it('Enrol button should display appropriate text', async () => {
    const wrapper = await shallowMount(CourseInformation, {
      props: {
        courseData: {},
        courseId: 1,
        courseInteractor: {
          can_enrol: true,
          is_site_guest: false,
          non_interactive_enrol_instance_enabled: true,
          is_enrolled_not_pending_approval: true,
        },
        hasActiveActivity: true,
      },
      global: {
        mocks: {
          $apollo: { loading: false },
        },
      },
    });
    expect(wrapper.vm.displayEnrolText).toBe('[[enrol, core_enrol]]');

    await wrapper.setProps({
      courseInteractor: {
        can_enrol: true,
        is_site_guest: false,
        non_interactive_enrol_instance_enabled: true,
        is_enrolled_not_pending_approval: false,
      },
    });
    expect(wrapper.vm.displayEnrolText).toBe(
      '[[enrolmentoptions, core_enrol]]'
    );

    await wrapper.setProps({
      courseInteractor: {
        can_enrol: true,
        is_site_guest: false,
        non_interactive_enrol_instance_enabled: true,
        non_interactive_enrol_requires_approval: true,
      },
    });
    await wrapper.setData({
      nonInteractiveEnrolPendingApprovalInfo: {
        button_name: 'Complete request',
        needs_create_new_application: false,
        pending: true,
        redirect_url: 'https://www.totara.com/',
      },
    });
    expect(wrapper.vm.displayEnrolText).toBe('Complete request');

    await wrapper.setData({
      nonInteractiveEnrolPendingApprovalInfo: {
        button_name: 'Complete request',
        needs_create_new_application: false,
        pending: false,
        redirect_url: 'https://www.totara.com/',
      },
    });
    expect(wrapper.vm.displayEnrolText).toBe('[[requestapproval, enrol_self]]');
  });
});
