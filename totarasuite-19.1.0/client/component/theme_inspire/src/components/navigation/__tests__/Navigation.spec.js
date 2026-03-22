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
 * @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
 * @module theme_inspire
 */

import Navigation from '../Navigation';
import { fireEvent, render, waitFor } from 'tui_test_utils/vtl';
import { axe } from 'jest-axe';
import { produce } from 'tui/immutable';

import setNavigationState from 'theme_inspire/graphql/set_navigation_state';

const mockProps = {
  menuData: [
    {
      name: 'top_link',
      linktext: 'Top link',
      parent: '',
      url: '/totara/dashboard/index.php',
      target: '',
      is_selected: false,
      customclass: null,
    },
    {
      name: 'top_container_1',
      linktext: 'Top container 1',
      parent: '',
      url: '#',
      target: '',
      is_selected: false,
      customclass: null,
    },
    {
      name: 'top_container_2',
      linktext: 'Top container 2',
      parent: '',
      url: '#',
      target: '',
      is_selected: false,
      customclass: null,
    },
    {
      name: 'child_1',
      linktext: 'Child 1',
      parent: 'top_container_1',
      url: '/totara/catalog/index.php',
      target: '',
      is_selected: false,
      customclass: null,
    },
    {
      name: 'child_2',
      linktext: 'Child 2',
      parent: 'top_container_2',
      url: '/totara/catalog/index.php',
      target: '',
      is_selected: false,
      customclass: null,
    },
    {
      name: 'child_container_1',
      linktext: 'Child container 1',
      parent: 'top_container_1',
      url: '#',
      target: '',
      is_selected: false,
      customclass: null,
    },
    {
      name: 'grandchild',
      linktext: 'Grandchild',
      parent: 'child_container_1',
      url: '/totara/plan/record/index.php',
      target: '',
      is_selected: false,
      customclass: null,
    },
  ],
  logoUrl: 'logourl',
  logoMark: 'logomarkurl',
  logoAlt: 'Totara test site',
  initialState: 'expanded',
  iconsEnabled: true,
};

async function expectCollapsedState(view) {
  const logo = view.getByRole('img', {
    name: /Totara test site/i,
  });
  const expandButton = view.getByRole('button', {
    name: /a11y_nav_expand, theme_inspire/,
  });

  expect(logo).toHaveAttribute('src', 'logomarkurl');
  expect(view.getByText(/Top link/i)).not.toBeVisible();
  expect(view.getByText(/Top container 1/i)).not.toBeVisible();
  expect(view.getByText(/Top container 2/i)).not.toBeVisible();

  expect(expandButton).toBeVisible();
  expect(expandButton).toHaveAttribute('aria-expanded', 'false');
}

async function expectExpandedState(view) {
  const logo = view.getByRole('img', {
    name: /Totara test site/i,
  });
  const collapseButton = view.getByRole('button', {
    name: /a11y_nav_collapse, theme_inspire/,
  });

  expect(logo).toHaveAttribute('src', 'logourl');
  expect(view.getByRole('link', { name: /Top link/ })).toBeVisible();
  expect(view.getByRole('button', { name: /Top container 1/ })).toBeVisible();
  expect(view.getByRole('button', { name: /Top container 2/ })).toBeVisible();
  expect(collapseButton).toBeVisible();
  expect(collapseButton).toHaveAttribute('aria-expanded', 'true');
}

async function expectOverlaidState(view) {
  const logo = view.getByRole('img', {
    name: /Totara test site/i,
  });
  const collapseButton = view.getByRole('button', {
    name: /a11y_nav_close_overlay, theme_inspire/,
  });

  expect(logo).toHaveAttribute('src', 'logourl');
  expect(view.getByRole('link', { name: /Top link/ })).toBeVisible();
  expect(view.getByRole('button', { name: /Top container 1/ })).toBeVisible();
  expect(view.getByRole('button', { name: /Top container 2/ })).toBeVisible();
  expect(collapseButton).toBeVisible();
  expect(collapseButton).toHaveAttribute('aria-expanded', 'true');
}

