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
 * @author Simon Chester <simon.chester@totaralearning.com>
 * @module tui
 */

import { h } from 'vue';
import ButtonAria from '../ButtonAria';
import { fireEvent, render, screen } from 'tui_test_utils/vtl';
import { axe } from 'jest-axe';

// Simple button implementation for testing
const SimpleButton = {
  props: ['disabled'],
  render() {
    return h(ButtonAria, () => [
      h(
        'div',
        {
          disabled: this.disabled,
          onClick: e => this.$emit('click', e),
        },
        ['foo']
      ),
    ]);
  },
};

describe('ButtonAria', () => {
  it('has accessible attributes', async () => {
    render(SimpleButton);
    const button = screen.getByRole('button', { name: 'foo' });
    expect(button.tabIndex).toBe(0);
    expect(button.getAttribute('disabled')).toBeNull();
    expect(button.getAttribute('aria-disabled')).toBeNull();
  });

  it('emits click event on button click', async () => {
    const click = jest.fn();
    const view = render(SimpleButton, {
      props: { onClick: click },
    });
    const button = view.getByRole('button');

    await fireEvent.click(button);
    expect(click).toHaveBeenCalled();
    click.mockClear();

    await fireEvent.keyDown(button, {
      key: 'Enter',
      code: 'Enter',
      keyCode: 13,
    });
    expect(click).toHaveBeenCalled();
    click.mockClear();

    await fireEvent.keyUp(button, { key: ' ', code: 'Space', keyCode: 32 });
    expect(click).toHaveBeenCalled();
    click.mockClear();
  });

  it('behaves appropriately when button is disabled', async () => {
    const click = jest.fn();
    render(SimpleButton, {
      props: {
        disabled: true,
        onClick: click,
      },
    });
    const button = screen.getByRole('button', { name: 'foo' });
    await fireEvent.click(button);
    expect(click).not.toHaveBeenCalled();
    expect(button.getAttribute('disabled')).toBeTruthy();
    expect(button.getAttribute('aria-disabled')).toBe('true');
  });

  it('should not have any accessibility violations', async () => {
    const view = render(SimpleButton);
    const results = await axe(view.container);
    expect(results).toHaveNoViolations();
  });
});
