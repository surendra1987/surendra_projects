/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author James Magness <james.magness@totara.com>
 * @module samples
 */

import { defineAsyncComponent, markRaw } from 'vue';
import { memoize } from 'tui/util';

const prefix = 'samples/components/samples/';

/**
 * Convert lowercase directory to capatalized menu heading
 *
 * @param {String} string
 * @return {String}
 */
const capitalizeFirstLetter = string => {
  return string.charAt(0).toUpperCase() + string.toLowerCase().slice(1);
};

const navRootSortOrder = ['foundations', 'components', 'plugins'];

/**
 * Sort the top level of the navigation with a specific order
 *
 * @param {Object} a
 * @param {Object} b
 * @return {Int}
 */
export const navRootSort = (a, b) => {
  const aIndex =
    navRootSortOrder.indexOf(a.id) !== -1
      ? navRootSortOrder.indexOf(a.id)
      : 999;
  const bIndex =
    navRootSortOrder.indexOf(b.id) !== -1
      ? navRootSortOrder.indexOf(b.id)
      : 999;
  return aIndex - bIndex;
};

/**
 * Recursive sort function to alphabetically sort pages and folders
 * after they have been extracted from the flat component list.
 *
 * @param {object} items
 * @return {object}
 */
export const sortChildren = function(items) {
  for (let ii = 0; ii < items.length; ii += 1) {
    if (items[ii]?.type === 'folder') {
      items[ii].children = sortChildren(items[ii].children);
    }
  }
  return items.sort((a, b) => {
    return a.label.localeCompare(b.label);
  });
};

export const wrapSampleComponent = memoize(sample => {
  return markRaw(
    defineAsyncComponent({
      loader: async () => {
        if (sample.tuiComponent) {
          await tui
            // eslint-disable-next-line tui/no-tui-internal
            ._loadTuiComponent(sample.tuiComponent);
        }
        return tui.loadComponent(sample.component);
      },
      errorComponent: tui.defaultExport(
        tui.require('tui/components/errors/ErrorPageRender')
      ),
    })
  );
});

export function getSamples() {
  const list = tui
    // eslint-disable-next-line tui/no-tui-internal
    ._getLoadedComponentModules('samples')
    .filter(x => x.startsWith(prefix))
    .map(x => {
      const parts = x.slice(prefix.length).split('/');
      return {
        type: 'page',
        component: x,
        tuiComponent: parts[0].toLowerCase() === 'plugins' ? parts[1] : null,
        parts,
        label: parts.at(-1),
        id: x.slice(prefix.length),
      };
    });

  // build tree
  const tree = { children: [] };
  for (const item of list) {
    const parts = item.parts.slice(0, -1);
    let lastNode = tree;
    for (let i = 0; i < parts.length; i++) {
      const id = parts.slice(0, i + 1).join('/');
      let node = lastNode.children.find(
        x => x.type === 'folder' && x.id === id
      );
      if (!node) {
        node = {
          type: 'folder',
          id,
          label: capitalizeFirstLetter(parts[i]),
          children: [],
        };
        lastNode.children.push(node);
      }
      lastNode = node;
    }

    lastNode.children.push({
      ...item,
      selectable: true,
      children: [],
    });
  }

  // Sort top level nav (root)
  tree.children.sort(navRootSort);

  // Recursively sort all direct of root folders
  tree.children = tree.children.map(parent => {
    if (parent.type === 'folder') {
      parent.children = sortChildren(parent.children);
    }
    return parent;
  });

  return {
    list,
    tree: tree.children,
  };
}
