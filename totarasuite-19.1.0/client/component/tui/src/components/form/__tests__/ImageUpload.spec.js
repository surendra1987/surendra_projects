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
 * @author Brian Barnes <brian.barnes@totaralearning.com>
 * @module tui
 */

import { nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import ImageUpload from '../ImageUpload';

function factory() {
  return mount(ImageUpload, {
    props: {
      href: 'www.example.com',
      itemId: 123,
      repositoryId: 543,
    },
  });
}

describe('ImageUpload', () => {
  it('Error functionality loads as expected', async () => {
    const wrapper = factory();
    const error = 'you fail';

    wrapper.vm.handleError({ error: error });
    expect(wrapper.vm.errorMessage).toBe(error);
    expect(wrapper.vm.isError).toBeTrue();
    await nextTick();

    wrapper.vm.handleFileLoaded({ file: { url: 'www.example.com' } });
    expect(wrapper.vm.errorMessage).toBe('');
    expect(wrapper.vm.isError).toBeFalse();
  });

  it('File errors have valid aria attributes', async () => {
    const wrapper = factory();
    const error = 'Bad file';

    // neither ariaDescribedBy nor error state
    expect(wrapper.vm.ariaDescribedbyId).toBe('');

    // the ID is aet
    await wrapper.setProps({ ariaDescribedby: 'ariaid' });
    await nextTick();
    expect(
      wrapper
        .find('[aria-describedby="' + wrapper.vm.ariaDescribedbyId + '"]')
        .exists()
    ).toBeTrue();

    // both ariaid & there is an error
    wrapper.vm.handleError({ error: error });
    await nextTick();

    expect(
      wrapper
        .find('[aria-describedby="' + wrapper.vm.ariaDescribedbyId + '"]')
        .exists()
    ).toBeTrue();
    expect(wrapper.find('#' + wrapper.vm.errorId).exists()).toBeTrue();

    await wrapper.setProps({ ariaDescribedby: undefined });
    expect(wrapper.vm.ariaDescribedbyId).toEqual(wrapper.vm.errorId);
    await nextTick();
    expect(
      wrapper.find('[aria-describedby="' + wrapper.vm.errorId + '"]').exists()
    ).toBeTrue();
    expect(wrapper.find('#' + wrapper.vm.errorId).exists()).toBeTrue();
  });
});
