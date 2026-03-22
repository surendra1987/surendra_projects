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
 * @module mod_perform
 */

import { addTypePolicies } from 'tui/apollo/client';

addTypePolicies({
  mod_perform_element_plugin: {
    fields: {
      plugin_config: { merge: true },
    },
  },
  mod_perform_subject_instance_overview_item: {
    fields: {
      last_update: { merge: true },
      due: { merge: true },
    },
  },
});
