import CatalogItemCard from '../CatalogItemCard';
import { fireEvent, render, screen } from 'tui_test_utils/vtl';

const item = {
  itemid: '22',
  featured: false,
  title: 'Ameliorated methodical portal',
  logo: '',
  image: {
    url: 'http://localhost/image.jpg',
    alt: 'Ameliorated methodical portal',
  },
  redirecturl: 'http://learn.totara81/course/view.php?id=28',
  objecttype: 'course',
  hero_data_type: 'text',
  hero_data_text: 'E-learning',
  hero_data_icon: { icon: '<span data-testid="hero-icon">E-Learning</span>' },
  description_enabled: true,
  description: '',
  progress_bar_enabled: false,
  progress_bar: null,
  text_placeholders_enabled: true,
  text_placeholders: [
    {
      data: 'Courses',
      label: null,
      show_label: null,
    },
    {
      data: 'Miscellaneous',
      label: null,
      show_label: null,
    },
  ],
  icon_placeholders_enabled: false,
  icon_placeholders: null,
};

describe('CatalogItemCard', () => {
  it('renders key item info', () => {
    render(CatalogItemCard, { props: { item } });
    // title
    expect(
      screen.getByText(/ameliorated methodical portal/i)
    ).toBeInTheDocument();
    // link
    expect(
      screen.getByRole('link', {
        name: /ameliorated methodical portal/i,
      }).href
    ).toBe(item.redirecturl);
  });

  it('renders hero info', async () => {
    const view = render(CatalogItemCard, {
      props: {
        item: {
          ...item,
          hero_data_type: null,
        },
      },
    });
    expect(screen.queryByText(/e-learning/i)).not.toBeInTheDocument();

    await view.rerender({
      item: {
        ...item,
        hero_data_type: 'text',
      },
    });
    expect(screen.getByText(/e-learning/i)).toBeInTheDocument();

    await view.rerender({
      item: {
        ...item,
        hero_data_type: 'icon',
      },
    });
    expect(screen.getByTestId('hero-icon')).toBeInTheDocument();
  });

  it('renders placeholders if present', async () => {
    const view = render(CatalogItemCard, { props: { item } });

    expect(screen.getByTestId('text-placeholders')).toBeInTheDocument();
    expect(screen.getByText(/courses/i)).toBeInTheDocument();
    expect(screen.getByText(/miscellaneous/i)).toBeInTheDocument();

    await view.rerender({
      item: {
        ...item,
        text_placeholders: [],
      },
    });

    expect(screen.queryByTestId('text-placeholders')).not.toBeInTheDocument();
    expect(screen.queryByText(/courses/i)).not.toBeInTheDocument();
    expect(screen.queryByText(/miscellaneous/i)).not.toBeInTheDocument();

    // test with labels
    await view.rerender({
      item: {
        ...item,
        text_placeholders: [
          {
            data: 'Courses',
            label: 'Learning type',
            show_label: true,
          },
        ],
      },
    });

    expect(screen.getByText(/learning type: courses/i)).toBeInTheDocument();
  });

  it('renders description', async () => {
    render(CatalogItemCard, {
      props: {
        item: {
          ...item,
          description_enabled: true,
          description: 'Potato farming',
        },
      },
    });

    expect(screen.getByText(/potato farming/i)).toBeInTheDocument();
  });

  it('renders featured label', async () => {
    const view = render(CatalogItemCard, { props: { item } });

    expect(screen.queryAllByText(/featured/i)).toBeEmpty();

    await view.rerender({
      item: {
        ...item,
        featured: true,
      },
    });

    expect(screen.getAllByText(/featured/i)).not.toBeEmpty();
  });

  it('renders progress', async () => {
    const view = render(CatalogItemCard, { props: { item } });

    expect(screen.queryByRole('progressbar')).not.toBeInTheDocument();

    await view.rerender({
      item: {
        ...item,
        progress_bar_enabled: true,
        progress_bar: {
          progress: 45,
        },
      },
    });

    expect(screen.getByRole('progressbar').getAttribute('aria-valuetext')).toBe(
      '45%'
    );
  });

  it('emits event when link is clicked', async () => {
    const onClick = jest.fn();
    render(CatalogItemCard, { props: { item, onClick } });

    await fireEvent.click(
      screen.getByRole('link', {
        name: /ameliorated methodical portal/i,
      })
    );

    expect(onClick).toHaveBeenCalledWith(expect.any(MouseEvent));
  });
});
