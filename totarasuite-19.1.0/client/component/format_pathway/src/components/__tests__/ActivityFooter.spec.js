/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
import ActivityFooter from '../ActivityFooter';

import { Completion } from 'format_pathway/activityCompletion';

describe('ActivityFooter', () => {
  it('Complete button should only show when course completion is enabled, self completion is enabled, the user is enroled and the activity is incomplete', async () => {
    const wrapper = await shallowMount(ActivityFooter, {
      props: {
        activeActivityCompletion: {
          activitycompletiontracking: Completion.MANUAL,
          completionstatus: false,
        },
        courseCompletionData: {
          completion: { statuskey: 'notyetstarted' },
          completionenabled: true,
        },
        onActivityPage: true,
        courseInteractor: {
          is_enrolled: true,
          is_enrolled_not_pending_approval: true,
        },
      },
    });
    expect(wrapper.vm.showCompleteButton).toBeTrue();

    await wrapper.setProps({
      activeActivityCompletion: {
        activitycompletiontracking: Completion.AUTOMATIC,
        completionstatus: false,
      },
      courseCompletionData: {
        completion: { statuskey: 'notyetstarted' },
        completionenabled: true,
      },
    });
    expect(wrapper.vm.showCompleteButton).toBeFalse();

    await wrapper.setProps({
      activeActivityCompletion: {
        activitycompletiontracking: Completion.MANUAL,
        completionstatus: true,
      },
      courseCompletionData: {
        completion: { statuskey: 'notyetstarted' },
        completionenabled: true,
      },
    });
    expect(wrapper.vm.showCompleteButton).toBeFalse();

    // When the user is not enrolled, the button will not show.
    await wrapper.setProps({
      activeActivityCompletion: {
        activitycompletiontracking: Completion.MANUAL,
        completionstatus: false,
      },
      courseCompletionData: {
        completion: { statuskey: '' },
        completionenabled: true,
      },
      courseInteractor: {
        is_enrolled: false,
      },
    });
    expect(wrapper.vm.showCompleteButton).toBeFalse();

    await wrapper.setProps({
      activeActivityCompletion: {
        activitycompletiontracking: Completion.MANUAL,
        completionstatus: false,
      },
      courseCompletionData: {
        completion: { statuskey: 'notyetstarted' },
        completionenabled: false,
      },
    });
    expect(wrapper.vm.showCompleteButton).toBeFalse();
  });
});
