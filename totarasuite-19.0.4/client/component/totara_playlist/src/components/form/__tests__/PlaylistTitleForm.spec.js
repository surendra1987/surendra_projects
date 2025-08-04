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
 * @author Kian Nguyen <kian.nguyen@totaralearning.com>
 * @module totara_playlist
 */

import PlaylistTitleForm from '../PlaylistTitleForm';
import InputText from 'tui/components/form/InputText';
import { mount } from '@vue/test-utils';

function factory() {
  return mount(PlaylistTitleForm, {
    attachTo: document.body,
    props: {
      title: 'Playlist Form',
      focusInput: true,
    },
  });
}

describe('PlaylistTitleForm', () => {
  it('checks the aria label of the input field', () => {
    const form = factory();
    let input = form.findComponent(InputText);
    expect(input.exists()).toBeTrue();
    expect(input.attributes('aria-label')).toBe(
      '[[playlisttitle, totara_playlist]]'
    );
  });

  it('should has the input focus', () => {
    const form = factory();
    let input = form.findComponent(InputText);
    expect(input.exists()).toBeTrue();
    expect(input.element).toBe(document.activeElement);
  });

  it('checks the escape key event', () => {
    const form = factory();
    let input = form.findComponent(InputText);
    expect(form.emitted('cancel')).toBeUndefined();

    input.trigger('keydown.esc');
    expect(form.emitted('cancel')).not.toBeUndefined();
  });
});
