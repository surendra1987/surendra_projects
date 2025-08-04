<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2020 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Fabian Derschatta <fabian.derschatta@totaralearning.com>
  @module mod_perform
-->
<template>
  <div class="tui-performUserActivityListSections">
    <ProgressTrackerNav
      v-if="isMultiSectionActive"
      class="tui-performUserActivityListSections__progressTracker"
      :items="subjectSectionsSubset"
      :force-vertical="true"
      :item-content-full-width="true"
      :popover-trigger-type="[]"
      marker-mode="workflow"
    >
      <template v-slot="{ entry: subjectSection }">
        <Collapsible
          :key="subjectSection.id"
          class="tui-performUserActivityListSections__collapsible"
          :initial-state="true"
          :exclude-header-padding="true"
          :hide-focus="true"
          variant="minimal"
          :label="subjectSection.section.display_title"
        >
          <template v-slot:label-extra>
            <a
              v-if="
                subjectSection.canParticipate &&
                  getViewActivitySectionUrl(subjectSection)
              "
              class="tui-performUserActivityListSections__viewSection"
              :href="getViewActivitySectionUrl(subjectSection)"
              @click.stop
            >
              {{ $str('view_section', 'mod_perform') }}
            </a>
          </template>
          <SectionsContent
            :about-role="aboutRole"
            :anonymous-responses="anonymousResponses"
            :subject-section="subjectSection"
            :relationship-types="getRelationshipTypes(subjectSection)"
          />
        </Collapsible>
      </template>
    </ProgressTrackerNav>
    <SectionsContent
      v-else
      :about-role="aboutRole"
      :anonymous-responses="anonymousResponses"
      :subject-section="subjectSectionsSubset[0]"
      :relationship-types="getRelationshipTypes(subjectSectionsSubset[0])"
    />
  </div>
</template>

<script>
import Collapsible from 'tui/components/collapsible/Collapsible';
import ProgressTrackerNav from 'tui/components/progresstracker/ProgressTrackerNav';
import SectionsContent from 'mod_perform/components/user_activities/list/SectionsContent';

export default {
  components: {
    Collapsible,
    ProgressTrackerNav,
    SectionsContent,
  },

  props: {
    aboutRole: {
      required: true,
      type: Number,
    },
    activityId: {
      required: true,
      type: String,
    },
    anonymousResponses: {
      required: true,
      type: Boolean,
    },
    isMultiSectionActive: {
      required: true,
      type: Boolean,
    },
    subjectSections: {
      required: true,
      type: Array,
    },
    viewUrl: {
      required: true,
      type: String,
    },
  },

  emits: ['single-section-view-only'],

  computed: {
    /**
     * Reduce the data set to the required values for this template
     *
     */
    subjectSectionsSubset() {
      return this.subjectSections.map(item => {
        const participation = item.participant_sections.map(
          participantSection => {
            let relationship,
              relationship_id,
              participant = {};

            const isForCurrentUser =
              participantSection.participant_instance.is_for_current_user;

            // These fields are not included if answers are anonymous.
            if (isForCurrentUser || !this.anonymousResponses) {
              relationship =
                participantSection.participant_instance.core_relationship.name;
              relationship_id =
                participantSection.participant_instance.core_relationship.id;
              participant = participantSection.participant_instance.participant;
            }

            return {
              id: participantSection.id,
              isForCurrentUser,
              progressStatus: participantSection.progress_status,
              availabilityStatus: participantSection.availability_status,
              isOverdue: participantSection.is_overdue,
              canAnswer: participantSection.can_answer,
              participant,
              relationship,
              relationship_id,
            };
          }
        );

        const filteredParticipation = participation.filter(
          participantSection => {
            if (!this.anonymousResponses) {
              return true;
            }

            return participantSection.isForCurrentUser;
          }
        );

        const participationToDisplay = this.filterToCanAnswer(
          filteredParticipation
        );

        const canCurrentUserAnswer =
          this.filterToCurrentUser(participationToDisplay).length > 0;

        return {
          canParticipate: item.can_participate,
          canCurrentUserAnswer,
          participation: filteredParticipation,
          participationToDisplay,
          summary: this.getParticipantSummary(participation),
          participant_sections: item.participant_sections,
          section: item.section,
          states: ['ready'],
        };
      });
    },
  },

  mounted() {
    this.checkSingleSectionViewOnly();
  },

  methods: {
    /**
     * All the relationship types for a single subject section with the sort_order as keys
     *
     * @param {Object} subjectSection
     * @returns {Object}
     */
    getRelationshipTypes(subjectSection) {
      let result = {};

      subjectSection.participant_sections.forEach(section => {
        if (section.participant_instance.core_relationship) {
          let name = section.participant_instance.core_relationship.name;
          let sortOrder =
            section.participant_instance.core_relationship.sort_order;
          let id = section.participant_instance.core_relationship.id;

          result[sortOrder] = { id: id, name: name };
        }
      });

      return result;
    },

    /**
     * Get "view" url for a specific user activity section based on current role.
     *
     * @param {Object} subjectSection
     * @returns {string}
     */
    getViewActivitySectionUrl(subjectSection) {
      const participantSection = subjectSection.participation.find(
        item =>
          item.isForCurrentUser &&
          item.relationship_id === this.aboutRole.toString()
      );

      if (participantSection) {
        return this.$url(this.viewUrl, {
          participant_section_id: participantSection.id,
        });
      }
      return null;
    },

    /**
     * Filter section participation to only ones that belong to the logged in user.
     *
     * @param {Object[]} participation
     * @return {Object[]}
     */
    filterToCurrentUser(participation) {
      return participation.filter(ps => ps.isForCurrentUser);
    },

    /**
     * Filter section participation to only ones that can answer.
     *
     * @param {Object[]} participation
     * @return {Object[]}
     */
    filterToCanAnswer(participation) {
      return participation.filter(ps => ps.canAnswer);
    },

    getParticipantSummary(participation) {
      const respondents = this.filterToCanAnswer(participation);
      const totalRespondents = respondents.length;
      const totalCompleted = respondents.filter(
        item => item.progressStatus === 'COMPLETE'
      ).length;

      return {
        totalRespondents,
        totalCompleted,
      };
    },

    /**
     * Let parent component know when we find out that we only have one section and it is view-only for the
     * current user.
     */
    checkSingleSectionViewOnly() {
      if (
        this.subjectSectionsSubset.length === 1 &&
        !this.subjectSectionsSubset[0].canCurrentUserAnswer
      ) {
        this.$emit('single-section-view-only', this.activityId);
      }
    },
  },
};
</script>

<style lang="scss">
.tui-performUserActivityListSections {
  & > * + * {
    margin-top: var(--gap-12);
  }

  &__progressTracker {
    &:first-child {
      margin-top: var(--gap-4);
    }
  }

  &__viewSection {
    position: absolute;
    top: calc(var(--gap-4) * -1);
    left: 0;
    white-space: nowrap;
    @include font(body-sm);
  }

  &__collapsible {
    margin-bottom: var(--gap-6);
    margin-left: calc(var(--gap-5) * -1);
  }
}
</style>
