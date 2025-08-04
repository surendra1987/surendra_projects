import { h } from 'vue';
import FilterBar from '../FilterBar';
import { fireEvent, render, screen, waitFor } from 'tui_test_utils/vtl';

const filters = [
  {
    key: 'a',
    title: 'a filter',
    type: 'single',
    options: [
      { id: 1, label: 'One' },
      { id: 2, label: 'Two' },
    ],
  },
];

describe('FilterBar', () => {
  it('accepts and emits a flat object of filters', async () => {
    const value = {
      search: 'foo',
      a: 1,
    };
    const onUpdate = jest.fn();
    const view = render(FilterBar, {
      props: { filters, value, 'onUpdate:value': onUpdate },
      global: {
        stubs: {
          Popover: {
            render() {
              return h('div', this.$slots.default());
            },
          },
        },
      },
    });

    expect(
      screen.getByRole('searchbox', { name: /search_the_catalogue/i }).value
    ).toBe('foo');
    await fireEvent.click(
      screen.getByRole('button', { name: /additional_filters/i })
    );
    await waitFor(() => {
      expect(screen.getByRole('combobox', { name: /a filter/i }).value).toBe(
        '1'
      );
    });

    await fireEvent.update(
      screen.getByRole('searchbox', { name: /search_the_catalogue/i }),
      'bar'
    );

    expect(onUpdate).toHaveBeenCalledWith({ search: 'bar', a: 1 });
    await view.rerender({
      value: { search: 'bar', a: 1 },
    });

    await fireEvent.update(
      screen.getByRole('combobox', { name: /a filter/i }),
      '2'
    );
    expect(onUpdate).toHaveBeenCalledWith({ search: 'bar', a: 2 });
  });
});
