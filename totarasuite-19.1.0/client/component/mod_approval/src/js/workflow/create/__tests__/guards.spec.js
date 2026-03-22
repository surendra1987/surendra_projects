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

import { needsAssignment } from '../guards.js';

describe('mod approval guards', () => {
  it('Check assignment', () => {
    let context = {
      selectedFormId: 10,
      getActiveForms: {
        mod_approval_get_active_forms: {
          items: [
            { id: 10, title: 'correct', needs_assignment_selection: true },
            { id: 1, title: 'early', needs_assignment_selection: false },
            { id: 55, title: 'blah', needs_assignment_selection: true },
            { id: 105, title: 'nah', needs_assignment_selection: false },
          ],
        },
      },
    };
    expect(needsAssignment(context)).toBeTrue();

    context.selectedFormId = 105;
    expect(needsAssignment(context)).toBeFalse();

    context.selectedFormId = '105';
    expect(needsAssignment(context)).toBeFalse();

    context.selectedFormId = '10';
    expect(needsAssignment(context)).toBeTrue();
  });
});
