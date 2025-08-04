/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * Totara Enterprise Extensions is provided only to Totara
 * Learning Solutions LTD's customers and partners, pursuant to
 * the terms and conditions of a separate agreement with Totara
 * Learning Solutions LTD or its affiliate.
 *
 * If you do not have an agreement with Totara Learning Solutions
 * LTD, you may not access, use, modify, or distribute this software.
 * Please contact [licensing@totara.com] for more information.
 *
 * @author Simon Chester <simon.chester@totara.com>
 * @module mfa_totp
 */

import Verify from '../Verify';
import { fireEvent, render, screen } from 'tui_test_utils/vtl';

describe('Verify', () => {
  it('emits data to send to the server', async () => {
    const view = render(Verify);

    await fireEvent.update(
      screen.getByRole('textbox', { name: /token/ }),
      '123456'
    );

    await fireEvent.click(screen.getByRole('button', { name: /verify/ }));

    expect(view.emitted('submit')).toEqual([[{ token: '123456' }]]);
  });

  it('renders submission errors', async () => {
    const view = render(Verify);

    await view.rerender({ submissionError: { type: 'verify' } });
    expect(screen.getByText(/error:invalid_token/)).toBeInTheDocument();

    await view.rerender({
      submissionError: {
        type: 'blah',
        message: 'My hovercraft is full of eels',
      },
    });

    expect(
      screen.getByText(/My hovercraft is full of eels/i)
    ).toBeInTheDocument();
  });
});
