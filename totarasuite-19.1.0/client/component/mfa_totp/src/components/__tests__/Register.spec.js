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

import Register from '../Register';
import { fireEvent, render, screen, waitFor } from 'tui_test_utils/vtl';
import createInstanceMutation from 'mfa_totp/graphql/create_instance';

const secret = 'brdsarntreal';

async function submitForm(token) {
  await fireEvent.update(
    screen.getByRole('textbox', { name: /verify/ }),
    token
  );

  await fireEvent.click(screen.getByRole('button', { name: /save/ }));
}

describe('Register', () => {
  it('sends form data to the server', async () => {
    const createResult = jest.fn(() => ({ data: { instance: { id: 123 } } }));

    const view = render(Register, {
      props: {
        data: { secret },
      },
      mockQueries: [
        {
          request: {
            query: createInstanceMutation,
            variables: { input: { secret, token: 'wrong' } },
          },
          result: {
            errors: [{ extensions: { category: 'mfa_totp/invalid_token' } }],
          },
        },
        {
          request: {
            query: createInstanceMutation,
            variables: { input: { secret, token: '123456' } },
          },
          result: createResult,
        },
      ],
    });

    // fill with wrong values first, should display error from server
    await submitForm('wrong');
    await waitFor(() => {
      expect(screen.getByText(/invalid_token/)).toBeInTheDocument();
    });

    // change value -- error should disappear
    await fireEvent.update(
      screen.getByRole('textbox', { name: /verify/ }),
      '1'
    );
    await waitFor(() => {
      expect(screen.queryByText(/invalid_token/)).not.toBeInTheDocument();
    });

    // correct values
    await submitForm('123456');
    await waitFor(() => {
      expect(createResult).toHaveBeenCalled();
    });
    expect(view.emitted('saved')).toEqual([[123]]);
  });

  it('shows both QR and text', async () => {
    const qr = 'https://placekitten.com/200/200';
    render(Register, {
      props: {
        data: { secret, qr_url: qr },
      },
    });

    const img = screen.getByRole('img');
    expect(img.getAttribute('src')).toBe(qr);

    expect(screen.getByText(/brds arnt real/)).toBeInTheDocument();
  });
});
