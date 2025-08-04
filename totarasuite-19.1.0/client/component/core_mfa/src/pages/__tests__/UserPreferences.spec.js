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
 * @module core_mfa
 */

import UserPreferences from '../UserPreferences';
import { fireEvent, render, screen, waitFor } from 'tui_test_utils/vtl';
import deleteMutation from 'core/graphql/mfa_delete_instance';

describe('UserPreferences', () => {
  it('renders instances', async () => {
    const execDelete = jest.fn(() => ({ data: { result: true } }));

    render(UserPreferences, {
      props: {
        factors: [],
        instances: [
          {
            id: 1,
            label: null,
            factor: 'totp',
            factor_name: 'Authenticator app',
            created_at_formatted: 'Yesterday',
          },
          {
            id: 2,
            label: 'Yubikey',
            factor: 'webauthn',
            factor_name: 'Security key',
            created_at_formatted: 'Yesterday',
          },
        ],
        authPluginCompatibleWarning: true,
      },
      mockQueries: [
        {
          request: {
            query: deleteMutation,
            variables: { input: { id: 1 } },
          },
          result: execDelete,
        },
      ],
    });

    expect(screen.getByText(/Authenticator app/i)).toBeInTheDocument();
    expect(screen.getByText(/Security key/i)).toBeInTheDocument();

    await fireEvent.click(
      screen.getByRole('button', {
        name: /remove_x, mfa, "Authenticator app"/i,
      })
    );

    await waitFor(() => {
      expect(screen.getByText(/remove_factor, mfa/)).toBeInTheDocument();
    });

    const reload = jest.fn();
    Object.defineProperty(window, 'location', {
      value: { reload },
      configurable: true,
    });

    await fireEvent.click(screen.getByRole('button', { name: /remove, core/ }));

    await waitFor(() => {
      expect(execDelete).toHaveBeenCalled();
      expect(reload).toHaveBeenCalled();
    });
  });

  it('links to factor registration', async () => {
    render(UserPreferences, {
      props: {
        factors: [{ id: 'totp', name: 'Authenticator app' }],
        instances: [],
        authPluginCompatibleWarning: true,
      },
    });

    await fireEvent.click(
      screen.getByRole('button', { name: /add_factor, mfa/ })
    );

    const link = await waitFor(() =>
      screen.getByRole('link', { name: /authenticator app/i })
    );
    expect(link).toBeInTheDocument();
    expect(link.getAttribute('href')).toBe(
      'http://localhost/mfa/register_factor.php?name=totp'
    );
  });
});
