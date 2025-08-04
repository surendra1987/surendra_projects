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
 * @author Brian Barnes <brian.barnes@totara.com>
 * @module format_pathway
 */

import ActivityView from 'format_pathway/pages/ActivityView';
import { shallowMount } from '@vue/test-utils';
import { reactive } from 'vue';

describe('ActivityView', () => {
  let mocks = {
    $apollo: {
      loading: false,
    },
  };
  let baseprops = {
    courseId: 8,
    activeCourseModuleId: 53,
    onActivityPage: true,
  };

  it('The available activities computed property works as expected', () => {
    let props = Object.assign({}, baseprops);
    let VueData = {};
    let wrapper = shallowMount(ActivityView, {
      props,
      global: { mocks },
    });

    expect(wrapper.vm.availableActivities).toEqual([]);

    VueData.navigationData = {
      sections: [
        {
          modules: [
            {
              cmid: 53,
              modtype: 'quiz',
              available: true,
              viewurl: 'http://localhost/mod/quiz/view.php?id=53',
              blacklisted: true,
            },
            {
              cmid: 91,
              modtype: 'asign',
              available: true,
              viewurl: 'http://localhost/mod/asign/view.php?id=91',
            },
            {
              cmid: 12,
              modtype: 'scorm',
              available: true,
              viewurl: 'http://localhost/mod/scorm/view.php?id=12',
            },
            {
              cmid: 32,
              modtype: 'facetoface',
              available: true,
              viewurl: 'http://localhost/mod/facetoface/view.php?id=32',
            },
            {
              cmid: 48,
              modtype: 'quiz',
              available: true,
              viewurl: 'http://localhost/mod/quiz/view.php?id=48',
            },
          ],
        },
      ],
    };
    let expected = [
      {
        cmid: 53,
        modtype: 'quiz',
        available: true,
        viewurl: 'http://localhost/mod/quiz/view.php?id=53',
        blacklisted: true,
      },
      {
        cmid: 91,
        modtype: 'asign',
        available: true,
        viewurl: 'http://localhost/mod/asign/view.php?id=91',
      },
      {
        cmid: 12,
        modtype: 'scorm',
        available: true,
        viewurl: 'http://localhost/mod/scorm/view.php?id=12',
      },
      {
        cmid: 32,
        modtype: 'facetoface',
        available: true,
        viewurl: 'http://localhost/mod/facetoface/view.php?id=32',
      },
      {
        cmid: 48,
        modtype: 'quiz',
        available: true,
        viewurl: 'http://localhost/mod/quiz/view.php?id=48',
      },
    ];

    // A simple case
    wrapper = shallowMount(ActivityView, {
      props,
      global: { mocks },
      data() {
        return VueData;
      },
    });

    expect(wrapper.vm.availableActivities).toEqual(expected);
    expect(wrapper.vm.showIncompatibleActivityWarning).toBeTrue();

    VueData.navigationData = {
      sections: [
        {
          modules: [
            {
              cmid: 53,
              modtype: 'quiz',
              available: true,
              viewurl: 'http://localhost/mod/quiz/view.php?id=53',
            },
            {
              cmid: 91,
              modtype: 'asign',
              available: true,
              viewurl: 'http://localhost/mod/asign/view.php?id=91',
            },
            {
              cmid: 12,
              modtype: 'scorm',
              available: true,
              viewurl: 'http://localhost/mod/scorm/view.php?id=12',
            },
          ],
        },
        {
          modules: [
            {
              cmid: 32,
              modtype: 'facetoface',
              available: true,
              viewurl: 'http://localhost/mod/facetoface/view.php?id=32',
            },
            {
              cmid: 48,
              modtype: 'quiz',
              available: true,
              viewurl: 'http://localhost/mod/quiz/view.php?id=48',
            },
          ],
        },
      ],
    };

    // Two sections
    wrapper = shallowMount(ActivityView, {
      props,
      global: { mocks },
      data() {
        return VueData;
      },
    });
    expected = [
      {
        cmid: 53,
        modtype: 'quiz',
        available: true,
        viewurl: 'http://localhost/mod/quiz/view.php?id=53',
      },
      {
        cmid: 91,
        modtype: 'asign',
        available: true,
        viewurl: 'http://localhost/mod/asign/view.php?id=91',
      },
      {
        cmid: 12,
        modtype: 'scorm',
        available: true,
        viewurl: 'http://localhost/mod/scorm/view.php?id=12',
      },
      {
        cmid: 32,
        modtype: 'facetoface',
        available: true,
        viewurl: 'http://localhost/mod/facetoface/view.php?id=32',
      },
      {
        cmid: 48,
        modtype: 'quiz',
        available: true,
        viewurl: 'http://localhost/mod/quiz/view.php?id=48',
      },
    ];

    expect(wrapper.vm.availableActivities).toEqual(expected);

    VueData.navigationData = {
      sections: [
        {
          modules: [
            {
              cmid: 53,
              modtype: 'quiz',
              available: true,
              viewurl: 'http://localhost/mod/quiz/view.php?id=53',
            },
            {
              cmid: 91,
              modtype: 'asign',
              available: true,
              viewurl: 'http://localhost/mod/asign/view.php?id=91',
            },
            {
              cmid: 12,
              modtype: 'scorm',
              available: true,
              viewurl: 'http://localhost/mod/scorm/view.php?id=12',
            },
          ],
        },
        {
          modules: [],
        },
        {
          modules: [
            {
              cmid: 32,
              modtype: 'facetoface',
              available: true,
              viewurl: 'http://localhost/mod/facetoface/view.php?id=32',
            },
            {
              cmid: 48,
              modtype: 'quiz',
              available: true,
              viewurl: 'http://localhost/mod/quiz/view.php?id=48',
            },
          ],
        },
        {
          modules: [],
        },
      ],
    };

    // with some empty sections
    wrapper = shallowMount(ActivityView, {
      props,
      global: { mocks },
      data() {
        return VueData;
      },
    });
    expect(wrapper.vm.availableActivities).toEqual(expected);

    // with some unavailable activities sections

    VueData.navigationData = {
      sections: [
        {
          modules: [
            {
              cmid: 53,
              modtype: 'quiz',
              available: true,
              viewurl: 'http://localhost/mod/quiz/view.php?id=53',
            },
            {
              cmid: 91,
              modtype: 'asign',
              available: true,
              viewurl: 'http://localhost/mod/asign/view.php?id=91',
            },
            {
              cmid: 108,
              modtype: 'asign',
              available: false,
              viewurl: 'http://localhost/mod/asign/view.php?id=108',
            },
            {
              cmid: 12,
              modtype: 'scorm',
              available: true,
              viewurl: 'http://localhost/mod/scorm/view.php?id=12',
            },
          ],
        },
        {
          modules: [],
        },
        {
          modules: [
            {
              cmid: 32,
              modtype: 'facetoface',
              available: true,
              viewurl: 'http://localhost/mod/facetoface/view.php?id=32',
            },
            {
              cmid: 48,
              modtype: 'quiz',
              available: true,
              viewurl: 'http://localhost/mod/quiz/view.php?id=48',
            },
          ],
        },
        {
          modules: [
            {
              cmid: 94,
              modtype: 'quiz',
              available: false,
              viewurl: 'http://localhost/mod/quiz/view.php?id=94',
            },
          ],
        },
      ],
    };

    wrapper = shallowMount(ActivityView, {
      props,
      global: { mocks },
      data() {
        return VueData;
      },
    });
    expect(wrapper.vm.availableActivities).toEqual(expected);
  });

  it('Next and previous activities', async () => {
    let props = Object.assign({}, baseprops, {
      activeCourseModuleId: 5,
    });
    let vueData = reactive({
      navigationData: {
        sections: [
          {
            modules: [
              {
                cmid: 5,
                modtype: 'quiz',
                available: true,
                blacklisted: false,
                viewurl: 'http://localhost/mod/quiz/view.php?id=5',
              },
            ],
          },
        ],
      },
      courseInteractor: {},
    });
    let wrapper = shallowMount(ActivityView, {
      props,
      global: { mocks },
      data() {
        return vueData;
      },
    });

    // Only one activity in the course
    expect(wrapper.vm.nextActivityUrl).toBeNull();
    expect(wrapper.vm.previousActivityUrl).toBeNull();
    expect(wrapper.vm.showIncompatibleActivityWarning).toBeFalse();

    // Normalish cases
    vueData.navigationData.sections = [
      {
        modules: [
          {
            cmid: 95,
            modtype: 'assign',
            available: true,
            viewurl: 'http://localhost/mod/assign/view.php?id=95',
          },
          {
            cmid: 1,
            modtype: 'quiz',
            available: true,
            viewurl: 'http://localhost/mod/quiz/view.php?id=1',
          },
          {
            cmid: 5,
            modtype: 'quiz',
            available: true,
            viewurl: 'http://localhost/mod/quiz/view.php?id=5',
          },
          {
            cmid: 12,
            modtype: 'facetoface',
            available: true,
            viewurl: 'http://localhost/mod/facetoface/view.php?id=12',
          },
        ],
      },
    ];

    wrapper = shallowMount(ActivityView, {
      props,
      global: { mocks },
      data() {
        return vueData;
      },
    });
    expect(wrapper.vm.previousActivityUrl).toBe(
      'http://localhost/mod/quiz/view.php?id=1'
    );
    expect(wrapper.vm.nextActivityUrl).toBe(
      'http://localhost/mod/facetoface/view.php?id=12'
    );
    vueData.navigationData.sections = [
      {
        modules: [
          {
            cmid: 1,
            modtype: 'quiz',
            available: true,
            viewurl: 'http://localhost/mod/quiz/view.php?id=1',
          },
          {
            cmid: 5,
            modtype: 'quiz',
            available: true,
            viewurl: 'http://localhost/mod/quiz/view.php?id=5',
          },
        ],
      },
      {
        modules: [
          {
            cmid: 12,
            modtype: 'facetoface',
            available: true,
            viewurl: 'http://localhost/mod/facetoface/view.php?id=12',
          },
        ],
      },
    ];

    wrapper = shallowMount(ActivityView, {
      props,
      global: { mocks },
      data() {
        return vueData;
      },
    });
    expect(wrapper.vm.previousActivityUrl).toBe(
      'http://localhost/mod/quiz/view.php?id=1'
    );
    expect(wrapper.vm.nextActivityUrl).toBe(
      'http://localhost/mod/facetoface/view.php?id=12'
    );

    // Unavailable modules
    vueData.navigationData.sections = [
      {
        modules: [
          {
            cmid: 1,
            modtype: 'quiz',
            available: true,
            viewurl: 'http://localhost/mod/quiz/view.php?id=1',
          },
          {
            cmid: 53,
            modtype: 'facetoface',
            available: true,
            viewurl: 'http://localhost/mod/facetoface/view.php?id=53',
          },
          {
            cmid: 18,
            modtype: 'choice',
            available: true,
            viewurl: 'http://localhost/mod/choice/view.php?id=18',
          },
          {
            cmid: 52,
            modtype: 'assign',
            available: false,
            viewurl: 'http://localhost/mod/assign/view.php?id=52',
          },
          {
            cmid: 5,
            modtype: 'hvp',
            available: true,
            viewurl: 'http://localhost/mod/hvp/view.php?id=5',
          },
        ],
      },
      {
        modules: [
          {
            cmid: 12,
            modtype: 'facetoface',
            available: false,
            viewurl: 'http://localhost/mod/facetoface/view.php?id=12',
          },
          {
            cmid: 76,
            modtype: 'quiz',
            available: false,
            viewurl: 'http://localhost/mod/quiz/view.php?id=76',
          },
          {
            cmid: 2,
            modtype: 'lesson',
            available: true,
            viewurl: 'http://localhost/mod/lesson/view.php?id=2',
          },
          {
            cmid: 345,
            modtype: 'lti',
            available: true,
            viewurl: 'http://localhost/mod/lti/view.php?id=345',
          },
          {
            cmid: 9,
            modtype: 'beh',
            available: true,
            viewurl: 'http://localhost/mod/beh/view.php?id=9',
          },
        ],
      },
      {
        modules: [
          {
            cmid: 91,
            modtype: 'facetoface',
            available: true,
            viewurl: 'http://localhost/mod/facetoface/view.php?id=91',
          },
          {
            cmid: 6,
            modtype: 'quiz',
            available: true,
            viewurl: 'http://localhost/mod/quiz/view.php?id=6',
          },
          {
            cmid: 12,
            modtype: 'quiz',
            available: true,
            viewurl: 'http://localhost/mod/quiz/view.php?id=12',
          },
        ],
      },
    ];

    wrapper = shallowMount(ActivityView, {
      props,
      global: { mocks },
      data() {
        return vueData;
      },
    });
    expect(wrapper.vm.previousActivityUrl).toBe(
      'http://localhost/mod/choice/view.php?id=18'
    );
    expect(wrapper.vm.nextActivityUrl).toBe(
      'http://localhost/mod/lesson/view.php?id=2'
    );

    // admin is on a restricted activity
    vueData.navigationData.sections = [
      {
        modules: [
          {
            cmid: 91,
            modtype: 'facetoface',
            available: true,
            viewurl: 'http://localhost/mod/facetoface/view.php?id=91',
          },
          {
            cmid: 5,
            modtype: 'quiz',
            available: false,
            viewurl: 'http://localhost/mod/quiz/view.php?id=5',
          },
          {
            cmid: 12,
            modtype: 'quiz',
            available: true,
            viewurl: 'http://localhost/mod/quiz/view.php?id=12',
          },
        ],
      },
    ];
    expect(wrapper.vm.previousActivityUrl).toBeNull();
    expect(wrapper.vm.nextActivityUrl).toBeNull();

    Object.assign(vueData.courseInteractor, {
      is_enrolled: true,
    });

    expect(wrapper.vm.showGuestEnrolmentBanner).toBeFalse();
    expect(wrapper.vm.getGuestEnrolmentBannerMessage).toBeEmpty();

    Object.assign(vueData.courseInteractor, {
      is_enrolled: false,
      guest_enrol_enabled: true,
      is_site_guest: true,
    });
    expect(wrapper.vm.showGuestEnrolmentBanner).toBeTrue();
    expect(wrapper.vm.getGuestEnrolmentBannerMessage).toEqual(
      '[[view_course_as_guest, container_course]]'
    );
  });
});