const setStateResult = jest.fn(() => ({ data: { result: true } }));

describe('Navigation', () => {
  it('supports expanding and collapsing the menu', async () => {
    const view = render(Navigation, {
      props: mockProps,
      mockQueries: [
        {
          request: {
            query: setNavigationState,
            variables: { input: { state: 'collapsed' } },
          },
          result: setStateResult,
        },
        {
          request: {
            query: setNavigationState,
            variables: { input: { state: 'expanded' } },
          },
          result: setStateResult,
        },
      ],
    });

    expectExpandedState(view);

    await fireEvent.click(
      view.getByRole('button', {
        name: /a11y_nav_collapse, theme_inspire/,
      })
    );

    await waitFor(() => {
      expect(setStateResult).toHaveBeenCalledTimes(1);
    });

    expectCollapsedState(view);

    await fireEvent.click(
      view.getByRole('button', {
        name: /a11y_nav_expand, theme_inspire/,
      })
    );

    await waitFor(() => {
      expect(setStateResult).toHaveBeenCalledTimes(2);
    });

    expectExpandedState(view);
  });

  it('should hide the icons when icons are disabled', async () => {
    const props = produce(mockProps, draft => {
      draft.iconsEnabled = false;
    });

    const view = render(Navigation, { props: props });

    const item = view.getByRole('link', { name: /Top link/i });
    expect(item.querySelector('svg')).not.toBeInTheDocument();

    const parentItem = view.getByRole('button', { name: /Top container 1/i });
    expect(parentItem.querySelector('svg')).not.toBeInTheDocument();
  });

  it('should hide the navigation when collapsed and icons are disabled', async () => {
    const props = produce(mockProps, draft => {
      draft.iconsEnabled = false;
    });

    const view = render(Navigation, { props: props });

    expect(view.getByRole('navigation')).toBeVisible();

    // Collapse
    await fireEvent.click(
      view.getByRole('button', {
        name: /a11y_nav_collapse, theme_inspire/,
      })
    );

    expect(view.queryByRole('navigation')).not.toBeInTheDocument();

    // Expand
    await fireEvent.click(
      view.getByRole('button', {
        name: /a11y_nav_expand, theme_inspire/,
      })
    );

    expect(view.getByRole('navigation')).toBeVisible();
  });

  it('should set the navigation to expanded mode when initial state is set to expanded', async () => {
    const props = produce(mockProps, draft => {
      draft.initialState = 'expanded';
    });

    const view = render(Navigation, { props: props });

    expectExpandedState(view);
  });

  it('should set the navigation to collapsed mode when initial state is set to collapsed', async () => {
    const props = produce(mockProps, draft => {
      draft.initialState = 'collapsed';
    });

    const view = render(Navigation, { props: props });

    expectCollapsedState(view);
  });

  it('should set the navigation to hidden mode when initial state is set to collapsed and icons are disabled', async () => {
    const props = produce(mockProps, draft => {
      draft.initialState = 'collapsed';
      draft.iconsEnabled = false;
    });

    const view = render(Navigation, { props: props });

    expect(view.queryByRole('navigation')).not.toBeInTheDocument();
  });

  it('should include the supplied custom css className', async () => {
    const props = produce(mockProps, draft => {
      draft.menuData[0].customclass = 'customclass';
    });

    const view = render(Navigation, { props: props });

    expect(
      view.getByText(/Top link/i).closest('.customclass')
    ).toBeInTheDocument();
  });

  it('should open external links when a target is provided', async () => {
    const props = produce(mockProps, draft => {
      draft.menuData[0].target = 'target';
    });

    const view = render(Navigation, { props: props });

    expect(view.getByRole('link', { name: /Top link/i })).toHaveAttribute(
      'target',
      '_blank'
    );
  });

  it('should show and hide child items when selecting a parent item', async () => {
    const view = render(Navigation, { props: mockProps });

    const parentItem = view.getByRole('button', { name: /Top container 1/i });

    expect(
      view.queryByRole('link', { name: /Child 1/i })
    ).not.toBeInTheDocument();
    expect(
      view.queryByRole('button', { name: /Child container 1/i })
    ).not.toBeInTheDocument();

    // Expand item
    await fireEvent.click(parentItem);

    expect(view.getByRole('link', { name: /Child 1/i })).toBeInTheDocument();
    expect(
      view.getByRole('button', { name: /Child container 1/i })
    ).toBeInTheDocument();

    // Collapse item
    await fireEvent.click(parentItem);

    expect(
      view.queryByRole('link', { name: /Child 1/i })
    ).not.toBeInTheDocument();
    expect(
      view.queryByRole('link', { name: /Child container 1/i })
    ).not.toBeInTheDocument();
  });

  it('should enter overlaid mode when selecting a parent item while in collapsed mode', async () => {
    const props = produce(mockProps, draft => {
      draft.initialState = 'collapsed';
    });

    const view = render(Navigation, { props: props });

    expectCollapsedState(view);

    const parentItem = view.getByRole('button', {
      name: /a11y_shownavitems, theme_inspire, "Top container 1"/,
    });

    expect(
      view.queryByRole('link', { name: /Child 1/i })
    ).not.toBeInTheDocument();
    expect(
      view.queryByRole('link', { name: /Child container 1/i })
    ).not.toBeInTheDocument();

    // Click item to enter overlaid mode
    await fireEvent.click(parentItem);

    expectOverlaidState(view);

    expect(view.getByRole('link', { name: /Child 1/i })).toBeInTheDocument();
    expect(
      view.getByRole('button', { name: /Child container 1/i })
    ).toBeInTheDocument();
  });

  it('should always expand the selected items parent', async () => {
    const props = produce(mockProps, draft => {
      draft.menuData[3].is_selected = true;
    });

    const view = render(Navigation, { props: props });

    // Check default from prop
    expect(view.getByRole('link', { name: /Child 1/i })).toBeInTheDocument();
    expect(
      view.getByRole('button', { name: /Child container 1/i })
    ).toBeInTheDocument();

    expectExpandedState(view);

    // Collapse the nav
    await fireEvent.click(
      view.getByRole('button', {
        name: /a11y_nav_collapse, theme_inspire/,
      })
    );

    expectCollapsedState(view);

    // Select another parent which expands the nav
    await fireEvent.click(
      view.getByRole('button', {
        name: /a11y_shownavitems, theme_inspire, "Top container 2"/,
      })
    );

    expectOverlaidState(view);

    // Selected item and new newly clicked item should be open
    expect(view.getByRole('link', { name: /Child 1/i })).toBeInTheDocument();
    expect(
      view.getByRole('button', { name: /Child container 1/i })
    ).toBeInTheDocument();

    expect(view.getByRole('link', { name: /Child 2/i })).toBeInTheDocument();

    // Collapsing then expanding should leave only the selected item open
    await fireEvent.click(
      view.getByRole('button', {
        name: /a11y_nav_close_overlay, theme_inspire/,
      })
    );

    await fireEvent.click(
      view.getByRole('button', {
        name: /a11y_nav_expand, theme_inspire/,
      })
    );

    // Selected item should be open and the others closed now
    expect(view.getByRole('link', { name: /Child 1/i })).toBeInTheDocument();
    expect(
      view.getByRole('button', { name: /Child container 1/i })
    ).toBeInTheDocument();

    expect(
      view.queryByRole('link', { name: /Child 2/i })
    ).not.toBeInTheDocument();
  });

  it('should support two levels of nested menu items', async () => {
    const view = render(Navigation, {
      props: mockProps,
    });

    expect(
      view.queryByRole('link', { name: /Grandchild/i })
    ).not.toBeInTheDocument();

    await fireEvent.click(
      view.getByRole('button', {
        name: /Top container 1/,
      })
    );

    await fireEvent.click(
      view.getByRole('button', {
        name: /Child container 1/,
      })
    );

    expect(view.getByRole('link', { name: /Grandchild/i })).toBeInTheDocument();
  });

  it('should not have any accessibility violations', async () => {
    const view = render(Navigation, {
      props: mockProps,
    });

    expect(await axe(view.container)).toHaveNoViolations();
  });
});
