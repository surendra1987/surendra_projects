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
 * @author Simon Chester <simon.chester@totara.com>
 * @module totara_catalog
 */

import ExplorePage from '../ExplorePage';
import { fireEvent, render, screen } from 'tui_test_utils/vtl';
import {
  resetUseFilteredResultsValues,
  useFilteredResultsValues as values,
} from '../use_filtered_results';
import { produce } from 'tui/immutable';

jest.mock('../use_filtered_results');

const item = i => ({ itemid: i, title: `Item ${i}` });

describe('ExplorePage', () => {
  beforeAll(() => {
    resetUseFilteredResultsValues();
  });

  it('renders results', async () => {
    values.results.value = {
      items: [item(1)],
    };
    renderExplorePage();
    expect(screen.getByRole('link', { name: /item 1/i })).toBeInTheDocument();
  });

  it('handles focus when using load more', async () => {
    values.results.value = {
      items: [item(1)],
    };
    values.loadMore = jest.fn(() => {
      values.firstNewResultIndex.value = values.results.value.items.length;
      values.results.value = produce(values.results.value, draft => {
        draft.items.push(item(draft.items.length + 1));
      });
    });

    renderExplorePage();

    expect(screen.getByRole('link', { name: /item 1/i })).toBeInTheDocument();
    expect(
      screen.queryByRole('link', { name: /item 2/i })
    ).not.toBeInTheDocument();

    // Pressing "Load more" should focus the first new loaded item
    await fireEvent.click(screen.getByRole('button', { name: /loadmore/ }));
    expect(screen.getByRole('link', { name: /item 2/i })).toBeInTheDocument();
    expect(document.activeElement).toBe(
      screen.getByRole('link', { name: /item 2/i })
    );

    // Try a second time
    await fireEvent.click(screen.getByRole('button', { name: /loadmore/ }));
    expect(screen.getByRole('link', { name: /item 3/i })).toBeInTheDocument();
    expect(document.activeElement).toBe(
      screen.getByRole('link', { name: /item 3/i })
    );
  });

  it('can hide header buttons', async () => {
    const view = renderExplorePage({
      buttons: {
        extra: {},
        create: [{ url: 'course', label: 'Course' }],
      },
    });
    expect(
      screen.getByRole('button', { name: /createnew/i })
    ).toBeInTheDocument();
    await view.rerender({ hideHeaderButtons: true });
    expect(
      screen.queryByRole('button', { name: /createnew/i })
    ).not.toBeInTheDocument();
  });
});

function renderExplorePage(props) {
  return render(ExplorePage, {
    props: {
      filters: [],
      buttons: { extra: {}, create: {} },
      config: {},
      ...props,
    },
  });
}
