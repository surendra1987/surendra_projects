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

import LoginNav from '../LoginNav';
import { fireEvent, render, screen, waitFor } from 'tui_test_utils/vtl';

function renderNav(props = {}) {
  return render(LoginNav, {
    props: {
      siteName: 'Totara',
      homeUrl: 'https://localhost/',
      logoUrl: 'logo.svg',
      ...props,
      lang: {
        current: 'en',
        langs: {
          en: 'English',
          de: 'Deutsch',
        },
        showMenu: false,
        ...props.lang,
      },
    },
  });
}

describe('LoginNav', () => {
  it('hides lang menu when showMenu is set to false', () => {
    renderNav();
    expect(
      screen.queryByRole('button', { name: /language_options/ })
    ).not.toBeInTheDocument();
  });

  it('renders lang menu', async () => {
    const setLocation = jest.fn();
    Object.defineProperty(window, 'location', {
      get: () => 'https://localhost/page',
      set: setLocation,
      configurable: true,
    });

    renderNav({ lang: { showMenu: true } });
    const langButton = screen.queryByRole('button', {
      name: /language_options/,
    });
    expect(langButton).toBeInTheDocument();
    await fireEvent.click(langButton);
    await waitFor(() => {
      expect(
        screen.queryByRole('menuitem', { name: /deutsch/i })
      ).toBeInTheDocument();
    });
    await fireEvent.click(screen.getByRole('menuitem', { name: /deutsch/i }));
    await waitFor(() => {
      expect(setLocation).toHaveBeenLastCalledWith(
        'https://localhost/page?lang=de'
      );
    });
  });
});
