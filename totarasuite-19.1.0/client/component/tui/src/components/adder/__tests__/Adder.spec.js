/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author Simon Chester <simon.chester@totaralearning.com>
 * @module tui
 */

import Adder from '../Adder';
// import { axe } from 'jest-axe';
import { render, fireEvent } from 'tui_test_utils/vtl';
import { h, nextTick } from 'vue';
import { unique } from 'tui/util';

async function renderAdder(options = {}) {
  function makeListSlot(name) {
    return function({ disabledItems, selectedItems, update }) {
      return h('div', [
        h(
          'div',
          { 'data-testid': name + '-adder-selection' },
          JSON.stringify(selectedItems)
        ),
        h(
          'div',
          { 'data-testid': name + '-adder-disabled' },
          JSON.stringify(disabledItems)
        ),
        h(
          'button',
          { onClick: () => update(unique([...selectedItems, 123, 456])) },
          `Select rows`
        ),
        h('button', { onClick: () => update([]) }, `Deselect`),
      ]);
    };
  }

  const result = render(Adder, {
    ...options,
    props: {
      title: 'Adder',
      ...options.props,
    },
    slots: {
      'browse-list': makeListSlot('browse'),
      'basket-list': makeListSlot('basket'),
      ...options.slots,
    },
  });

  await nextTick();

  await result.rerender({ open: true });

  return { result };
}

describe('Adder', () => {
  // TODO: is this test useful?
  it('displays content passed in via slot', async () => {
    const slots = ['notices', 'browse-filters', 'pre-list', 'browse-list'];

    const slotProps = {};

    const { result } = await renderAdder({
      slots: {
        ...Object.fromEntries(
          slots.map(name => [
            name,
            function(props) {
              slotProps[name] = props;
              return h('div', 'content-' + name);
            },
          ])
        ),
        'basket-list': '<div>content-basket-list</div>',
      },
    });

    expect(result.getByText('Adder')).toBeTruthy();

    // assert slots were rendered
    slots.forEach(slot => result.getByText('content-' + slot));
  });

  it('updates list of selected items in response to slot', async () => {
    const { result } = await renderAdder();

    expect(result.getByTestId('browse-adder-selection')).toHaveTextContent(
      '[]'
    );
    result.getByText('[[itemsselected, totara_core, 0]]');

    await fireEvent.click(result.getByText('Select rows'));

    expect(result.getByTestId('browse-adder-selection')).toHaveTextContent(
      '[123,456]'
    );
    result.getByText('[[itemsselected, totara_core, 2]]');

    await fireEvent.click(result.getByText(/adder_selection_with_count/));

    expect(result.getByTestId('basket-adder-selection')).toHaveTextContent(
      '[123,456]'
    );

    await fireEvent.click(result.getByText('Deselect'));

    expect(result.getByTestId('basket-adder-selection')).toHaveTextContent(
      '[]'
    );

    await fireEvent.click(result.getByText('[[adder_browse, totara_core]]'));

    expect(result.getByTestId('browse-adder-selection')).toHaveTextContent(
      '[]'
    );
  });

  it('emits event when selected tab is shown, passing selected ids', async () => {
    const { result } = await renderAdder();

    expect(result.emitted('selected-tab-active')).toBeFalsy();

    await fireEvent.click(result.getByText('[[adder_selection, totara_core]]'));

    expect(result.emitted('selected-tab-active')).toEqual([[[]]]);

    await fireEvent.click(result.getByText('[[adder_browse, totara_core]]'));
    await fireEvent.click(result.getByText('Select rows'));
    await fireEvent.click(result.getByText(/adder_selection_with_count/));

    expect(result.emitted('selected-tab-active')).toEqual([[[]], [[123, 456]]]);

    await fireEvent.click(result.getByText('[[add, totara_core]]'));
    expect(result.emitted('added')).toEqual([[[123, 456]]]);
  });

  it('only allows submitting when > 1 item and not loading', async () => {
    const { result } = await renderAdder();

    const getAdd = () =>
      result.getByRole('button', { name: '[[add, totara_core]]' });

    expect(getAdd()).toBeDisabled();
    await fireEvent.click(result.getByText('Select rows'));
    expect(getAdd()).not.toBeDisabled();
    await fireEvent.click(getAdd());
    expect(result.emitted('added')).toEqual([[[123, 456]]]);

    await result.rerender({ showLoadingBtn: true });
    expect(getAdd()).toBeDisabled();
  });

  it('handles already selected items', async () => {
    const { result } = await renderAdder({
      props: { existingItems: [123, 999] },
    });

    result.getByText('[[itemsselected, totara_core, 0]]');
    expect(result.getByTestId('browse-adder-selection')).toHaveTextContent(
      '[123,999]'
    );
    expect(result.getByTestId('browse-adder-disabled')).toHaveTextContent(
      '[123,999]'
    );

    await fireEvent.click(result.getByText('Select rows'));
    expect(result.getByTestId('browse-adder-selection')).toHaveTextContent(
      '[123,999,456]'
    );
    expect(result.getByTestId('browse-adder-disabled')).toHaveTextContent(
      '[123,999]'
    );
    result.getByText('[[itemsselected, totara_core, 1]]');

    await fireEvent.click(result.getByText(/adder_selection_with_count/));
    expect(result.emitted('selected-tab-active')).toEqual([[[456]]]);

    await fireEvent.click(result.getByText('[[add, totara_core]]'));
    expect(result.emitted('added')).toEqual([[[123, 999, 456]]]);
  });
});
