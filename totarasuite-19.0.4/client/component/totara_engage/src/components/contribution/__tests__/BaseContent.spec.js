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
 * @author Brian Barnes <brian.barnes@totara.com>
 * @module totara_engage
 */

import BaseContent from '../BaseContent';
import { render, screen, waitFor } from 'tui_test_utils/vtl';
import { shallowMount } from '@vue/test-utils';

describe('BaseContent', () => {
  it('Count is correct', async () => {
    const wrapper = shallowMount(BaseContent, {
      props: {
        cards: [{}, {}, {}],
        loadingMore: false,
        loading: false,
      },
    });

    expect(wrapper.vm.count).toBe(3);

    await wrapper.setProps({ canAdd: true });
    // First element is the add card, not to be included in the count
    expect(wrapper.vm.count).toBe(2);

    const content = render(BaseContent, {
      props: {
        cards: [{}, {}, {}],
        loadingMore: false,
        loading: false,
        isLoadMoreVisible: true,
        totalCards: 15,
      },
    });

    await waitFor(() => {
      expect(
        screen.getByText(/\[viewedresources, engage_article, 3\]/)
      ).toBeInTheDocument();
    });

    content.rerender({ canAdd: true });
    await waitFor(() => {
      // First element is the add card, not to be included in the count
      expect(
        screen.getByText(/\[viewedresources, engage_article, 2\]/)
      ).toBeInTheDocument();
    });
    expect(
      screen.getByRole('button', { text: /\[loadmore, engage_article\]/ })
    ).toBeInTheDocument();

    // Number of cards matching whats on the page
    content.rerender({ totalCards: 2 });
    await waitFor(() => {
      // First element is the add card, not to be included in the count
      expect(
        screen.getByText(/\[resourcecount, totara_engage, 2\]/)
      ).toBeInTheDocument();
    });

    expect(
      screen.queryByRole('button', { text: /\[loadmore, engage_article\]/ })
    ).toBeNull();
  });
});
