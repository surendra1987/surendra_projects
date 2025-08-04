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
 * @module container_workspace
 */

import { addTypePolicies } from 'tui/apollo/client';

addTypePolicies({
  container_workspace_workspace: {
    fields: {
      interactor: { merge: true },
    },
  },
  container_workspace_member: {
    fields: {
      workspace_interactor: { merge: true },
      member_interactor: { merge: true },
    },
  },
  container_workspace_member_request: {
    fields: {
      workspace_interactor: { merge: true },
    },
  },
  container_workspace_discussion: {
    fields: {
      discussion_interactor: { merge: true },
    },
  },
});
