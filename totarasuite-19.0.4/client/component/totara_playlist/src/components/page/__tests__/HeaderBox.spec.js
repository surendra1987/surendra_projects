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

import HeaderBox from '../HeaderBox';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import InputText from 'tui/components/form/InputText';
import { mount } from '@vue/test-utils';

describe('HeaderBox', () => {
  let headerBox = null;

  beforeEach(() => {
    headerBox = mount(HeaderBox, {
      props: {
        title: 'Hello world',
        updateAble: false,
        playlistId: 15,
      },
      attachTo: document.body,
    });
  });

  test('the edit button should be rendered but hidden by default', () => {
    let button = headerBox.findComponent(ButtonIcon);
    expect(button.exists()).toBeTrue();
    expect(button.isVisible()).toBeFalse();
  });

  test('the edit button should be visible when prop is changed', async () => {
    await headerBox.setProps({ updateAble: true });
    expect(headerBox.props('updateAble')).toBeTrue();

    let button = headerBox.findComponent(ButtonIcon);

    expect(button.exists()).toBeTrue();
    expect(button.isVisible()).toBeTrue();
  });

  test('the rendering of input element', async () => {
    expect(headerBox.findComponent(InputText).exists()).toBeFalse();
    expect(headerBox.findComponent(ButtonIcon).exists()).toBeTrue();

    await headerBox.setData({ editing: true });

    expect(headerBox.findComponent(ButtonIcon).exists()).toBeFalse();
    expect(headerBox.findComponent(InputText).exists()).toBeTrue();
  });

  test('the auto focus of edit button and input element', async () => {
    // We have to make sure that the update-able to be true, so that the click
    // on button is able to trigger.
    headerBox.setProps({ updateAble: true });
    await headerBox.vm.$nextTick();

    expect(headerBox.vm.editing).toBeFalse();
    let button = headerBox.findComponent(ButtonIcon);
    expect(button.element).not.toBe(document.activeElement);

    button.trigger('click');

    // Editing is now on true.
    expect(headerBox.vm.editing).toBeTrue();
    await headerBox.vm.$nextTick();

    expect(headerBox.findComponent(InputText).exists()).toBeTrue();
    expect(headerBox.findComponent(ButtonIcon).exists()).toBeFalse();

    let input = headerBox.findComponent(InputText);
    expect(input.element).toBe(document.activeElement);

    input.trigger('keydown.esc');

    await headerBox.vm.$nextTick();

    expect(headerBox.findComponent(InputText).exists()).toBeFalse();
    expect(headerBox.findComponent(ButtonIcon).exists()).toBeTrue();

    // Reload the button, as it has been cleared out from the wrapper.
    button = headerBox.findComponent(ButtonIcon);
    expect(button.element).toBe(document.activeElement);
  });

  test('the button is disrepect when not update able', async () => {
    let button = headerBox.findComponent(ButtonIcon);
    expect(headerBox.findComponent(InputText).exists()).toBeFalse();

    button.trigger('click');
    expect(headerBox.findComponent(InputText).exists()).toBeFalse();
  });
});
