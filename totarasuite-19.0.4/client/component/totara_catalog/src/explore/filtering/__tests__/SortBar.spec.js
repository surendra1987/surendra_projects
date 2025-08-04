import SortBar from '../SortBar';
import { fireEvent, render, screen } from 'tui_test_utils/vtl';
import { copyText } from 'tui/dom/clipboard';

jest.mock('tui/dom/clipboard');

jest.mock('tui/components/popover/Popover', () => {
  const { h } = require('vue');
  return {
    render() {
      return h('div', [
        this.$slots.trigger(),
        this.$slots.default(),
        this.$slots.buttons(),
      ]);
    },
  };
});

describe('SortBar', () => {
  it('allows copying the current URL to the clipboard', async () => {
    const url = 'https://example.com/test';
    Object.defineProperty(window, 'location', {
      value: {
        href: url,
      },
      writable: true,
    });

    render(SortBar, {
      props: {},
    });

    expect(screen.getByRole('textbox', { name: /url, core/i }).value).toBe(url);

    await fireEvent.click(
      screen.getByRole('button', { name: /copy_link_to_clipboard/i })
    );

    expect(copyText).toHaveBeenCalledOnceWith(url);
  });
});
