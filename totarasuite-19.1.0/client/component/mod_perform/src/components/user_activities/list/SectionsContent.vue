<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2022 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Rodney Cruden-Powell <rodney.cruden-powell@totara.com>
  @module mod_perform
-->
<template>
  <div
    :key="subjectSection.section.id"
    class="tui-performUserActivityListSection"
  >
    <template v-for="key in Object.keys(relationshipTypes)">
      <Table
        v-if="
          !anonymousResponses ||
            getTableData(relationshipTypes[key].id).length > 0
        "
        :key="key"
        no-items-text=""
        :data="getTableData(relationshipTypes[key].id)"
        :border-bottom-hidden="true"
        :hover-off="true"
        :stack-at="512"
      >
        <template v-slot:header-row>
          <HeaderCell
            class="tui-performUserActivityListSection__roleHeader"
            size="12"
            valign="start"
            :is-stacked="false"
          >
            <span>
              {{ relationshipTypes[key].name }}
              <span>{{ getRoleHeaderCount(relationshipTypes[key].id) }}</span>
            </span>
          </HeaderCell>
        </template>

        <template v-slot:row="{ row }">
          <Cell size="9" valign="center">
            <ParticipantUserHeader
              :user-name="
                row.isForCurrentUser
                  ? $str('user_activities_you', 'mod_perform')
                  : row.participant.fullname
              "
              :profile-picture="row.participant.profileimageurlsmall"
              :profile-picture-alt="row.participant.profileimagealt"
              :regular-weight="!forCurrentUserInRole(row)"
              :section-modal="true"
              size="xxsmall"
            />
          </Cell>
          <Cell
            class="tui-performUserActivityListSection__stateProgressCell"
            size="3"
            :heavy="false"
            valign="center"
          >
            <span
              v-if="row.availabilityStatus == 'CLOSED'"
              class="tui-performUserActivityListSection__closed"
            >
              {{ $str('user_activities_closed', 'mod_perform') }}
            </span>

            <div class="tui-performUserActivityListSection__progress">
              <div class="tui-performUserActivityListSection__progressIcon">
                <ViewOnlyIcon
                  v-if="!row.canAnswer"
                  :aria-hidden="true"
                  class="tui-performUserActivityListSection__progressIcon-viewOnly"
                  :size="300"
                />

                <OverdueIcon
                  v-else-if="
                    row.isOverdue && row.availabilityStatus != 'CLOSED'
                  "
                  :aria-hidden="true"
                  class="tui-performUserActivityListSection__progressIcon-overdue"
                  :size="300"
                />

                <SuccessIcon
                  v-else-if="row.progressStatus == 'COMPLETE'"
                  :aria-hidden="true"
                  class="tui-performUserActivityListSection__progressIcon-complete"
                  :size="300"
                />
              </div>

              <span>{{ getStatusText(row.progressStatus) }}</span>
            </div>
          </Cell>
        </template>
      </Table>

      <Button
        v-if="shouldShowViewMore(relationshipTypes[key].id)"
        :key="'view_more-' + key"
        class="tui-performUserActivityListSection__viewMoreButton"
        :styleclass="{ primary: true, small: true, transparent: true }"
        :text="$str('button_view_more', 'mod_perform')"
        @click="viewMore(relationshipTypes[key].id)"
      />
    </template>

    <template v-if="anonymousResponses">
      <Table
        no-items-text=""
        :data="[{}]"
        :border-bottom-hidden="true"
        :hover-off="true"
        :stack-at="512"
      >
        <template v-slot:header-row>
          <HeaderCell
            class="tui-performUserActivityListSection__roleHeader"
            size="12"
            valign="start"
            :is-stacked="false"
          >
            <span>
              {{
                $str(
                  'user_activities_other_anonymous_responses',
                  'mod_perform',
                  anonRespondents
                )
              }}
            </span>
          </HeaderCell>
        </template>

        <template v-slot:row>
          <Cell size="12" valign="center">
            <p v-if="allAnonRespondentsCompleted">
              {{ $str('user_activities_all_completed', 'mod_perform') }}
            </p>
            <p v-else>
              {{
                $str(
                  'user_activities_total_completed',
                  'mod_perform',
                  anonRespondentsCompleted
                )
              }}
            </p>
          </Cell>
        </template>
      </Table>
    </template>
  </div>
</template>

<script>
import Button from 'tui/components/buttons/Button';
import Cell from 'tui/components/datatable/Cell';
import HeaderCell from 'tui/components/datatable/HeaderCell';
import OverdueIcon from 'tui/components/icons/Overdue';
import ParticipantUserHeader from 'mod_perform/components/user_activities/participant/ParticipantUserHeader';
import SuccessIcon from 'tui/components/icons/SuccessSolid';
import Table from 'tui/components/datatable/Table';
import ViewOnlyIcon from 'tui/components/icons/ViewOnlyAccess';

