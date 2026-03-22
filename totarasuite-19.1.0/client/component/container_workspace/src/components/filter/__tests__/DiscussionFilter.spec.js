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
 * @module container_workspace
 */

import DiscussionFilter from '../DiscussionFilter';
import { fireEvent, render, screen, waitFor } from 'tui_test_utils/vtl';
import discussionOptionsQuery from 'container_workspace/graphql/discussion_filter_options';

describe('DiscussionFilter', () => {
  it('allows sorting and filtering', async () => {
    const sortOption = {
      __typename: 'container_workspace_discussion_sort_option',
    };

    const view = render(DiscussionFilter, {
      props: {
        workspaceId: 1,
      },
      mockQueries: [
        {
          request: {
            query: discussionOptionsQuery,
          },
          result: {
            data: {
              sorts: [
                { ...sortOption, label: 'Last updated', value: 'updated' },
                { ...sortOption, label: 'Date posted', value: 'posted' },
              ],
            },
          },
        },
      ],
    });

    // wait for query to load
    // there's no findByX for option values, so we need to do it manually
    await waitFor(() => {
      expect(
        view.container.querySelector('option[value=updated]')
      ).toBeInTheDocument();
    });

    // sorting should emit the sort value
    await fireEvent.update(
      screen.getByRole('combobox', { name: /sortby/ }),
      'posted'
    );
    expect(view.emitted('update-sort')).toEqual([['posted']]);

    // searching should emit the value and hide sorting
    await fireEvent.update(
      screen.getByRole('searchbox', { name: /search_discussions/ }),
      'schmetterling'
    );
    await fireEvent.click(
      screen.getByRole('button', { name: /search_discussions/ })
    );
    expect(view.emitted('update-search-term')).toEqual([['schmetterling']]);
    expect(view.queryByRole('combobox', { name: /sortby/ })).toBeNull();

    // clearing should restore the sort combobox
    await fireEvent.click(screen.getByRole('button', { name: /clear/ }));
    expect(view.emitted('update-search-term')).toEqual([
      ['schmetterling'],
      [''],
    ]);
    expect(
      screen.getByRole('combobox', { name: /sortby/ })
    ).toBeInTheDocument();
  });
});
