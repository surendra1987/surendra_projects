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

import ClosureSetting from '../Assignment';
import { fireEvent, render, waitFor } from 'tui_test_utils/vtl';

import { h } from 'vue';

// graphQL
import updateAssignmentSetting from 'mod_perform/graphql/add_track_assignments';

jest.mock('tui/notifications');

const defaultDraftData = {
  activity_context_id: 1,
  draft: true,
  can_assign_positions: true,
  can_assign_organisations: true,
  types: {
    aud: 1,
    org: 2,
    pos: 3,
    ind: 4,
  },
  assignees: [],
};

const addedOneAudience = jest.fn(() => ({
  data: {
    mod_perform_add_track_assignments: {
      id: '32',
      can_assign_organisations: true,
      can_assign_positions: true,
      assignments: [
        {
          type: 1,
          group: {
            id: '3',
            type: 1,
            type_label: 'Audience',
            name: 'VIP',
            size: 5,
            extra: null,
          },
        },
      ],
    },
  },
}));

describe('Manage PA assignments', () => {
  it('can manage assigning audience in draft state', async () => {
    let view = render(ClosureSetting, {
      props: { activityId: 1, data: defaultDraftData, trackId: 2 },
      mockQueries: [
        {
          request: {
            query: updateAssignmentSetting,
            variables: {
              assignments: {
                track_id: 2,
                type: 1,
                groups: [
                  {
                    id: '1',
                    type: 1,
                  },
                ],
              },
            },
          },
          result: addedOneAudience,
        },
      ],
      global: {
        stubs: {
          AudienceAdder: {
            props: ['open'],
            emits: ['added'],
            watch: {
              open(val) {
                if (val) {
                  this.$emit('added', {
                    data: [
                      {
                        id: '1',
                        name: 'Content makers',
                        idnumber: '',
                        description:
                          'This audience is for creative staff members',
                        type: 'STATIC',
                        active: true,
                      },
                    ],
                    ids: [1],
                  });
                }
              },
            },
            render() {
              return h('div');
            },
          },
        },
      },
    });

    // Assign users button should be available
    expect(
      view.getByRole('button', {
        name: '[[user_group_assignment_add_group, mod_perform]]',
      })
    ).not.toBeDisabled();

    // Should see no groups assigned text
    expect(
      view.getByText('[[user_group_assignment_no_users, mod_perform]]')
    ).toBeInTheDocument();

    // Trigger assign users dropdown
    await fireEvent.click(
      view.getByRole('button', {
        name: '[[user_group_assignment_add_group, mod_perform]]',
      })
    );

    await waitFor(() => {
      // Should see available assignment type buttons
      expect(
        view.getByRole('menuitem', {
          name: '[[user_group_assignment_group_cohort, mod_perform]]',
        })
      ).toBeInTheDocument();
    });

    // Trigger audience adder
    await fireEvent.click(
      view.getByRole('menuitem', {
        name: '[[user_group_assignment_group_cohort, mod_perform]]',
      })
    );

    await waitFor(() => {
      expect(addedOneAudience).toHaveBeenCalled();
    });
  });
});
