/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Dave Wallace <dave.wallace@totaralearning.com>
 * @module tui
 */

import { render, waitFor } from 'tui_test_utils/vtl';
import Responsive from '../Responsive';

describe('Responsive', () => {
  it('passes the boundary to the scoped slot', async () => {
    const slot = jest.fn();
    render(Responsive, {
      props: {
        breakpoints: [
          { name: 'small', boundaries: [0, 764] },
          { name: 'medium', boundaries: [765, 1192] },
          { name: 'large', boundaries: [1193, 1672] },
        ],
      },
      slots: {
        default: slot,
      },
    });

    // The resizing functionality is async
    await waitFor(() => {
      expect(slot).toHaveBeenCalledWith({ currentBoundaryName: 'large' });
    });
  });

  it('renders in VTL', async () => {
    const slot = jest.fn();
    render(Responsive, {
      props: {
        breakpoints: [
          { name: 'small', boundaries: [0, 764] },
          { name: 'medium', boundaries: [765, 1192] },
          { name: 'large', boundaries: [1193, 1672] },
        ],
      },
      slots: {
        default: slot,
      },
    });

    // The resizing functionality is async
    await waitFor(() => {
      expect(slot).toHaveBeenCalledWith({ currentBoundaryName: 'large' });
    });
  });
});
