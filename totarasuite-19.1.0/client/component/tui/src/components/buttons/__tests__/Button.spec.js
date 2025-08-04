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
 * @author Simon Chester <simon.chester@totaralearning.com>
 * @module tui
 */

import { fireEvent, render, screen } from 'tui_test_utils/vtl';
import Button from '../Button';
import { axe } from 'jest-axe';
import { shallowMount } from '@vue/test-utils';

describe('Button', () => {
  it('emits click event on button click', async () => {
    const click = jest.fn();
    render(Button, {
      props: {
        text: 'text',
        onClick: click,
      },
    });
    await fireEvent.click(screen.getByRole('button'));
    expect(click).toHaveBeenCalled();
  });

  it('does not emit click event when button is disabled or loading', async () => {
    const click = jest.fn();
    render(Button, {
      props: {
        text: 'text',
        loading: true,
        onClick: click,
      },
    });
    await fireEvent.click(screen.getByRole('button'));
    expect(click).not.toHaveBeenCalled();
  });

  it('should not have any accessibility violations', async () => {
    const view = render(Button, {
      props: { text: 'text' },
    });
    const results = await axe(view.container);
    expect(results).toHaveNoViolations();
  });

  it('should support autofocus', () => {
    const wrapper = shallowMount(Button, {
      props: {
        text: 'btn text',
        autofocus: true,
      },
      attachTo: document.body,
    });
    expect(wrapper.element).toBe(document.activeElement);
    wrapper.element.remove();
  });

  it.each([
    [null, null],
    [true, 'true'],
    ['true', 'true'],
    [false, 'false'],
    ['false', 'false'],
  ])(
    'should set aria-disabled appropriately when passed %p',
    (setTo, expectedAttrValue) => {
      render(Button, {
        props: {
          text: 'text',
          ariaDisabled: setTo,
        },
      });
      const attrValue = screen
        .getByRole('button')
        .getAttribute('aria-disabled');
      expect(attrValue).toBe(expectedAttrValue);
    }
  );
});
