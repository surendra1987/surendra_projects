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

import RegisterFactor from '../RegisterFactor';
import { fireEvent, render, screen } from 'tui_test_utils/vtl';

jest.mock('tui/tui', () => ({
  asyncComponent: x => require(x),
}));

jest.mock(
  'mfa_foobar/components/Register',
  () => {
    const { h } = require('vue');
    return {
      props: { data: Object },
      render() {
        return h('div', [
          'Foobar Register',
          h(
            'div',
            { 'data-testid': 'register-data' },
            JSON.stringify(this.data)
          ),
          h('button', { onClick: () => this.$emit('saved', 9) }, 'Save'),
        ]);
      },
    };
  },
  { virtual: true }
);

describe('RegisterFactor', () => {
  it("renders the factor's registration UI", async () => {
    render(RegisterFactor, {
      props: {
        factor: 'foobar',
        title: 'Look out, Ted!',
        data: { rockmelons: 12 },
      },
    });

    expect(screen.getByText(/foobar register/i)).toBeInTheDocument();
    expect(screen.getByText(/\{"rockmelons":12\}/i)).toBeInTheDocument();

    const setLocation = jest.fn();
    Object.defineProperty(window, 'location', {
      set: setLocation,
      configurable: true,
    });

    await fireEvent.click(screen.getByRole('button', { name: /save/i }));

    expect(setLocation).toHaveBeenCalledWith(
      'http://localhost/mfa/user_preferences.php'
    );
  });
});
