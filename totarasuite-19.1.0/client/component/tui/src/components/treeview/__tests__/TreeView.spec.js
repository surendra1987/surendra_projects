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
 * @module tui
 */

import TreeView from '../TreeView';
import { fireEvent, render, waitFor } from 'tui_test_utils/vtl';
import { __setString } from 'tui/i18n';
import { produce } from 'tui/immutable';

const items = [
  {
    id: 1,
    label: 'Item 1',
    selectable: false,
    children: [
      {
        id: 2,
        label: 'Item 1-1',
        selectable: true,
        children: [],
      },
    ],
  },
  {
    id: 3,
    label: 'Item 2',
    selectable: true,
    children: [
      {
        id: 4,
        label: 'Item 2-1',
        selectable: true,
        children: [
          {
            id: 5,
            label: 'Item 2-1-1',
            selectable: true,
            children: [
              {
                id: 6,
                label: 'Item 2-1-1-1',
                selectable: true,
                children: [],
              },
            ],
          },
        ],
      },
    ],
  },
];

const mockProps = {
  items: items,
  selectedItems: [],
  expandedItems: [],
  multiSelect: false,
};

__setString(
  'a11y_treeview_expand_item',
  'totara_core',
  'Show children of {$a}'
);

__setString(
  'a11y_treeview_collapse_item',
  'totara_core',
  'Hide children of {$a}'
);

__setString('a11y_treeview_select_item', 'totara_core', 'Select {$a}');

