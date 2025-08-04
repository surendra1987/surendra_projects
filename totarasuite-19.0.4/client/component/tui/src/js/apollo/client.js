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
 * @author Simon Chester <simon.chester@totaralearning.com>
 * @module tui
 */

import {
  ApolloClient,
  InMemoryCache,
  ApolloLink,
  createHttpLink,
} from '@apollo/client/core';
import { BatchHttpLink } from '@apollo/client/link/batch-http';
import { createTuiContextLink } from './internal/tui_context_link';
import { createDevLink } from './internal/dev_link';
import { createErrorLink } from './internal/error_link';
import { createUnloadSuppressionLink } from './internal/unload_suppression_link';
import { config } from '../config';
import { totaraUrl, url } from '../util';

export { NetworkStatus } from '@apollo/client/core';

const lang = config.locale.totaraLangId;

const endpoint = totaraUrl('/totara/webapi/ajax.php');

const params = { lang };
// Add 'strings' query string parameter to graphql queries where we have the config option enabled
if (config.locale.debugstringids) {
  const strings = new URLSearchParams(window.location.search).get('strings');
  if (strings) {
    params.strings = strings;
  }
}

const httpLinkOptions = {
  uri: url(endpoint, params),
  credentials: 'same-origin',
  headers: {
    'X-Totara-Sesskey': config.sesskey,
  },
};

const link = ApolloLink.from([
  createTuiContextLink(),
  createDevLink(),
  createErrorLink(),
  createUnloadSuppressionLink(),
  ApolloLink.split(
    operation => operation.getContext().batch,
    new BatchHttpLink(httpLinkOptions),
    createHttpLink({
      ...httpLinkOptions,
      uri: operation =>
        url(endpoint, {
          operation: operation && operation.operationName,
          ...params,
        }),
    })
  ),
]);

const inMemoryCache = new InMemoryCache({
  addTypename: false,
  freezeResults: true,
});

const apolloClient = new ApolloClient({
  link,
  cache: inMemoryCache,
  assumeImmutableResults: true,
  resolvers: {},
  connectToDevTools: process.env.NODE_ENV === 'development',
});

// monkey patch .mutate() to add automatic refetch option
const originalMutate = apolloClient.queryManager.mutate;
apolloClient.queryManager.mutate = function(options) {
  return originalMutate
    .apply(apolloClient.queryManager, arguments)
    .then(result => {
      if (options.refetchAll) {
        apolloClient.reFetchObservableQueries();
      }
      return result;
    });
};

export default apolloClient;

export const cache = {
  /** @type {typeof inMemoryCache.modify} */
  modify(options) {
    return inMemoryCache.modify(options);
  },

  /** @type {typeof inMemoryCache.identify} */
  identify(object) {
    return inMemoryCache.identify(object);
  },
};

/**
 * @param {import('@apollo/client').TypePolicies} policies
 */
export function addTypePolicies(policies) {
  inMemoryCache.policies.addTypePolicies(policies);
}

addTypePolicies({
  core_user: {
    fields: {
      core_user_card_display: { merge: true },
    },
  },
});
