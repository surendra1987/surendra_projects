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
 * Please contact [licensing@totaralearning.com] for more information.
 *
 * @author Simon Chester <simon.chester@totaralearning.com>
 * @module tui_test_utils
 */

// eslint-disable-next-line tui/no-direct-testing-library-import
import { render as originalRender } from '@testing-library/vue';
import { createMockProvider } from './apollo';
import { DefaultApolloClient } from '@vue/apollo-composable';

export * from '@testing-library/vue';

/** @type {import('@testing-library/vue').render} */
export function render(TestComponent, options = {}, configure) {
  options = { ...options };

  const mockQueries = options.global?.mockQueries || options.mockQueries;

  if (mockQueries) {
    if (options.global?.mocks?.$apollo) {
      throw new Error('Cannot use mockQueries while also mocking $apollo');
    }
    const mockProvider = createMockProvider(mockQueries);
    options.global = {
      ...options.global,
      plugins: (options.global?.plugins ?? []).concat(mockProvider),
      provide: {
        [DefaultApolloClient]: mockProvider.defaultClient,
        ...options.global?.provide,
      },
    };
  }

  return originalRender(TestComponent, options, configure);
}
