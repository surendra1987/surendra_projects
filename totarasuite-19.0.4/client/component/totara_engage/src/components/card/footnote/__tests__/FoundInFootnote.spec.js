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

import FoundInFootnote from '../FoundInFootnote.vue';
import { render, screen, waitFor } from 'tui_test_utils/vtl';

describe('FoundInFootnote', () => {
  it('Displays correctly', async () => {
    render(FoundInFootnote, {
      props: {
        containers: [
          { name: 'play1', url: 'http://example.com/1', type: 'a' },
          { name: 'play2', url: 'http://example.com/2', type: 'a' },
        ],
      },
    });

    await waitFor(() => {
      expect(
        screen.getByText(/foundinmultiple, totara_engage/)
      ).toBeInTheDocument();
    });

    render(FoundInFootnote, {
      props: {
        containers: [{ name: 'play1', url: 'http://example.com/1', type: 'a' }],
      },
    });

    await waitFor(() => {
      expect(
        screen.getByRole('link', { text: /foundin, totara_engage, play1/ })
      ).toBeInTheDocument();
    });
  });
});
