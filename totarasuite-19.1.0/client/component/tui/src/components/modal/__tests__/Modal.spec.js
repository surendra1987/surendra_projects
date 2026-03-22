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
 * @author Simon Chester <simon.chester@totaralearning.com>
 * @module tui
 */

import { h } from 'vue';
import Modal from '../Modal';
import { fireEvent, render } from 'tui_test_utils/vtl';
import { axe } from 'jest-axe';

const ModalWrap = {
  inheritAttrs: false,
  render() {
    return h(
      'div',
      h(
        Modal,
        {
          ...this.$attrs,
          onClose: () => this.$emit('close'),
        },
        this.$slots
      )
    );
  },
};

function renderModal() {
  return render(ModalWrap, {
    slots: {
      default: ['content'],
      buttons: ['buttons'],
    },
    props: {
      title: 'Title',
    },
  });
}

describe('Modal', () => {
  it('adds modal to body', async () => {
    const view = renderModal();
    expect(document.body.querySelector('[role=dialog]')).toBeNull();
    await view.rerender({ open: true });
    expect(document.body.querySelector('[role=dialog]')).not.toBeNull();
    view.unmount();
  });

  it('should not have any accessibility violations', async () => {
    const view = renderModal();
    const results = await axe(view.container, {
      rules: {
        region: { enabled: false },
      },
    });
    expect(results).toHaveNoViolations();
    view.unmount();
  });

  it('is dismissed by the escape key', async () => {
    const view = renderModal();
    await view.rerender({ open: true });
    await fireEvent.keyDown(document, { key: 'Escape' });
    expect(view.emitted('close')).toHaveLength(1);
    view.unmount();
  });
});
