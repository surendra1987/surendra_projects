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

import Collapsible from '../Collapsible';
import { fireEvent, render, screen } from 'tui_test_utils/vtl';
import { axe } from 'jest-axe';
import { h } from 'vue';

const slots = {
  default({ empty }) {
    return !empty && h('div', { class: 'test-action' }, 'content');
  },
};

describe('Collapsible', () => {
  it('should not have any accessibility violations', async () => {
    const view = render(Collapsible, {
      props: { label: 'hello', value: true, initialState: true },
      slots,
    });

    const results = await axe(view.container);
    expect(results).toHaveNoViolations();
  });

  it('hides and shows its content when the header is pressed', async () => {
    render(Collapsible, {
      props: { label: 'hello' },
      slots,
    });

    expect(screen.queryByText(/content/)).not.toBeInTheDocument();
    await fireEvent.click(screen.queryByRole('button', /hello/));
    expect(screen.queryByText(/content/)).toBeInTheDocument();
  });
});
