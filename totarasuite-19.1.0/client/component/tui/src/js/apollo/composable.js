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

import { isRef, watch } from 'vue';
import { useQuery as apolloUseQuery } from '@vue/apollo-composable';

export {
  useMutation,
  useQueryLoading,
  useGlobalQueryLoading,
  useMutationLoading,
  useGlobalMutationLoading,
  useApolloClient,
} from '@vue/apollo-composable';

/** @type {apolloUseQuery} */
export function useQuery(document, variables, options = {}) {
  const result = apolloUseQuery(document, variables, options);

  // implement throwOnError functionality so query errors can be handled by default application error handler
  watch(
    () => result.error.value,
    error => {
      if (error && (getOptionsValue(options).throwOnError ?? true)) {
        throw error;
      }
    }
  );

  return result;
}

function getOptionsValue(options) {
  if (isRef(options)) {
    return options.value;
  } else if (typeof options === 'function') {
    return options();
  } else {
    return options;
  }
}
