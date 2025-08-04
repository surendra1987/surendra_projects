/**
 * This file is part of Totara Enterprise Extensions.
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Alvin Smith <alvin.smith@totaralearning.com>
 * @module container_workspace
 */

import LibraryTab from '../WorkspaceLibraryTab';
import { shallowMount } from '@vue/test-utils';
import { render, screen, waitFor } from 'tui_test_utils/vtl';
import getWorkspaceInteractor from 'container_workspace/graphql/workspace_interactor';
import sharedCards from 'container_workspace/graphql/shared_cards';

jest.mock('tui/config', () => ({
  config: {
    theme: { name: 'test' },
  },
}));

describe('WorkspaceLibraryTab', () => {
  it('Contribution card is displayed when a user can contribute', () => {
    let tab = shallowMount(LibraryTab, {
      props: {
        workspaceId: 5,
        units: 3,
        gridDirection: 'horizontal',
      },
      data() {
        return {
          interactor: {
            can_share_resources: true,
          },
          contribution: {
            cursor: 1234,
            cards: [],
          },
        };
      },
      global: {
        mocks: {
          $apollo: { loading: false },
        },
      },
    });

    const addCard = {
      instanceid: 5,
      component: 'WorkspaceContributeCard',
      tuicomponent:
        'container_workspace/components/card/WorkspaceContributeCard',
      // Populate all the default data.
      name: '',
      user: {},
    };

    // User can contribute
    expect(tab.vm.displayCards.length).toEqual(1);
    expect(tab.vm.displayCards[0]).toMatchObject(addCard);

    // User can't contribute
    tab.vm.interactor.can_share_resources = false;
    expect(tab.vm.displayCards.length).toEqual(0);

    // can't contribute with one card
    const card = {
      id: 123,
      component: 'someJunk',
    };
    tab.vm.contribution.cards.push(card);
    expect(tab.vm.displayCards[0]).toEqual(card);
    expect(tab.vm.displayCards.length).toEqual(1);

    // and add back in the contribution ability
    tab.vm.interactor.can_share_resources = true;
    expect(tab.vm.displayCards.length).toEqual(2);
    expect(tab.vm.displayCards[0]).toMatchObject(addCard);
    expect(tab.vm.displayCards[1]).toEqual(card);
  });

  it('Item count is correct', async () => {
    render(LibraryTab, {
      props: {
        workspaceId: 5,
        units: 3,
        gridDirection: 'horizontal',
        isLoadMoreVisible: true,
        filterValue: { sort: 5 },
      },
      data() {
        return {
          interactor: {
            can_share_resources: true,
          },
          contribution: {
            cursor: 1234,
            cards: [{}, {}],
          },
          $apollo: { loading: false },
        };
      },
      global: {
        stubs: { ContributionFilter: true },
      },
      mockQueries: [
        {
          request: {
            query: sharedCards,
            variables: {
              access: null,
              type: null,
              topic: null,
              sort: 5,
              section: null,
              search: null,
              workspace_id: 5,
              area: 'library',
              include_footnotes: true,
              footnotes_type: 'shared',
              footnotes_item_id: 5,
              footnotes_area: 'library',
              footnotes_component: 'container_workspace',
              source: 'ws.5.1',
              theme: 'ventura',
            },
          },
          result: {
            data: {
              contribution: {
                cursor: {
                  total: 22,
                  next: 'eyJsaW1pdCI6MjAsInBhZ2UiOjJ9',
                },
                cards: [
                  {
                    instanceid: '65',
                    name: 'orphaned',
                    summary: null,
                    component: 'engage_course',
                    tuicomponent: 'engage_course/components/card/CourseCard',
                    access: 'PUBLIC',
                    comments: 0,
                    reactions: 0,
                    timecreated: 'Created 4 days ago.',
                    sharedbycount: 0,
                    bookmarked: false,
                    extra: '',
                    owned: true,
                    user: {
                      id: '2',
                      fullname: 'Admin User',
                      profileimageurl: 'http://example.com',
                      profileimagealt: 'Admin User',
                    },
                    topics: [],
                    footnotes: [
                      {
                        component: 'CardSharedByFootnote',
                        tuicomponent:
                          'totara_engage/components/card/footnote/SharedByFootnote',
                        props: '',
                      },
                    ],
                    url: 'http://example.com',
                    interactor: { can_bookmark: false },
                  },
                  {
                    instanceid: '64',
                    name: 'Pathway',
                    summary: null,
                    component: 'engage_course',
                    tuicomponent: 'engage_course/components/card/CourseCard',
                    access: 'PUBLIC',
                    comments: 0,
                    reactions: 0,
                    timecreated: 'Created 4 days ago.',
                    sharedbycount: 0,
                    bookmarked: false,
                    extra: '',
                    owned: true,
                    user: {
                      id: '2',
                      fullname: 'Admin User',
                      profileimageurl: 'http://example.com',
                      profileimagealt: 'Admin User',
                    },
                    topics: [],
                    footnotes: [
                      {
                        component: 'CardSharedByFootnote',
                        tuicomponent:
                          'totara_engage/components/card/footnote/SharedByFootnote',
                        props: '',
                      },
                    ],
                    url: 'http://example.com',
                    interactor: { can_bookmark: false },
                  },
                  {
                    instanceid: '35',
                    name: 'Auto 29',
                    summary: null,
                    component: 'engage_article',
                    tuicomponent: 'engage_article/components/card/ArticleCard',
                    access: 'PUBLIC',
                    comments: 0,
                    reactions: 0,
                    timecreated: 'Created 6 days ago.',
                    sharedbycount: 0,
                    bookmarked: false,
                    extra: '',
                    owned: true,
                    user: {
                      id: '2',
                      fullname: 'Admin User',
                      profileimageurl: 'http://example.com',
                      profileimagealt: 'Admin User',
                    },
                    topics: [{ id: '1', value: 'One' }],
                    footnotes: [
                      {
                        component: 'CardSharedByFootnote',
                        tuicomponent:
                          'totara_engage/components/card/footnote/SharedByFootnote',
                        props: '',
                      },
                    ],
                    url: 'http://example.com',
                    interactor: { can_bookmark: false },
                  },
                ],
              },
            },
          },
        },
        {
          request: {
            query: getWorkspaceInteractor,
            variables: { workspace_id: 5 },
          },
          result: {
            data: {
              interactor: {
                __typename: 'container_workspace_workspace_interactor',
                can_delete: true,
                can_update: true,
                can_add_members: true,
                can_invite: true,
                can_join: false,
                can_leave: false,
                cannot_leave_reason: 'IS_OWNER',
                joined: true,
                workspaces_admin: true,
                own: true,
                can_request_to_join: false,
                has_requested_to_join: false,
                can_view: true,
                can_view_discussions: true,
                can_create_discussions: true,
                can_view_library: true,
                can_view_members: true,
                can_view_member_requests: false,
                can_share_resources: true,
                can_unshare_resources: true,
                can_add_audiences: true,
                muted: false,
                has_seen: true,
                user: {
                  __typename: 'core_user',
                  id: '2',
                  fullname: 'Admin User',
                  profileurl:
                    'http://172.30.252.255:8081/development/integration/server/user/profile.php',
                  profileimagealt: 'Admin User',
                  profileimageurl:
                    'http://172.30.252.255:8081/development/integration/server/theme/image.php?theme=inspire&component=core&image=u%2Ff1',
                },
              },
            },
          },
        },
      ],
    });

    await waitFor(() => {
      expect(
        screen.getByText(/itemscount, totara_engage, 22/)
      ).toBeInTheDocument();
    });
  });
});
