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

import ClosureSetting from '../Closure';
import { fireEvent, render, waitFor } from 'tui_test_utils/vtl';

// graphQL
import updateClosureSetting from 'mod_perform/graphql/update_activity_closure_settings';

jest.mock('tui/notifications');

const options = [
  {
    id: 'close_on_completion',
    label: 'Auto-close when all sections completed',
    desc:
      'The participants own activity will be closed once they have completed and submitted all sections.',
  },
  {
    id: 'close_on_due_date',
    label: 'Close on due date',
    desc: 'The activity will be closed on the set due date.',
  },
  {
    id: 'manual_close',
    label: 'Manual close',
    desc: 'Allow responding participants to manually close their own activity.',
  },
];

const defaultDraftData = {
  checkbox_options: options,
  draft: true,
  mutable: {
    close_on_due_date: true,
  },
  valid_closure_state: true,
  value: {
    close_on_completion: false,
    close_on_due_date: false,
    close_on_section_submission: false,
    manual_close: false,
  },
};

const closeOnCompletionOn = jest.fn(() => ({
  data: {
    mod_perform_update_activity_closure_settings: {
      close_on_section_submission: false,
      close_on_completion: true,
      close_on_due_date: false,
      manual_close: false,
    },
  },
}));

const sectionClosureOn = jest.fn(() => ({
  data: {
    mod_perform_update_activity_closure_settings: {
      close_on_section_submission: true,
      close_on_completion: true,
      close_on_due_date: true,
      manual_close: false,
    },
  },
}));

const activityDueDateOn = jest.fn(() => ({
  data: {
    mod_perform_update_activity_closure_settings: {
      close_on_section_submission: false,
      close_on_completion: true,
      close_on_due_date: true,
      manual_close: false,
    },
  },
}));

const activityManualCloseOn = jest.fn(() => ({
  data: {
    mod_perform_update_activity_closure_settings: {
      close_on_section_submission: true,
      close_on_completion: true,
      close_on_due_date: true,
      manual_close: true,
    },
  },
}));

describe('Manage PA closure conditions', () => {
  it('can manage the section closure draft state', async () => {
    let view = render(ClosureSetting, {
      props: { activityId: 1, data: defaultDraftData },
      mockQueries: [
        {
          request: {
            query: updateClosureSetting,
            variables: {
              input: {
                activity_id: 1,
                close_on_completion: true,
                close_on_due_date: false,
                close_on_section_submission: false,
                manual_close: false,
              },
            },
          },
          result: closeOnCompletionOn,
        },
        {
          request: {
            query: updateClosureSetting,
            variables: {
              input: {
                activity_id: 1,
                close_on_completion: true,
                close_on_due_date: true,
                close_on_section_submission: false,
                manual_close: false,
              },
            },
          },
          result: activityDueDateOn,
        },
        {
          request: {
            query: updateClosureSetting,
            variables: {
              input: {
                activity_id: 1,
                close_on_completion: true,
                close_on_due_date: true,
                close_on_section_submission: false,
                manual_close: true,
              },
            },
          },
          result: activityManualCloseOn,
        },
        {
          request: {
            query: updateClosureSetting,
            variables: {
              input: {
                activity_id: 1,
                close_on_completion: true,
                close_on_due_date: true,
                close_on_section_submission: true,
                manual_close: true,
              },
            },
          },
          result: sectionClosureOn,
        },
      ],
    });

    // section closure toggle should be changeable
    expect(
      view.getByRole('button', {
        name: '[[manage_closure_section_closure_option, mod_perform]]',
      })
    ).not.toBeDisabled();

    // Auto-close when all sections completed should be changeable
    expect(
      view.getByRole('checkbox', {
        name:
          '[[manage_closure_activity_closure, mod_perform]] Auto-close when all sections completed',
      })
    ).not.toBeDisabled();

    // Turn on the close on completion setting
    await fireEvent.click(
      view.getByRole('checkbox', {
        name:
          '[[manage_closure_activity_closure, mod_perform]] Auto-close when all sections completed',
      })
    );

    await waitFor(() => {
      expect(closeOnCompletionOn).toHaveBeenCalled();
    });

    // Turn on due date checkbox
    await fireEvent.click(
      view.getByRole('checkbox', {
        name: 'Close on due date',
      })
    );

    await waitFor(() => {
      expect(activityDueDateOn).toHaveBeenCalled();
    });

    // Turn on manual close checkbox
    await fireEvent.click(
      view.getByRole('checkbox', {
        name: 'Manual close',
      })
    );

    await waitFor(() => {
      expect(activityManualCloseOn).toHaveBeenCalled();
    });

    // Turn on section change toggle
    await fireEvent.click(
      view.getByRole('button', {
        name: '[[manage_closure_section_closure_option, mod_perform]]',
      })
    );

    // Auto-close when all sections completed should be disabled when section change toggle is on
    expect(
      view.getByRole('checkbox', {
        name:
          '[[manage_closure_activity_closure, mod_perform]] Auto-close when all sections completed',
      })
    ).toBeDisabled();

    await waitFor(() => {
      expect(sectionClosureOn).toHaveBeenCalled();
    });

    // Help text can be displayed
    await fireEvent.click(
      view.getByRole('button', {
        name:
          '[[show_help_for_x, totara_core, "[[manage_closure_activity_closure, mod_perform]]"]]',
      })
    );
  });
});
