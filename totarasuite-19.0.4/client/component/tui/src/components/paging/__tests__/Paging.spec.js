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
 * @module tui
 */

import Paging from '../Paging';
import { fireEvent, render, screen } from 'tui_test_utils/vtl';
import { axe } from 'jest-axe';

function getVisiblePageLinks() {
  return screen
    .getAllByRole('button', { name: /pagea, core/ })
    .map(x => Number(x.textContent));
}

const getPageNumber = view =>
  Number(view.container.querySelector('[aria-current="page"]').textContent);

describe('Paging', () => {
  it('should not have any accessibility violations', async () => {
    const view = render(Paging, {
      props: { page: 5, totalItems: 1000 },
    });
    const results = await axe(view.container);
    expect(results).toHaveNoViolations();
  });

  it('renders links to the first and last pages, and two to the side', async () => {
    const view = render(Paging, {
      props: { page: 20, totalItems: 1000 },
    });

    expect(getPageNumber(view)).toBe(20);
    expect(getVisiblePageLinks()).toEqual([1, 18, 19, 21, 22, 100]);

    await view.rerender({ page: 1 });
    expect(getPageNumber(view)).toBe(1);
    expect(getVisiblePageLinks()).toEqual([2, 3, 4, 5, 6, 7, 100]);

    await view.rerender({ page: 99 });
    expect(getPageNumber(view)).toBe(99);
    expect(getVisiblePageLinks()).toEqual([1, 94, 95, 96, 97, 98, 100]);
  });

  it('allows changing pages by clicking on them', async () => {
    const view = render(Paging, {
      props: { page: 20, totalItems: 1000 },
    });

    expect(getPageNumber(view)).toBe(20);
    expect(getVisiblePageLinks()).toEqual([1, 18, 19, 21, 22, 100]);

    // switch to page 22
    fireEvent.click(screen.getByRole('button', { name: /pagea, core, "22"/ }));
    expect(view.emitted('page-change')).toEqual([[22]]);
    await view.rerender({ page: 22 });
    expect(getPageNumber(view)).toBe(22);
    expect(getVisiblePageLinks()).toEqual([1, 20, 21, 23, 24, 100]);
  });

  it('allows navigating directly to a page', async () => {
    const view = render(Paging, {
      props: { page: 1, totalItems: 1000 },
    });

    async function goTo(page) {
      await fireEvent.update(
        screen.getByRole('textbox', { name: /page, core/ }),
        page.toString()
      );
      await fireEvent.click(screen.getByRole('button', { name: /gotopage/ }));
    }

    // regular page
    await goTo(55);
    expect(view.emitted('page-change')).toEqual([[55]]);

    // outside of range -- clamped
    await goTo(0);
    expect(view.emitted('page-change')).toEqual([[55], [1]]);
    await goTo(100000);
    expect(view.emitted('page-change')).toEqual([[55], [1], [100]]);
  });

  it('allows changing the number of items per page', async () => {
    const view = render(Paging, {
      props: { page: 1, totalItems: 1000 },
    });

    await fireEvent.update(
      screen.getByRole('combobox', { name: /itemsperpage/ }),
      100
    );

    expect(view.emitted('count-change')).toEqual([[100]]);
    await view.rerender({ itemsPerPage: 100 });
    expect(getVisiblePageLinks()).toEqual([2, 3, 4, 5, 6, 7, 10]);
  });
});
