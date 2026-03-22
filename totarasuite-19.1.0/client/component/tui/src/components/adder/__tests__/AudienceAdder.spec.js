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
 * @author Kevin Hottinger <kevin.hottinger@totaralearning.com>
 * @module tui
 */

import { mount } from '@vue/test-utils';
import AudienceAdder from '../AudienceAdder';
import { axe } from 'jest-axe';

function factory() {
  return mount(AudienceAdder, {
    props: {
      open: true,
      existingItems: [1, 2],
    },
    global: {
      mocks: {
        $apollo: {
          addSmartQuery: function() {},
          loading: false,
        },
        audiences: function() {
          return { items: [] };
        },
      },
      stubs: ['CloseButton', 'FilterBar'],
    },
  });
}

describe('AudienceAdder', () => {
  it('should not have any accessibility violations', async () => {
    const wrapper = factory();
    const results = await axe(wrapper.element, {
      rules: {
        region: { enabled: false },
      },
    });
    expect(results).toHaveNoViolations();
  });
});
