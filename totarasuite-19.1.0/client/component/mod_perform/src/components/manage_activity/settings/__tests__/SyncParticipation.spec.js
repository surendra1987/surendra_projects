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

import ParticipantSelectionSetting from '../SyncParticipation';
import { fireEvent, render } from 'tui_test_utils/vtl';

jest.mock('tui/notifications');

const defaultDraftData = {
  override_global_participation_settings: false,
  sync_participant_instance_creation: 0,
  sync_participant_instance_closure: 0,
  sync_participant_instance_closure_options: [
    {
      id: 0,
      desc: 'aaa',
      label: 'Disabled',
    },
    {
      id: 1,
      desc: 'bbb',
      label: 'Close not started only',
    },
    {
      id: 2,
      desc: 'ccc',
      label: 'Close all',
    },
  ],
  sync_participant_instance_creation_options: [
    {
      id: 0,
      desc: 'aaa',
      label: 'Disabled',
    },
    {
      id: 1,
      desc: 'bbb',
      label: 'Enabled',
    },
  ],
};

describe('Manage PA sync participant conditions', () => {
  it('can manage the sync participant setting draft state', async () => {
    let view = render(ParticipantSelectionSetting, {
      props: { activityId: 1, data: defaultDraftData },
    });

    expect(
      view.getByText(
        '[[perform_admin_sync_participant_instance_override_heading, mod_perform]]'
      )
    ).toBeInTheDocument();

    // Override global toggle should be changeable
    expect(
      view.getByRole('button', {
        name:
          '[[perform_admin_sync_participant_instance_override_heading, mod_perform]]',
      })
    ).not.toBeDisabled();

    expect(
      view.getByRole('combobox', {
        name:
          '[[perform_admin_sync_participant_instance_role_change_create, mod_perform]]',
      })
    ).toBeDisabled();

    expect(
      view.getByRole('combobox', {
        name:
          '[[perform_admin_sync_participant_instance_role_change_close, mod_perform]]',
      })
    ).toBeDisabled();

    // Turn global override mode on
    await fireEvent.click(
      view.getByRole('button', {
        name:
          '[[perform_admin_sync_participant_instance_override_heading, mod_perform]]',
      })
    );

    expect(
      view.getByRole('combobox', {
        name:
          '[[perform_admin_sync_participant_instance_role_change_create, mod_perform]]',
      })
    ).not.toBeDisabled();

    expect(
      view.getByRole('combobox', {
        name:
          '[[perform_admin_sync_participant_instance_role_change_close, mod_perform]]',
      })
    ).not.toBeDisabled();
  });
});