export default {
  components: {
    Cell,
    Button,
    HeaderCell,
    OverdueIcon,
    ParticipantUserHeader,
    SuccessIcon,
    Table,
    ViewOnlyIcon,
  },

  props: {
    aboutRole: {
      required: true,
      type: Number,
    },
    anonymousResponses: Boolean,
    relationshipTypes: Object,
    subjectSection: {
      required: true,
      type: Object,
    },
  },

  data() {
    return {
      viewMoreSelected: {},
    };
  },

  computed: {
    /**
     * Have all the other anonymous responses been completed
     *
     * @returns {Boolean}
     */
    allAnonRespondentsCompleted() {
      return this.anonRespondents == this.anonRespondentsCompleted;
    },

    /**
     * How many anonymous respondents are there less the ones displayed
     *
     * @returns {Number}
     */
    anonRespondents() {
      let result =
        this.subjectSection.summary.totalRespondents -
        this.subjectSection.participationToDisplay.length;

      return result < 0 ? 0 : result;
    },

    /**
     * How many anonymous respondents have completed less the ones displayed
     *
     * @returns {Number}
     */
    anonRespondentsCompleted() {
      let result =
        this.subjectSection.summary.totalCompleted -
        this.subjectSection.participationToDisplay.length;

      return result < 0 ? 0 : result;
    },
  },

  methods: {
    /**
     * Get the table data based on a relationship id
     *
     * @param {Number} relationship_id
     * @returns {Array}
     */
    getTableData(relationship_id) {
      //  If the 'view more' button hasn't been clicked then only include the first three participants
      if (!this.viewMoreSelected[relationship_id]) {
        return this.participationData(relationship_id).slice(0, 3);
      }

      return this.participationData(relationship_id);
    },

    /**
     * Check if this relationship table should have the 'view more' button
     *
     * @param {Number} relationship_id
     * @returns {Boolean}
     */
    shouldShowViewMore(relationship_id) {
      return (
        this.participationData(relationship_id).length > 3 &&
        !this.viewMoreSelected[relationship_id]
      );
    },

    /**
     * Add this relationship id to viewMoreSelected so we know that it's been selected
     *
     * @param {Number} relationship_id
     */
    viewMore(relationship_id) {
      this.viewMoreSelected[relationship_id] = true;
    },

    /**
     * Get the number of users per role as a string based on the relationship_id
     *
     * @param {Number} relationship_id
     * @returns {String}
     */
    getRoleHeaderCount(relationship_id) {
      const data = this.participationData(relationship_id);
      return data.length > 1
        ? this.$str('user_activities_role_count', 'mod_perform', data.length)
        : '';
    },

    /**
     * Get the participation data for the provided relationship id
     *
     * @param {Number} relationship_id
     * @returns {Array}
     */
    participationData(relationship_id) {
      let result = [];

      this.subjectSection.participationToDisplay.forEach(participation => {
        if (participation.relationship_id == relationship_id) {
          result.push(participation);
        }
      });

      return result;
    },

    /**
     * Check if row is for current user and the role matched the tab role
     *
     * @param {Object} row
     * @return {Boolean}
     */
    forCurrentUserInRole(row) {
      return (
        row.isForCurrentUser &&
        row.relationship_id === this.aboutRole.toString()
      );
    },

    /**
     * Get the localized status text for a particular user activity.
     *
     * @param status {String}
     * @returns {string}
     */
    getStatusText(status) {
      switch (status) {
        case 'NOT_STARTED':
          return this.$str('user_activities_status_not_started', 'mod_perform');
        case 'IN_PROGRESS':
          return this.$str('user_activities_status_in_progress', 'mod_perform');
        case 'COMPLETE':
          return this.$str('user_activities_status_complete', 'mod_perform');
        case 'NOT_SUBMITTED':
          return this.$str(
            'user_activities_status_not_submitted',
            'mod_perform'
          );
        case 'PROGRESS_NOT_APPLICABLE':
          return this.$str(
            'response_visibility_view_only_lozenge',
            'mod_perform'
          );
        default:
          return '';
      }
    },
  },
};
</script>

<style lang="scss">
.tui-performUserActivityListSection {
  max-width: 800px;
  margin-left: var(--gap-12);

  & > * + * {
    margin-top: var(--gap-2);
  }

  &__roleHeader {
    @include font(body-sm);
    margin-top: var(--gap-4);
    margin-bottom: var(--gap-2);
    @media (min-width: $tui-screen-sm) {
      margin-bottom: 0;
    }
  }

  &__closed {
    margin-left: var(--gap-7);
    color: var(--color-neutral-6);
    @include font(body-sm);
  }

  &__stateProgressCell {
    margin-top: var(--gap-2);
    margin-left: var(--gap-1);
    @media (min-width: $tui-screen-sm) {
      margin-top: 0;
      margin-left: 0;
    }
  }

  &__progress {
    display: flex;
    & > * + * {
      margin-left: var(--gap-2);
    }
  }

  &__progressIcon {
    min-width: 20px;

    &-complete {
      color: var(--color-prompt-success);
    }

    &-overdue {
      color: var(--color-prompt-alert);
    }
  }

  &__viewMoreButton {
    width: 100%;
    @include font(body-sm);
  }
}
</style>
