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
 * @module core_auth
 */

import Login from '../Login';
import { fireEvent, render, screen, waitFor } from 'tui_test_utils/vtl';
import { redirectWithPost } from 'tui/dom/form';

jest.mock('tui/dom/form', () => {
  return {
    redirectWithPost: jest.fn(),
  };
});

const layoutData = {
  lang: { current: 'en', langs: {}, showMenu: false },
  siteName: 'Totara',
  homeUrl: 'https://localhost/',
  logoUrl: 'logo.svg',
  backgroundImage: { url: 'bg.svg' },
  footerHtml: '',
};

describe('Login', () => {
  it('submits form', async () => {
    render(Login, { props: { layoutData } });
    await fireEvent.update(
      screen.getByRole('textbox', { name: /username/ }),
      'fred'
    );
    await fireEvent.update(screen.getByLabelText(/password/), 'topsecret');
    await fireEvent.click(screen.getByRole('button', { name: /login/ }));
    await waitFor(() => {
      expect(redirectWithPost).toHaveBeenCalledWith(
        'http://localhost/login/index.php',
        {
          username: 'fred',
          password: 'topsecret',
          rememberusernamechecked: undefined,
          logintoken: 'sesskey',
        }
      );
    });
  });

  it('renders idps', async () => {
    render(Login, {
      props: {
        layoutData,
        loginOptions: {
          idps: [{ name: 'Stubby', url: 'https://localhost/stubidp' }],
        },
      },
    });

    const link = screen.getByRole('link', { name: 'Stubby' });
    expect(link.href).toBe('https://localhost/stubidp');
  });
});
