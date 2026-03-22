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
 * @author Kevin Hottinger <kevin.hottinger@totaralearning.com>
 * @module tui
 */

import { shallowMount } from '@vue/test-utils';
import FileCard from '../FileCard';

describe('FilterSidePanel', () => {
  it('fileExtension is working correctly', async () => {
    const wrapper = shallowMount(FileCard, {
      props: {
        fileSize: 124,
        filename: 'something.png',
      },
    });

    expect(wrapper.vm.fileExtension).toBe(
      '[[file_extension, totara_core, "png"]]'
    );

    await wrapper.setProps({ filename: 'something' });
    expect(wrapper.vm.fileExtension).toBeNull();

    await wrapper.setProps({ filename: '' });
    expect(wrapper.vm.fileExtension).toBeNull();

    await wrapper.setProps({ filename: '.png' });
    expect(wrapper.vm.fileExtension).toBe(
      '[[file_extension, totara_core, "png"]]'
    );

    await wrapper.setProps({ filename: 'something.spec.js' });
    expect(wrapper.vm.fileExtension).toBe(
      '[[file_extension, totara_core, "js"]]'
    );
  });
});
