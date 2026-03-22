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

import VisibilitySetting from '../Visibility';
import { fireEvent, render, waitFor } from 'tui_test_utils/vtl';

// graphQL
import updateVisibilitySetting from 'mod_perform/graphql/update_activity_visibility_settings';

jest.mock('tui/notifications');

const options = [
  {
    name: 'option 1',
    value: 0,
  },
  {
    name: 'option 2',
    value: 1,
  },
  {
    name: 'option 3',
    value: 2,
  },
];

const defaultDraftData = {
  anonymous_responses: {
    mutable: true,
    value: false,
  },
  valid_closure_state: true,
  visibility_condition: {
    anonymous_value: 2,
    options: options,
    value: 0,
  },
};

const defaultActiveData = {
  anonymous_responses: {
    mutable: false,
    value: false,
  },
  valid_closure_state: true,
  visibility_condition: {
    anonymous_value: 2,
    options: options,
    value: 0,
  },
};

// Anon is off with value 2 selected
const anonOffOption2 = jest.fn(() => ({
  data: {
    mod_perform_update_activity_visibility_settings: {
      anonymous_responses: false,
      visibility_condition: {
        name: 'Show responses when the section is submitted and closed',
        value: 1,
      },
    },
  },
}));

// Anon is on with value 2 selected
const anonOnOption2 = jest.fn(() => ({
  data: {
    mod_perform_update_activity_visibility_settings: {
      anonymous_responses: true,
      visibility_condition: {
        name: 'Show responses when the section is submitted and closed',
        value: 2,
      },
    },
  },
}));

// Anon is on with value 2 selected
const anonOffOption3 = jest.fn(() => ({
  data: {
    mod_perform_update_activity_visibility_settings: {
      anonymous_responses: true,
      visibility_condition: {
        name: 'Show responses when the section is submitted and closed',
        value: 2,
      },
    },
  },
}));

describe('Manage PA anonymous visibility', () => {
  it('can manage the anonymous draft state', async () => {
    let view = render(VisibilitySetting, {
      props: { activityId: 1, data: defaultDraftData },
      mockQueries: [
        {
          request: {
            query: updateVisibilitySetting,
            variables: {
              input: {
                activity_id: 1,
                anonymous_responses: false,
                visibility_condition: 1,
              },
            },
          },
          result: anonOffOption2,
        },
        {
          request: {
            query: updateVisibilitySetting,
            variables: {
              input: {
                activity_id: 1,
                anonymous_responses: true,
                visibility_condition: 2,
              },
            },
          },
          result: anonOnOption2,
        },
        {
          request: {
            query: updateVisibilitySetting,
            variables: {
              input: {
                activity_id: 1,
                anonymous_responses: false,
                visibility_condition: 2,
              },
            },
          },
          result: anonOffOption3,
        },
      ],
    });

    // Anon toggle should be changeable
    expect(
      view.getByRole('button', {
        name: '[[activity_general_anonymous_responses_label, mod_perform]]',
      })
    ).not.toBeDisabled();

    // Option 1 should be disabled
    expect(
      view.getByRole('radio', {
        name: '[[visibility_condition_response_label, mod_perform]] option 1',
      })
    ).not.toBeDisabled();

    // Option 2 should be disabled
    expect(
      view.getByRole('radio', {
        name: 'option 2',
      })
    ).not.toBeDisabled();

    // Select the second option
    await fireEvent.click(
      view.getByRole('radio', {
        name: 'option 2',
      })
    );

    await waitFor(() => {
      expect(anonOffOption2).toHaveBeenCalled();
    });

    // Turn anonymous mode on
    await fireEvent.click(
      view.getByRole('button', {
        name: '[[activity_general_anonymous_responses_label, mod_perform]]',
      })
    );

    await waitFor(() => {
      expect(anonOnOption2).toHaveBeenCalled();
    });

    // Option 1 should be disabled
    expect(
      view.getByRole('radio', {
        name: '[[visibility_condition_response_label, mod_perform]] option 1',
      })
    ).toBeDisabled();

    // Option 2 should be disabled
    expect(
      view.getByRole('radio', {
        name: 'option 2',
      })
    ).toBeDisabled();

    // Turn anonymous mode back off
    await fireEvent.click(
      view.getByRole('button', {
        name: '[[activity_general_anonymous_responses_label, mod_perform]]',
      })
    );

    await waitFor(() => {
      expect(anonOffOption3).toHaveBeenCalled();
    });
  });

  it('can manage the anonymous active state', async () => {
    let view = render(VisibilitySetting, {
      props: { activityId: 1, data: defaultActiveData },
      mockQueries: [
        {
          request: {
            query: updateVisibilitySetting,
            variables: {
              input: {
                activity_id: 1,
                anonymous_responses: false,
                visibility_condition: 1,
              },
            },
          },
          result: anonOffOption2,
        },
      ],
    });

    // Anon toggle no longer available and replaced with string instead
    expect(
      view.getByText('[[boolean_setting_text_disabled, mod_perform]]')
    ).toBeInTheDocument();

    // Select the second option
    await fireEvent.click(
      view.getByRole('radio', {
        name: 'option 2',
      })
    );

    await waitFor(() => {
      expect(anonOffOption2).toHaveBeenCalled();
    });
  });
});
