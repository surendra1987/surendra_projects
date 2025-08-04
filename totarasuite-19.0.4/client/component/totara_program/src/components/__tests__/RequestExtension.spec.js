/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Brian Barnes <brian.barnes@totara.com>
 * @module totara_program
 */
import RequestExtension from '../RequestExtension';
import { fireEvent, render, screen, waitFor } from 'tui_test_utils/vtl';
import extensionRequest from 'totara_program/graphql/request_extension';

describe('RequestExtension', () => {
  it('Clicking the link loads the modal', async () => {
    let date = new Date().toISOString();
    const extedatetime = new Date(date);
    date = new Date(date);
    const progId = '59';
    const extreason = 'Some random reason';
    extedatetime.setMilliseconds(0);
    extedatetime.setSeconds(0);
    extedatetime.setMinutes(5);
    extedatetime.setHours(6);
    extedatetime.setDate(1);
    extedatetime.setFullYear(date.getFullYear() + 2);

    const component = render(RequestExtension, {
      props: {
        programId: progId,
        currentDue: (date / 1000).toFixed(0).toString(), // data is supplied as a Unix timestamp
        programName: 'new Program',
        extensionRequested: false,
      },
      global: {
        mockQueries: [
          {
            request: {
              query: extensionRequest,
              variables: {
                input: {
                  id: progId,
                  extdatetime: extedatetime.toISOString(),
                  extreason,
                },
              },
            },
            result: {
              data: {
                totara_program_request_extension: {
                  success: true,
                  message: 'Pending extension request',
                },
              },
            },
          },
        ],
      },
    });

    expect(screen.queryByText(/requestextension/)).toBeInTheDocument();
    expect(screen.queryByText(/pendingextension/)).not.toBeInTheDocument();

    // while the modal is shown, act as though it's not
    await fireEvent.click(screen.getByText(/requestextension/));
    expect(screen.getByText(/extenduntil/)).toBeInTheDocument();

    const submitButton = screen.getByRole('button', { name: /submit/ });
    expect(submitButton).toBeDisabled();

    const reason = screen.getByLabelText(/reason, totara_program/);
    await fireEvent.update(reason, extreason);
    await fireEvent.update(
      screen.getByLabelText(/\[\[year, totara_core\]\]/),
      extedatetime.getUTCFullYear()
    );
    await fireEvent.update(screen.getByLabelText(/\[\[minutes, core\]\]/), '5');
    await fireEvent.update(screen.getByLabelText(/\[\[hour, core\]\]/), '6');
    await fireEvent.update(
      screen.getByLabelText(/\[\[day, totara_core\]\]/),
      '1'
    );

    await waitFor(() => {
      expect(submitButton).toBeEnabled();
    });
    await fireEvent.click(submitButton);
    await waitFor(() => {
      expect(component.emitted('extension-requested')).toEqual([[]]);
    });

    await waitFor(() => {
      expect(screen.queryByText(/pendingextension/)).toBeInTheDocument();
    });
    expect(screen.queryByText(/requestextension/)).not.toBeInTheDocument();
  });
});
