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
 * @module mod_approval
 */

import { getSelectedForm } from '../selectors.js';

describe('mod approval Create selectors', () => {
  it('Check assignment', () => {
    let items = [
      { id: 10, title: 'correct', needs_assignment_selection: true },
      { id: 1, title: 'early', needs_assignment_selection: false },
      { id: 55, title: 'blah', needs_assignment_selection: true },
      { id: 105, title: 'nah', needs_assignment_selection: false },
    ];
    let context = {
      selectedFormId: undefined,
      getActiveForms: {
        mod_approval_get_active_forms: {
          items,
        },
      },
    };
    expect(getSelectedForm(context)).toBeUndefined();

    context.selectedFormId = 105;
    expect(getSelectedForm(context)).toBe(items[3]);

    context.selectedFormId = '105';
    expect(getSelectedForm(context)).toBe(items[3]);

    context.selectedFormId = '10';
    expect(getSelectedForm(context)).toBe(items[0]);
  });
});
