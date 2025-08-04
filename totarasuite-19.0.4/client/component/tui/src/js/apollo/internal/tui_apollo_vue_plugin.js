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
 * @module tui
 */

import { DefaultApolloClient } from '@vue/apollo-composable';
import { createApolloProvider } from '@vue/apollo-option';
import { vueApolloErrorHandler } from '../../errors';
import apolloClient from '../client';

export default {
  install(app) {
    // Options API
    const apolloProvider = createApolloProvider({
      defaultClient: apolloClient,
      errorHandler: vueApolloErrorHandler,
    });
    apolloProvider.install(app);

    // Composition API
    app.provide(DefaultApolloClient, apolloClient);
  },
};
