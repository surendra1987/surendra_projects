/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Steve Barnett <steve.barnett@totaralearning.com>
 * @module totara_notification
 */

import NotificationPreferenceForm from '../NotificationPreferenceForm';
import { shallowMount } from '@vue/test-utils';
import {
  SCHEDULE_TYPES,
  NOTIFICATION_PREFERENCE_KEYS,
} from '../../../internal/notification_preference';

let wrapper;

const props = {
  resolverClassName: 'eventClassName',
  contextId: 1,
  validScheduleTypes: [SCHEDULE_TYPES.ON_EVENT],
  availableRecipients: [
    {
      class_name: 'test_class',
      name: 'test',
    },
  ],
  defaultDeliveryChannels: [
    {
      component: 'email',
      label: 'Email',
      is_enabled: true,
      is_sub_delivery_channel: true,
      parent_component: 'popup',
    },
  ],
};

describe('NotificationPreferenceForm', () => {
  beforeEach(() => {
    wrapper = shallowMount(NotificationPreferenceForm, {
      props: props,
    });
  });

  it('should validateBodyEditor with no requiredBody', async () => {
    // set parentValue body so that requiredBody is false
    await wrapper.setProps({
      parentValue: {
        ...Object.fromEntries(NOTIFICATION_PREFERENCE_KEYS.map(x => [x, null])),
        body: '{"type":"doc","content":[]}',
      },
    });

    const result = wrapper.vm.validateBodyEditor();
    expect(result).toEqual('');
  });

  it('should validateBodyEditor with requiredBody and no content', () => {
    const result = wrapper.vm.validateBodyEditor();

    expect(result).toEqual('[[required, core]]');
  });

  it('should validateBodyEditor with requiredBody and content', () => {
    const result = wrapper.vm.validateBodyEditor('content');

    expect(result).toEqual('');
  });
});
