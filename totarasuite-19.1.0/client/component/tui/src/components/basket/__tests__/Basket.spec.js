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

import Basket from '../Basket';
import { flushMicrotasks } from 'tui_test_utils';
import { render, fireEvent } from 'tui_test_utils/vtl';
import { axe } from 'jest-axe';
import { h } from 'vue';

describe('Basket', () => {
  it('disables actions when no items are passed', async () => {
    const view = render(Basket, {
      props: {
        items: [1, 2, 3],
        bulkActions: [
          { label: 'Action', action: () => {} },
          { label: 'Action', action: () => {} },
        ],
      },
      slots: {
        actions({ empty }) {
          return !empty && h('div', 'test action');
        },
      },
    });

    expect(view.getByText(/3/i)).toBeInTheDocument();
    expect(view.getByText(/test action/i)).toBeInTheDocument();
    expect(
      view.getByRole('button', { name: '[[bulkactions, core]]' })
    ).not.toBeDisabled();

    await view.rerender({ items: [] });

    expect(view.getByText(/0/i)).toBeInTheDocument();
    expect(view.queryByText(/test action/i)).not.toBeInTheDocument();
    expect(
      view.getByRole('button', { name: '[[bulkactions, core]]' })
    ).toBeDisabled();
  });

  it('calls action when single bulk action clicked', async () => {
    const action = jest.fn();

    const view = render(Basket, {
      props: {
        items: [1, 2, 3],
        bulkActions: [{ label: 'Action', action: action }],
      },
    });

    expect(action).not.toHaveBeenCalled();
    await fireEvent.click(view.getByRole('button', { name: 'Action' }));
    expect(action).toHaveBeenCalledTimes(1);
  });

  it('calls action when multiple bulk action clicked', async () => {
    const action = jest.fn();

    const view = render(Basket, {
      props: {
        items: [1, 2, 3],
        bulkActions: [
          { label: 'Action', action: action },
          { label: 'Other', action: () => {} },
        ],
      },
    });

    expect(action).not.toHaveBeenCalled();
    await fireEvent.click(
      view.getByRole('button', { name: '[[bulkactions, core]]' })
    );
    await flushMicrotasks();
    await fireEvent.click(view.getByRole('menuitem', { name: 'Action' }));
    expect(action).toHaveBeenCalledTimes(1);
  });

  it('should not have any accessibility violations', async () => {
    const view = render(Basket, {
      props: {
        items: [1, 2, 3],
        bulkActions: [{ label: 'Action', action: () => {} }],
      },
    });

    expect(await axe(view.container)).toHaveNoViolations();
  });
});
