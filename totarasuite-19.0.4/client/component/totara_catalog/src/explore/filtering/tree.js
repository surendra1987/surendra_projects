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
 * @module totara_catalog
 */

export function listToTree(options) {
  const byId = new Map();

  options = options.map(x => {
    // eslint-disable-next-line no-unused-vars
    const { children, ...fields } = x;
    return fields;
  });

  for (const node of options) {
    byId.set(node.id, node);
  }

  const parentless = [];

  for (const node of options) {
    if (node.parentid == null) {
      parentless.push(node);
    } else {
      const parent = byId.get(node.parentid);
      if (parent) {
        if (!parent.children) {
          parent.children = [];
        }
        parent.children.push(node);
      } else {
        console.warn(
          `could not find parent ${node.parentid} of node ${node.id}`
        );
      }
    }
  }

  return parentless;
}
