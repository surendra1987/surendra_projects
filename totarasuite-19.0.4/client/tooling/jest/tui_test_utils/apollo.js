/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @module tui_test_utils
 */

import { ApolloClient, InMemoryCache } from '@apollo/client/core';
import { createApolloProvider } from '@vue/apollo-option';
import { MockLink } from '@apollo/client/testing/core';

class CustomMockLink extends MockLink {
  /**
   * @param {import('@apollo/client').Operation} operation 
   */
  request(operation) {
    // Vue reactivity interferes with matching
    operation.variables = JSON.parse(JSON.stringify(operation.variables));
    return super.request(operation);
  }
}

export function createMockProvider(mocks) {
  const cache = new InMemoryCache({
    addTypename: false,
    freezeResults: true,
  });

  const link = new CustomMockLink(mocks);

  const client = new ApolloClient({
    link,
    cache,
    assumeImmutableResults: true,
    resolvers: {},
  });

  return createApolloProvider({
    defaultClient: client,
  });
}
