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
 * @author Brian Barnes <brian.barnes@totaralearning.com>
 * @module engage_survey
 */

import SurveyCardBody from '../SurveyCardBody';
import { shallowMount } from '@vue/test-utils';

function factory() {
  return shallowMount(SurveyCardBody, {
    props: {
      resourceId: 5,
      name: '',
      access: 'PUBLIC',
      voted: false,
      owned: true,
      editAble: false,
    },
  });
}

describe('Engage SurveyCardBody', () => {
  it('displayName works as expected', async () => {
    const wrapper = factory();
    expect(wrapper.vm.displayName).toBe('');

    let name = 'short name';
    await wrapper.setProps({ name });
    expect(wrapper.vm.displayName).toBe(name);

    await wrapper.setProps({
      voted: true,
    });
    expect(wrapper.vm.displayName).toBe(name);

    name = 'longer name that should be logn enough to trigger a larger value';
    await wrapper.setProps({
      name,
      voted: false,
    });
    expect(wrapper.vm.displayName).toBe(name);

    await wrapper.setProps({
      voted: true,
    });
    expect(wrapper.vm.displayName).toContain(name.substr(0, 30));
    expect(wrapper.vm.displayName).toContain(String.fromCharCode(8230));
    expect(wrapper.vm.displayName).not.toContain(name.substr(35, 10));

    await wrapper.setProps({
      voted: false,
      editAble: true,
    });
    expect(wrapper.vm.displayName).toBe(name);
  });
});
