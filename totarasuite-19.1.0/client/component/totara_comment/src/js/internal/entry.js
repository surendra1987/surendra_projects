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
 * @module totara_comment
 */

import { addTypePolicies } from 'tui/apollo/client';

addTypePolicies({
  totara_comment_comment: {
    fields: {
      interactor: { merge: true },
    },
  },
  totara_comment_reply: {
    fields: {
      interactor: { merge: true },
    },
  },
});
