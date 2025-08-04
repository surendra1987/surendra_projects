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

import Verify from '../Verify';
import { fireEvent, render, screen, waitFor } from 'tui_test_utils/vtl';
import verifyMutation from 'core/graphql/mfa_verify_factor';

jest.mock('tui/tui', () => {
  const { markRaw } = require('vue');
  return {
    asyncComponent: x => markRaw(require(x)),
    loadComponent: x => markRaw(require(x)),
  };
});

jest.mock(
  'mfa_brick/components/Verify',
  () => ({
    render: h => h('div', ["I'm a brick"]),
  }),
  { virtual: true }
);

jest.mock(
  'mfa_foobar/components/Verify',
  () => {
    const { h } = require('vue');
    return {
      inheritAttrs: false,
      props: {
        data: Object,
        submitting: Boolean,
        submissionError: Object,
      },
      render() {
        const output = (id, content) =>
          h('div', { 'data-testid': id }, content);

        return h('div', [
          'Foobar Verify',
          output('instances', this.data.instances.map(x => x.id).join(',')),
          // output('submitting', String(this.submitting)),
          output('error', this.submissionError && this.submissionError.type),
          h(
            'button',
            { onClick: () => this.$emit('submit', { token: '123' }) },
            'Verify'
          ),
          h(
            'button',
            { onClick: () => this.$emit('submit', { token: '000' }) },
            'Verify (invalid)'
          ),
        ]);
      },
    };
  },
  { virtual: true }
);

describe('Verify', () => {
  it("renders the factor's verification UI", async () => {
    render(Verify, {
      props: {
        registeredFactors: [
          {
            id: 'foobar',
            data: { instances: [{ id: 1 }, { id: 2 }, { id: 3 }] },
          },
        ],
      },
    });

    expect(screen.getByText(/foobar verify/i)).toBeInTheDocument();

    // check instances, submitting, error are passed to verification UI
    expect(screen.getByTestId('instances').textContent).toBe('1,2,3');
    // expect(screen.getByTestId('submitting').textContent).toBe('false');
    expect(screen.getByTestId('error').textContent).toBe('');
  });

  it("passes input from the factor's verification UI back to the server", async () => {
    const setLocation = jest.fn();
    Object.defineProperty(window, 'location', {
      set: setLocation,
      configurable: true,
    });

    const execSubmit = jest.fn(() => ({
      data: { result: { success: true, next_url: '/success' } },
    }));
    const execSubmitFailure = jest.fn(() => ({
      data: { result: { success: false, next_url: null } },
    }));

    render(Verify, {
      props: {
        registeredFactors: [
          {
            id: 'foobar',
            data: { instances: [] },
          },
        ],
      },
      mockQueries: [
        {
          request: {
            query: verifyMutation,
            variables: { input: { factor: 'foobar', data: '{"token":"123"}' } },
          },
          result: execSubmit,
        },
        {
          request: {
            query: verifyMutation,
            variables: { input: { factor: 'foobar', data: '{"token":"000"}' } },
          },
          result: execSubmitFailure,
        },
      ],
    });

    await fireEvent.click(
      screen.getByRole('button', { name: 'Verify (invalid)' })
    );

    await waitFor(() => {
      expect(execSubmitFailure).toHaveBeenCalled();
    });

    expect(screen.getByTestId('error').textContent).toBe('verify');

    await fireEvent.click(screen.getByRole('button', { name: 'Verify' }));

    await waitFor(() => {
      expect(execSubmit).toHaveBeenCalled();
    });

    expect(screen.getByTestId('error').textContent).toBe('');
    expect(setLocation).toHaveBeenCalledWith('/success');
  });

  it('lets you pick from multiple factors', async () => {
    render(Verify, {
      props: {
        registeredFactors: [
          {
            id: 'brick',
            name: 'Brick',
            data: { instances: [{ id: 1 }] },
          },
          {
            id: 'foobar',
            name: 'Rock',
            data: { instances: [{ id: 2 }] },
          },
        ],
      },
    });

    expect(screen.getByRole('button', { name: /brick/i })).toBeInTheDocument();
    await fireEvent.click(screen.getByRole('button', { name: /rock/i }));

    // we should now be rendering the correct factor:
    expect(screen.getByText(/foobar verify/i)).toBeInTheDocument();
    expect(screen.getByTestId('instances').textContent).toBe('2');
  });
});
