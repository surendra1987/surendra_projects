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
 * @author Brian Barnes <brian.barnes@totara.com>
 * @module tui
 */

import ConfirmationModal from '../ConfirmationModal';
import { render, screen } from 'tui_test_utils/vtl';

describe('ModalConfirmation', () => {
  it('is accessible', async () => {
    const view = render(ConfirmationModal, {
      props: { title: 'Modal Title' },
      slots: { default: 'Confirmation message' },
    });
    await view.rerender({ open: true });
    expect(screen.getByRole('dialog')).toBeInTheDocument();
    expect(screen.getByText('Modal Title')).toBeInTheDocument();
    expect(screen.getByText('Confirmation message')).toBeInTheDocument();
  });
});
