/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @module engage_survey
 */

import SurveyResultBody from '../SurveyResultBody';
import { render, screen } from 'tui_test_utils/vtl';

function factory() {
  return render(SurveyResultBody, {
    props: {
      name: '',
      questions: [{ votes: 5, id: 5, options: [], answertype: 'blah' }],
      access: 'PUBLIC',
      labelId: 'lbl',
      resourceId: '5',
      url: '/url',
    },
  });
}

describe('Engage SurveyCardBody', () => {
  it('displayName works as expected', async () => {
    const view = factory();
    expect(screen.getByTestId('title').textContent).toBe('');

    let name = 'short name';
    await view.rerender({ name });
    expect(screen.getByTestId('title').textContent).toBe(name);

    name = 'longer name that should be logn enough to trigger a larger value';
    await view.rerender({
      name,
    });
    const displayName = screen.getByTestId('title').textContent;
    expect(displayName).toContain(name.slice(0, 30));
    expect(displayName).toContain(String.fromCharCode(8230));
    expect(displayName).not.toContain(name.slice(35, 45));
  });
});
