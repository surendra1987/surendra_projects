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

import { navRootSort } from '../samples';
import { sortChildren } from '../samples';

describe('Samples navigation sort rules', () => {
  it('should sort the root nav with a specific sort order', async () => {
    const actual = [
      { id: 'not specified' },
      { id: 'components' },
      { id: 'plugins' },
      { id: 'foundations' },
    ];

    actual.sort(navRootSort);
    expect(actual[0].id).toEqual('foundations');
    expect(actual[1].id).toEqual('components');
    expect(actual[2].id).toEqual('plugins');
    expect(actual[3].id).toEqual('not specified');
  });

  it('should recursively sort folders and pages alphabetically', () => {
    const testTree = [
      { label: 'a1p1', type: 'page' },
      {
        label: 'a1f1',
        type: 'folder',
        children: [
          { label: 'a2p2', type: 'page' },
          { label: 'a2f2', type: 'folder', children: [] },
          { label: 'c2p2', type: 'page' },
          { label: 'b2p2', type: 'page' },
        ],
      },
      { label: 'b1p1', type: 'page' },
      { label: 'b1f1', type: 'folder', children: [] },
    ];

    const actual = sortChildren(testTree);
    expect(actual[0].label).toEqual('a1f1');
    expect(actual[1].label).toEqual('a1p1');
    expect(actual[2].label).toEqual('b1f1');
    expect(actual[0].children[0].label).toEqual('a2f2');
    expect(actual[0].children[1].label).toEqual('a2p2');
    expect(actual[0].children[2].label).toEqual('b2p2');
  });
});
