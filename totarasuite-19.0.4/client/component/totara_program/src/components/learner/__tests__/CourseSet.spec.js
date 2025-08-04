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
 * Please contact [licensing@totaralearning.com] for more information.
 *
 * @author Brian Barnes <brian.barnes@totaralearning.com>
 * @module totara_program
 */

import CourseSet from '../CourseSet';
import { render, fireEvent, screen } from 'tui_test_utils/vtl';

describe('CourseSet', () => {
  it('Can navigate to course', async () => {
    const setLocation = jest.fn();
    Object.defineProperty(window, 'location', {
      set: setLocation,
      configurable: true,
    });

    render(CourseSet, {
      props: {
        courses: [
          {
            image: 'blah.jpg',
            launchURL: 'https://example.com/course1',
            fullname: 'testing course 1',
            score: 5,
            progress: 74,
            can_mark_complete: true,
            is_complete: false,
            completeURL: 'http://example.com',
          },
          {
            image: 'blah.jpg',
            fullname: 'testing course 2',
            progress: 74,
            can_mark_complete: true,
            is_complete: false,
            completeURL: 'http://example.com',
          },
        ],
      },
    });
    const launchButton = screen.queryByRole('button', {
      name: /launchcoursex, totara_program, "testing course 1"/,
    });
    expect(launchButton).toBeInTheDocument();
    expect(
      screen.queryByRole('button', {
        name: /launchcoursex, totara_program, "testing course 2"/,
      })
    ).not.toBeInTheDocument();
    await fireEvent.click(launchButton);
    expect(setLocation).toHaveBeenCalledWith('https://example.com/course1');
  });
});
