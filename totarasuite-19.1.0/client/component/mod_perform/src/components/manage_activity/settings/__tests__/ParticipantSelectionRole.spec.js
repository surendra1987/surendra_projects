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
 * Please contact [licensing@totara.com] for more information.
 *
 * @author Kevin Hottinger <kevin.hottinger@totara.com>
 * @module totara_perform
 */

import ParticipantSelectionSetting from '../ParticipantSelectionRole';
import { fireEvent, render, waitFor } from 'tui_test_utils/vtl';

jest.mock('tui/notifications');

const defaultDraftData = {
  options: [
    {
      id: 1,
      label: 'Subjects',
    },
    {
      id: 6,
      label: 'Managers',
    },
    {
      id: 7,
      label: "Manager's managers",
    },
    {
      id: 8,
      label: 'Appraisers',
    },
    {
      id: 9,
      label: 'Direct reports',
    },
  ],
  relationships: [
    {
      id: 2,
      name: 'Peers',
      mutable: true,
      selector: {
        id: 1,
        name: 'Subjects',
      },
    },
    {
      id: 3,
      name: 'Mentors',
      mutable: true,
      selector: {
        id: 1,
        name: 'Subjects',
      },
    },
    {
      id: 4,
      name: 'Reviewers',
      mutable: true,
      selector: {
        id: 1,
        name: 'Subjects',
      },
    },
    {
      id: 5,
      name: 'External respondents',
      mutable: true,
      selector: {
        id: 1,
        name: 'Subjects',
      },
    },
  ],
};

describe('Manage PA participant selection conditions', () => {
  it('can manage the participant selection role draft state', async () => {
    let view = render(ParticipantSelectionSetting, {
      props: { activityId: 1, data: defaultDraftData },
    });

    expect(
      view.getByText(
        '[[manual_participant_selector_role_description, mod_perform]]'
      )
    ).toBeInTheDocument();

    expect(
      view.getByRole('combobox', {
        name: 'Peers',
      })
    ).toHaveValue('1');

    expect(
      view.getByRole('combobox', {
        name: 'Mentors',
      })
    ).toHaveValue('1');

    expect(
      view.getByRole('combobox', {
        name: 'Reviewers',
      })
    ).toHaveValue('1');

    expect(
      view.getByRole('combobox', {
        name: 'External respondents',
      })
    ).toHaveValue('1');

    await fireEvent.select(view.getByRole('combobox', { name: 'Peers' }), {
      target: { value: 6 },
    });

    await waitFor(() => {
      expect(
        view.getByRole('combobox', {
          name: 'Peers',
        })
      ).toHaveValue('6');
    });
  });
});
