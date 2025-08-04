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
 * @author Brian Barnes <brian.barnes@totara.com>
 * @module totara_program
 */

import { shallowMount } from '@vue/test-utils';
import AffectedCard from '../AffectedCard';

describe('Card', () => {
  it('Contains the location for the affected string to be changed', async () => {
    const wrapper = await shallowMount(AffectedCard, {
      props: {
        statusKey: 'notduetostartuntil',
      },
    });
    expect(wrapper.find('[data-assignmentstatus]').exists()).toBeTrue();
  });

  it('Displays more info when program is live', async () => {
    const wrapper = await shallowMount(AffectedCard, {
      props: {
        statusKey: 'notduetostartuntil',
      },
    });
    expect(wrapper.vm.moreInfo).toBeFalse();

    wrapper.setProps({
      statusKey: 'nolongeravailabletolearners',
    });
    expect(wrapper.vm.moreInfo).toBeFalse();

    await wrapper.setProps({
      statusKey: 'programlive',
      statistics: {
        total: 5,
        assignments: 3,
        exceptions: 1,
      },
    });
    expect(wrapper.vm.moreInfo).toBeTrue();
  });
});