describe('TreeView', () => {
  it('expands and collapses tree items when clicking the toggle button', async () => {
    const view = render(TreeView, { props: mockProps });

    let showItem = view.queryByRole('button', {
      name: /Show children of Item 2/i,
    });
    let hideItem = view.queryByRole('button', {
      name: /Hide children of Item 2/i,
    });
    let showSubItem = view.queryByRole('button', {
      name: /Show children of Item 2-1/i,
    });
    let hideSubItem = view.queryByRole('button', {
      name: /Hide children of Item 2-1/i,
    });
    let showSubSubItem = view.queryByRole('button', {
      name: /Show children of Item 2-1-1/i,
    });

    expect(showItem).toBeInTheDocument();
    expect(hideItem).not.toBeInTheDocument();
    expect(showSubItem).not.toBeInTheDocument();
    expect(hideSubItem).not.toBeInTheDocument();
    expect(showSubSubItem).not.toBeInTheDocument();

    // Expand item
    await fireEvent.click(showItem);
    expect(view.emitted('update:expandedItems')).toEqual([[[3]]]);
    await view.rerender({ expandedItems: [3] });

    hideItem = view.queryByRole('button', {
      name: /Hide children of Item 2/i,
    });
    showSubItem = view.queryByRole('button', {
      name: /Show children of Item 2-1/i,
    });
    expect(hideItem).toBeInTheDocument();
    expect(showSubItem).toBeInTheDocument();

    // Expand sub item
    await fireEvent.click(showSubItem);
    expect(view.emitted('update:expandedItems')).toEqual([[[3]], [[3, 4]]]);
    await view.rerender({ expandedItems: [3, 4] });

    hideSubItem = view.queryByRole('button', {
      name: /Hide children of Item 2-1/i,
    });
    showSubSubItem = view.queryByRole('button', {
      name: /Show children of Item 2-1-1/i,
    });
    expect(hideSubItem).toBeInTheDocument();
    expect(showSubSubItem).toBeInTheDocument();

    // Collapse sub item
    await fireEvent.click(hideSubItem);
    expect(view.emitted('update:expandedItems')).toEqual([
      [[3]],
      [[3, 4]],
      [[3]],
    ]);
    await view.rerender({ expandedItems: [3] });

    showSubItem = view.queryByRole('button', {
      name: /Show children of Item 2-1/i,
    });
    showSubSubItem = view.queryByRole('button', {
      name: /Show children of Item 2-1-1/i,
    });
    expect(showSubItem).toBeInTheDocument();
    expect(showSubSubItem).not.toBeInTheDocument();

    // Collapse item
    await fireEvent.click(hideItem);
    expect(view.emitted('update:expandedItems')).toEqual([
      [[3]],
      [[3, 4]],
      [[3]],
      [[]],
    ]);
    await view.rerender({ expandedItems: [] });

    showItem = view.queryByRole('button', {
      name: /Show children of Item 2/i,
    });
    hideItem = view.queryByRole('button', {
      name: /Hide children of Item 2/i,
    });
    expect(showItem).toBeInTheDocument();
    expect(hideItem).not.toBeInTheDocument();
  });

  it('supports single selecting tree items', async () => {
    const view = render(TreeView, { props: mockProps });

    let item2 = view.queryByLabelText(/Select Item 2/i);
    expect(item2).toBeInTheDocument();

    let subItem = view.queryByLabelText(/Select Item 2-1/i);
    expect(subItem).not.toBeInTheDocument();

    await fireEvent.click(item2);
    expect(view.emitted('update:selectedItems')).toEqual([[[3]]]);
    await view.rerender({ selectedItems: [3] });

    // Expand
    let showItem = view.queryByRole('button', {
      name: /Show children of Item 2/i,
    });
    await fireEvent.click(showItem);
    expect(view.emitted('update:expandedItems')).toEqual([[[3]]]);
    await view.rerender({ expandedItems: [3] });

    subItem = view.queryByLabelText(/Select Item 2-1/i);
    expect(subItem).toBeInTheDocument();

    // Select sub item
    await fireEvent.click(subItem);
    expect(view.emitted('update:selectedItems')).toEqual([[[3]], [[4]]]);
    await view.rerender({ selectedItems: [4] });

    // Deselect
    await fireEvent.click(subItem);
    expect(view.emitted('update:selectedItems')).toEqual([[[3]], [[4]], [[]]]);
  });

  it('supports multi selecting tree items', async () => {
    const props = produce(mockProps, draft => {
      draft.multiSelect = true;
    });
    const view = render(TreeView, { props: props });

    let item2 = view.getByLabelText(/Select Item 2/i);
    expect(item2).toBeInTheDocument();

    let subItem = view.queryByText(/Item 2-1/i);
    expect(subItem).not.toBeInTheDocument();

    await fireEvent.click(item2);
    expect(view.emitted('update:selectedItems')).toEqual([[[3]]]);
    await view.rerender({ selectedItems: [3] });

    // Expand
    let showItem = view.queryByRole('button', {
      name: /Show children of Item 2/i,
    });
    await fireEvent.click(showItem);
    expect(view.emitted('update:expandedItems')).toEqual([[[3]]]);
    await view.rerender({ expandedItems: [3] });

    subItem = view.getByLabelText(/Select Item 2-1/i);
    expect(subItem).toBeInTheDocument();

    // Select sub item
    await fireEvent.click(subItem);
    expect(view.emitted('update:selectedItems')).toEqual([[[3]], [[3, 4]]]);
    await view.rerender({ selectedItems: [3, 4] });

    // Deselect
    await fireEvent.click(subItem);
    expect(view.emitted('update:selectedItems')).toEqual([
      [[3]],
      [[3, 4]],
      [[3]],
    ]);
    await view.rerender({ selectedItems: [3] });

    // Deselect
    await fireEvent.click(item2);
    expect(view.emitted('update:selectedItems')).toEqual([
      [[3]],
      [[3, 4]],
      [[3]],
      [[]],
    ]);
  });

  it('expands and collapses tree items that are not selectable when clicking them', async () => {
    const view = render(TreeView, { props: mockProps });
    let item1 = view.queryByText(/Item 1/i);
    expect(item1).toBeInTheDocument();

    // Clicking item 1 should expand because it's not selectable
    await fireEvent.click(item1);
    expect(view.emitted('update:expandedItems')).toEqual([[[1]]]);
    await view.rerender({ expandedItems: [1] });

    let item2 = view.queryByText(/Item 2/i);
    expect(item2).toBeInTheDocument();

    // Clicking item 2 should not expand because it is selectable
    await fireEvent.click(item2);
    expect(view.emitted('update:expandedItems')).toEqual([[[1]]]);
    await view.rerender({ expandedItems: [1] });

    // Collapse
    await fireEvent.click(item1);
    expect(view.emitted('update:expandedItems')).toEqual([[[1]], [[]]]);
  });

  it('supports full keyboard navigation and selection', async () => {
    const view = render(TreeView, { props: mockProps });
    let item1 = view.getAllByRole('treeitem')[0];
    let item2 = view.getAllByRole('treeitem')[1];
    expect(item1).toBeInTheDocument();

    // First element should be focusable by default
    item1.focus();

    await waitFor(() => {
      expect(item1.tabIndex).toBe(0);
    });

    await waitFor(() => {
      expect(item2.tabIndex).toBe(-1);
    });

    /**
     * ARROWDOWN
     *
     * Moves focus to next item
     */
    await fireEvent.keyDown(document, {
      key: 'ArrowDown',
      code: 'ArrowDown',
    });

    item1 = view.getAllByRole('treeitem')[0];
    await waitFor(() => {
      expect(item1.tabIndex).toBe(-1);
    });

    item2 = view.getAllByRole('treeitem')[1];
    await waitFor(() => {
      expect(item2.tabIndex).toBe(0);
    });

    // Arrow down again does nothing because there is no third item
    await fireEvent.keyDown(document, {
      key: 'ArrowDown',
      code: 'ArrowDown',
    });

    item1 = view.getAllByRole('treeitem')[0];
    await waitFor(() => {
      expect(item1.tabIndex).toBe(-1);
    });

    item2 = view.getAllByRole('treeitem')[1];
    await waitFor(() => {
      expect(item2.tabIndex).toBe(0);
    });

    /**
     * ARROWUP
     *
     * Moves focus to previous item
     */
    await fireEvent.keyDown(document, {
      key: 'ArrowUp',
      code: 'ArrowUp',
    });

    item1 = view.getAllByRole('treeitem')[0];
    await waitFor(() => {
      expect(item1.tabIndex).toBe(0);
    });

    item2 = view.getAllByRole('treeitem')[1];
    await waitFor(() => {
      expect(item2.tabIndex).toBe(-1);
    });

    // Arrow up again should do nothing because there's no previous item
    await fireEvent.keyDown(document, {
      key: 'ArrowUp',
      code: 'ArrowUp',
    });

    item1 = view.getAllByRole('treeitem')[0];
    await waitFor(() => {
      expect(item1.tabIndex).toBe(0);
    });

    item2 = view.getAllByRole('treeitem')[1];
    await waitFor(() => {
      expect(item2.tabIndex).toBe(-1);
    });

    /**
     * ARROWRIGHT
     *
     * Expands current item if it's collapsed, otherwise focus the next item
     */
    await item1.focus();

    // Cant see child yet
    let subItem11 = view.queryByText(/Item 1-1/i);
    await waitFor(() => {
      expect(subItem11).not.toBeInTheDocument();
    });

    // Arrow right should expand
    await fireEvent.keyDown(document, {
      key: 'ArrowRight',
      code: 'ArrowRight',
    });
    expect(view.emitted('update:expandedItems')).toEqual([[[1]]]);
    await view.rerender({ expandedItems: [1] });

    subItem11 = view.queryByText(/Item 1-1/i);
    await waitFor(() => {
      expect(subItem11).toBeInTheDocument();
    });

    item1 = view.getAllByRole('treeitem')[0];
    await waitFor(() => {
      expect(item1.tabIndex).toBe(0);
    });

    item2 = view.getAllByRole('treeitem')[1];
    await waitFor(() => {
      expect(item2.tabIndex).toBe(-1);
    });

    // Arrow right again should focus next item
    await fireEvent.keyDown(document, {
      key: 'ArrowRight',
      code: 'ArrowRight',
    });

    item1 = view.getAllByRole('treeitem')[0];
    await waitFor(() => {
      expect(item1.tabIndex).toBe(-1);
    });

    item2 = view.getAllByRole('treeitem')[1];
    await waitFor(() => {
      expect(item2.tabIndex).toBe(0);
    });

    /**
     * ARROWLEFT
     *
     * Collapses current item if it's expanded, otherwise focus the parent treeitem
     */
    await fireEvent.keyDown(document, {
      key: 'ArrowLeft',
      code: 'ArrowLeft',
    });

    item1 = view.getAllByRole('treeitem')[0];
    await waitFor(() => {
      expect(item1.tabIndex).toBe(0);
    });

    item2 = view.getAllByRole('treeitem')[1];
    await waitFor(() => {
      expect(item2.tabIndex).toBe(-1);
    });

    // Arrow left again should collapse the item
    await fireEvent.keyDown(document, {
      key: 'ArrowLeft',
      code: 'ArrowLeft',
    });
    expect(view.emitted('update:expandedItems')).toEqual([[[1]], [[]]]);
    await view.rerender({ expandedItems: [0] });

    subItem11 = view.queryByText(/Item 1-1/i);
    await waitFor(() => {
      expect(subItem11).not.toBeInTheDocument();
    });

    await fireEvent.keyDown(document, {
      key: 'ArrowDown',
      code: 'ArrowDown',
    });

    /**
     * SPACE
     *
     * Select and unselect
     */
    await fireEvent.keyDown(document, {
      key: 'Space',
      code: 'Space',
    });
    expect(view.emitted('update:selectedItems')).toEqual([[[3]]]);
    await view.rerender({ selectedItems: [3] });

    await fireEvent.keyDown(document, {
      key: 'Space',
      code: 'Space',
    });
    expect(view.emitted('update:selectedItems')).toEqual([[[3]], [[]]]);
    await view.rerender({ selectedItems: [0] });

    /**
     * ENTER
     *
     * Select and unselect
     */
    await fireEvent.keyDown(document, {
      key: 'Enter',
      code: 'Enter',
    });
    expect(view.emitted('update:selectedItems')).toEqual([[[3]], [[]], [[3]]]);
    await view.rerender({ selectedItems: [3] });

    await fireEvent.keyDown(document, {
      key: 'Space',
      code: 'Space',
    });
    expect(view.emitted('update:selectedItems')).toEqual([
      [[3]],
      [[]],
      [[3]],
      [[]],
    ]);
    await view.rerender({ selectedItems: [0] });

    /**
     * HOME
     *
     * Focuses the first item
     */
    await fireEvent.keyDown(document, {
      key: 'Home',
      code: 'Home',
    });
    item1 = view.getAllByRole('treeitem')[0];
    await waitFor(() => {
      expect(item1.tabIndex).toBe(0);
    });

    item2 = view.getAllByRole('treeitem')[1];
    await waitFor(() => {
      expect(item2.tabIndex).toBe(-1);
    });

    /**
     * END
     *
     * Focuses the last item
     */
    await fireEvent.keyDown(document, {
      key: 'Home',
      code: 'Home',
    });
    item1 = view.getAllByRole('treeitem')[0];
    await waitFor(() => {
      expect(item1.tabIndex).toBe(0);
    });

    item2 = view.getAllByRole('treeitem')[1];
    await waitFor(() => {
      expect(item2.tabIndex).toBe(-1);
    });
  });
});
