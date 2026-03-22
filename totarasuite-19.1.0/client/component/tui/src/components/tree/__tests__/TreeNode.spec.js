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
 * @module tui
 */

import { shallowMount } from '@vue/test-utils';
import TreeNode from '../TreeNode';

describe('TreeNode', () => {
  it('hasContent can handle different types effectively', async () => {
    let child = {
      id: 'Australia',
      label: 'Australia',
      children: [],
      content: {
        items: [
          {
            id: 'melbourne',
            label: 'Melbourne',
          },
          {
            id: 'sydney',
            label: 'Sydney',
          },
        ],
      },
    };

    let treeNode = shallowMount(TreeNode, {
      props: {
        label: 'a',
        content: [],
        children: [],
        openList: [],
      },
    });

    expect(treeNode.vm.hasContent).toBeFalse();
    expect(treeNode.vm.hasChildren).toBeFalse();

    await treeNode.setProps({ content: {} });
    expect(treeNode.vm.hasContent).toBeFalse();

    await treeNode.setProps({ content: { data: 5, monke: 'abc' } });
    expect(treeNode.vm.hasContent).toBeTrue();

    await treeNode.setProps({ content: null });
    expect(treeNode.vm.hasContent).toBeFalse();

    // Having children trumps any content details
    await treeNode.setProps({ content: [], children: [child, child] });
    expect(treeNode.vm.hasContent).toBeTrue();

    await treeNode.setProps({ content: {} });
    expect(treeNode.vm.hasContent).toBeTrue();

    await treeNode.setProps({ content: { data: 5, monke: 'abc' } });
    expect(treeNode.vm.hasContent).toBeTrue();

    await treeNode.setProps({ content: null });
    expect(treeNode.vm.hasContent).toBeTrue();

    treeNode = shallowMount(TreeNode, {
      props: {
        label: 'a',
        openList: [],
      },
    });
    expect(treeNode.vm.hasContent).toBeFalse();
  });
});
