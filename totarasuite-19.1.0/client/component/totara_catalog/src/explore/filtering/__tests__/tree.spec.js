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

import { listToTree } from '../tree';

describe('listToTree', () => {
  it('takes a flat list and turns it into a tree', () => {
    const input = [
      { id: 3, parentid: 1 },
      { id: 4, parentid: 3 },
      { id: 5, parentid: 3 },
      { id: 1 },
      { id: 2, parentid: null },
    ];

    expect(listToTree(input)).toEqual([
      {
        id: 1,
        children: [
          {
            id: 3,
            parentid: 1,
            children: [
              { id: 4, parentid: 3 },
              { id: 5, parentid: 3 },
            ],
          },
        ],
      },
      { id: 2, parentid: null },
    ]);
  });
});
