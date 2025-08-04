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
 * @author Brian Barnes <brian.barnes@totara.com>
 * @module core_course
 */

import { shallowMount } from '@vue/test-utils';
import CategoryTree from '../CategoryTree';

describe('CategoryTree', () => {
  it('should build the category tree', () => {
    function addAddtionalData(category) {
      return Object.assign(category, {
        children: [],
        label: category.name,
        content: {},
      });
    }

    let tree = shallowMount(CategoryTree);

    let categoryList = [
      { id: '1', name: 'parent', parent: null },
      { id: '2', name: 'another parent', parent: null },
    ];
    // only one level of categories
    let expectedCategoryList = categoryList.map(addAddtionalData);
    expect(tree.vm.buildCategoryTree(categoryList, null)).toEqual(
      expectedCategoryList
    );

    // 2 levels of categories
    let newRecord = {
      id: '3',
      name: 'subcategory',
      parent: { name: 'parent', id: '1' },
    };
    categoryList.push(newRecord);
    expectedCategoryList[0].children.push(addAddtionalData(newRecord));

    newRecord = {
      id: '4',
      name: 'subcategory 2',
      parent: { name: 'parent', id: '1' },
    };
    categoryList.push(newRecord);
    expectedCategoryList[0].children.push(addAddtionalData(newRecord));
    expect(tree.vm.buildCategoryTree(categoryList, null)).toEqual(
      expectedCategoryList
    );

    // three categories
    newRecord = {
      id: '5',
      name: 'subcategory 2',
      parent: { name: 'parent', id: '3' },
    };
    categoryList.push(newRecord);
    expectedCategoryList[0].children[0].children.push(
      addAddtionalData(newRecord)
    );
    expect(tree.vm.buildCategoryTree(categoryList, null)).toEqual(
      expectedCategoryList
    );
  });
});
