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
 * Please contact [licensing@totara.com] for more information.
 *
 * @author Jack Humphrey <jack.humphrey@totara.com>
 * @module container_workspace
 */

import WorkspaceForm from '../WorkspaceForm';
import { render, fireEvent, screen, waitFor } from 'tui_test_utils/vtl';

// GraphQL queries
import findTopics from 'totara_topic/graphql/find_topics';
import getTopicConfig from 'totara_topic/graphql/get_config';

describe('WorkspaceForm', () => {
  it('creates or edits a workspace', async () => {
    const view = render(WorkspaceForm, {
      props: {
        workspaceName: 'Hi',
      },
      data() {
        return {
          editorReady: true,
        };
      },
      global: { stubs: ['SpaceImagePicker', 'Weka'] },
      mockQueries: [
        {
          request: {
            query: getTopicConfig,
            variables: {
              component: 'container_workspace',
              item_type: 'course',
            },
          },
          result: {
            data: {
              config: {
                __typename: 'totara_topic_get_config',
                enabled: true,
              },
            },
          },
        },
        {
          request: {
            query: findTopics,
            variables: { search: '', exclude: [] },
          },
          result: {
            data: {
              topics: [],
            },
          },
        },
        {
          request: {
            query: findTopics,
            variables: { search: 'i', exclude: [] },
          },
          result: {
            data: {
              topics: [
                {
                  __typename: 'totara_topic_topic',
                  id: '1',
                  value: 'yui',
                  catalog: 'catalog_fts=yui',
                },
                {
                  __typename: 'totara_topic_topic',
                  id: '2',
                  value: 'poi',
                  catalog: 'catalog_fts=poi',
                },
              ],
            },
          },
        },
        {
          request: {
            query: findTopics,
            variables: { search: 'i', exclude: ['2'] },
          },
          result: {
            data: {
              topics: [
                {
                  __typename: 'totara_topic_topic',
                  id: '1',
                  value: 'yui',
                  catalog: 'catalog_fts=yui',
                },
              ],
            },
          },
        },
      ],
    });

    await fireEvent.update(
      screen.getByRole('textbox', {
        name: /space_name_label, container_workspace/,
      }),
      'New workspace name'
    );

    await waitFor(() => {
      expect(
        screen.getByRole('button', { name: /tag_list, totara_core/ })
      ).toBeInTheDocument();
    });

    await fireEvent.click(
      screen.getByRole('button', { name: /tag_list, totara_core/ })
    );

    await fireEvent.update(
      screen.getByRole('textbox', { name: /tag_list, totara_core/ }),
      'i'
    );
    await waitFor(() => {
      expect(screen.getByText('poi')).toBeInTheDocument();
    });

    await fireEvent.click(screen.getByRole('menuitem', { name: /poi/ }));

    await fireEvent.click(screen.getByRole('button', { name: /submit/ }));

    expect(view.emitted('submit')).toEqual([
      [
        {
          name: 'New workspace name',
          description: null,
          descriptionFormat: 5,
          draftId: null,
          isHidden: false,
          isPrivate: false,
          tags: ['2'],
        },
      ],
    ]);
  });
});
