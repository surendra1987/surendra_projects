/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Keanu Johnson-Carnevale <keanu.johnson-carnevale@totara.com>
 * @module totara_api
 */

import { fireEvent, render, screen, waitFor } from 'tui_test_utils/vtl';
import Clients from '../Clients.vue';
import apiClientsQuery from 'totara_api/graphql/clients';

describe('Clients.vue', () => {
  it('Checks that a confirmation modal is shown upon clicking "Rotate secret" for a client', async () => {
    const apiClientsResult = jest.fn(() => ({
      data: {
        clients: {
          items: [
            {
              __typename: 'totara_api_client',
              id: '3',
              name: 'test client',
              service_account: {
                user: {
                  __typename: 'core_user',
                  id: '4',
                  fullname: 'Александр Соколов',
                },
                is_valid: true,
                status: 'VALID',
              },
              description: '',
              oauth2_client_providers: [
                {
                  id: '3',
                  client_id: 'tODkUZafodMR5I8i',
                  client_secret: 'LcMvuKvZmMjHhSZLkqOO8bCe',
                },
              ],
              status: true,
              active_tokens: 3,
            },
          ],
          total: 2,
          next_cursor: '',
        },
      },
    }));

    render(Clients, {
      mockQueries: [
        {
          request: {
            query: apiClientsQuery,
            variables: { input: {} },
          },
          result: apiClientsResult,
        },
      ],
    });

    await waitFor(() => {
      expect(screen.getByText('test client')).toBeInTheDocument();
    });

    //find the dropdown menu
    const button = screen.getByRole('button', {
      name: /\[\[actions_for, totara_api, "test client"\]\]/i,
    });

    //click the dropdown menu
    await fireEvent.click(button);

    //find 'rotate client secret' as an option
    const rotateButton = screen.getByText(
      /\[\[rotate_client_secret, totara_api\]\]/i
    );

    //checking that before clicking 'rotate client secret' the modal is not shown
    expect(
      screen.queryByText(
        /\[\[rotate_confirm_body_1, totara_api, "test client"\]\]/i
      )
    ).not.toBeInTheDocument();

    await fireEvent.click(rotateButton);

    //checking that after clicking  'rotate client secret' the modal shown
    expect(
      screen.queryByText(
        /\[\[rotate_confirm_body_1, totara_api, "test client"\]\]/i
      )
    ).toBeInTheDocument();
  });
});
