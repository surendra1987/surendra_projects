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
 * @module mod_approval
 */

import { addTypePolicies } from 'tui/apollo/client';

addTypePolicies({
  mod_approval_application: {
    fields: {
      assignment: { merge: true },
      current_state: { merge: true },
      interactor: { merge: true },
      page_urls: { merge: true },
    },
  },
  mod_approval_workflow: {
    fields: {
      interactor: { merge: true },
    },
  },
  mod_approval_workflow_stage: {
    fields: {
      type: { merge: false },
    },
  },
});
