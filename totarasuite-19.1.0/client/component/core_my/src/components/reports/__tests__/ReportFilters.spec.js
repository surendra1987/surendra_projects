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
 * @module core_my
 */

import ReportFilters from '../ReportFilters';
import { fireEvent, render, screen } from 'tui_test_utils/vtl';
import { TenantFilterValue } from 'core_my/constants';

describe('ReportFilters', () => {
  it('does not render anything if there is only one tenant choice', async () => {
    const view = render(ReportFilters, {
      props: {
        filters: {},
        tenants: [{ id: 1, name: 'David' }],
      },
    });

    expect(view.baseElement.textContent).toBe('');
  });

  it('emits chosen tenant', async () => {
    const view = render(ReportFilters, {
      props: {
        filters: {},
        tenants: [
          { id: TenantFilterValue.NONE, name: 'None' },
          { id: 1, name: 'David' },
        ],
      },
    });

    await fireEvent.update(
      screen.getByRole('combobox', { name: /filter_reports/ }),
      1
    );

    expect(view.emitted('update:filters')).toEqual([[{ tenant: 1 }]]);
  });
});
