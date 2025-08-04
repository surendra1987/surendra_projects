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

  @author Samantha Jayasinghe <samantha.jayasinghe@totaralearning.com>
  @author Fabian Derschatta <fabian.derschatta@totaralearning.com>
  @module mod_perform
-->
<template>
  <LayoutSidePanel
    class="tui-participantContent"
    :loading="$apollo.loading"
    :title="getActivityTitle()"
    :outer-first-loader="true"
  >
    <template v-if="showBanner" v-slot:feedback-banner>
      <NotificationBanner
        type="info"
        :dismissable="true"
        :message="bannerMessage"
        @dismiss="hideBanner()"
      />
    </template>

    <template v-slot:content-nav>
      <PageBackLink
        v-if="!isExternalParticipant && !viewOnlyReportMode"
        :link="$url(userActivitiesUrl)"
        :text="$str('back_to_user_activities', 'mod_perform')"
      />
    </template>

    <template v-slot:user-overview>
      <ParticipantGeneralInformation
        v-if="subjectUser.card_display && !viewOnlyReportMode"
        :subject-user="subjectUser"
        :job-assignments="jobAssignments"
        :current-user-is-subject="currentUserIsSubject"
        :relationship="currentRelationship"
      />

      <div v-else class="tui-participantContent__user">
        <ParticipantUserHeader
          :user-name="subjectUser.fullname"
          :profile-picture="subjectUser.profileimageurlsmall"
          :profile-picture-alt="subjectUser.profileimagealt"
          size="small"
          class="tui-participantContent__user-info"
        />
        <ResponseRelationshipSelector
          v-if="viewOnlyReportMode"
          v-model:value="selectedRelationshipFilter"
          :anonymous-responses="activity.anonymous_responses"
          :subject-instance-id="subjectInstanceId"
        />
        <div v-else class="tui-participantContent__user-relationship">
          {{ $str('user_activities_your_relationship_to_user', 'mod_perform') }}
          <h3 class="tui-participantContent__user-relationshipValue">
            {{ relationshipToUser }}
          </h3>
        </div>
      </div>
    </template>

    <template v-slot:side-panel>
      <h3 class="tui-participantContent__progressTrackerHeading">
        {{ $str('sections_header', 'mod_perform') }}
      </h3>
      <ProgressTrackerNav
        v-if="viewOnlyReportMode"
        :force-vertical="true"
        :item-content-full-width="true"
        :item-content-overflow-hidden="true"
        :items="siblingSections"
        :uncontained-popover="true"
        marker-mode="workflow"
        class="tui-participantContent__progressTracker"
      >
        <template v-slot="{ entry }">
          <ActivityProgressTrackerItem
            :id="entry.id"
            :key="entry.id"
            :selected-id="navModel"
            :text="entry.display_title"
            @select="navChange(entry)"
          />
        </template>
      </ProgressTrackerNav>
      <ProgressTrackerNav
        v-else
        :force-vertical="true"
        :item-content-full-width="true"
        :item-content-overflow-hidden="true"
        :items="participantSections"
        :uncontained-popover="true"
        marker-mode="workflow"
        class="tui-participantContent__progressTracker"
      >
        <template v-slot="{ entry }">
          <ActivityProgressTrackerItem
            :id="entry.id"
            :key="entry.section.id"
            :availability="entry.availability_string"
            :has-required="entry.has_required_element"
            :selected-id="navModel"
            :text="entry.section.display_title"
            @select="navChange(entry)"
          />
        </template>
      </ProgressTrackerNav>
    </template>

    <!-- Add manual close action to side panel -->
    <template
      v-if="
        respondingParticipant &&
          activity.settings.manual_close &&
          !singleSectionCloseOnSubmission
      "
      v-slot:side-panel-action
    >
      <div class="tui-participantContent__manualClose">
        <div
          v-if="!instanceClosed && !canManuallyClose"
          class="tui-participantContent__manualClose-requirement"
        >
          {{ $str('manual_closure_required_sections', 'mod_perform') }}
        </div>

        <div class="tui-participantContent__manualClose-action">
          <Button
            :disabled="!canManuallyClose || instanceClosed"
            :text="$str('manual_closure_submit_button', 'mod_perform')"
            type="submit"
            @click="openManualCloseConfirmation"
          >
            <template v-slot:icon>
              <LockIcon v-if="!instanceClosed" />
              <LockedIcon v-else />
            </template>
          </Button>
        </div>
      </div>
    </template>

    <template v-slot:content>
      <Uniform
        v-if="initialValues"
        :key="activeParticipantSection.id"
        ref="form"
        v-slot="{ getSubmitting }"
        class="tui-participantContent__form"
        :initial-values="initialValues"
        @submit="handleSubmit"
        @change="handleChange"
      >
        <!-- Section -->
        <div ref="sectionElement" class="tui-participantContent__section">
          <div class="tui-participantContent__sectionHeading">
            <h2 class="tui-participantContent__sectionHeading-title">
              {{ section.display_title }}
            </h2>
            <div
              v-if="!viewOnlyReportMode"
              class="tui-participantContent__infoBar"
            >
              <ResponsesAreVisibleToDescription
                class="tui-participantContent__sectionHeadingOtherResponsesDescription"
                :current-user-is-subject="currentUserIsSubject"
                :visible-to-relationships="responsesAreVisibleTo"
                :activity="activity"
                :participant-section="activeParticipantSection"
              />
              <div
                class="tui-participantContent__sectionHeading-otherResponseSwitch"
              >
                <ToggleSwitch
                  v-if="hasOtherResponse && participantCanAnswer"
                  v-model:value="showOtherResponse"
                  :text="
                    $str('user_activities_other_response_show', 'mod_perform')
                  "
                />
              </div>
            </div>
            <!-- In view only report mode and relationship not in section -->
            <div
              v-else-if="noParticipantForRelationshipFilter"
              class="tui-participantContent__sectionHeading-relationshipNotInSection"
            >
              {{ $str('selected_relationship_not_in_section', 'mod_perform') }}
            </div>
          </div>
          <template v-if="!noParticipantForRelationshipFilter">
            <div class="tui-participantContent__section-requiredContainer">
              <span
                class="tui-participantContent__section-responseRequired"
                v-text="'*'"
              />
              {{ $str('section_element_response_required', 'mod_perform') }}
            </div>

            <div class="tui-participantContent__sectionItems">
              <div
                v-for="sectionElement in cleanedSectionElements"
                :key="sectionElement.id"
                class="tui-participantContent__sectionItem"
                role="group"
                :aria-label="sectionElement.element.title"
              >
                <ResponseHeader
                  v-if="sectionElement.element.title"
                  :id="$id('title')"
                  :is-respondable="sectionElement.is_respondable"
                  :required="sectionElement.element.is_required"
                  :title="sectionElement.element.title"
                />

                <div class="tui-participantContent__sectionItem-content">
                  <component
                    :is="sectionElement.formComponent"
                    :active-section-is-closed="activeSectionIsClosed"
                    :anonymous-responses="activity.anonymous_responses"
                    :core-relationship-id="coreRelationshipId"
                    :element="sectionElement.element"
                    :element-components="sectionElement.element.element_plugin"
                    :error="errors && errors[sectionElement.id]"
                    :group-id="checkboxGroupId"
                    :is-draft="isDraft"
                    :is-external-participant="isExternalParticipant"
                    :participant-can-answer="participantCanAnswer"
                    :participant-instance-id="participantInstanceId"
                    :permissions="sectionElement.permissions"
                    :subject-instance-id="subjectInstanceId"
                    :path="['sectionElements', sectionElement.id]"
                    :section-element="sectionElement"
                    :show-other-response="showOtherResponse"
                    :view-only="viewOnlyReportMode"
                    :subject-user="subjectUser"
                    :token="token"
                    :current-user-id="currentUserId"
                    @refetch-sections="reloadData"
                    @remove-from-form-data="removeFromSectionElement"
                    @unsaved-plugin-change="unsavedPluginChange"
                    @show-banner="canShowBanner"
                  />
                </div>
              </div>
            </div>
          </template>
        </div>

        <div
          v-if="
            !activeSectionIsClosed &&
              participantCanAnswer &&
              !viewOnlyReportMode
          "
          class="tui-participantContent__actions"
        >
          <ButtonGroup>
            <Button
              :text="sectionSubmitButtonText"
              type="submit"
              variant="primary"
              @click="fullSubmit(getSubmitting)"
            >
              <template v-if="lockSectionOnSubmission" v-slot:icon>
                <LockIcon />
              </template>
            </Button>

            <Button
              v-if="hasSaveDraft"
              :text="$str('participant_section_button_draft', 'mod_perform')"
              type="submit"
              variant="link"
              @click="draftSubmit(getSubmitting)"
            />
          </ButtonGroup>
        </div>

        <div
          v-if="
            (activeSectionIsClosed &&
              (previousNavSectionModel || nextNavSectionModel)) ||
              !participantCanAnswer ||
              viewOnlyReportMode
          "
          class="tui-participantContent__navigation"
        >
          <div>
            <Button
              v-if="previousNavSectionModel"
              :text="$str('previous_section', 'mod_perform')"
              @click="loadPreviousSection"
            />
          </div>

          <div class="tui-participantContent__navigation-buttons">
            <Button
              v-if="nextNavSectionModel"
              :styleclass="{ primary: 'true' }"
              :text="$str('next_section', 'mod_perform')"
              @click="loadNextSection"
            />
            <ActionLink
              v-if="viewOnlyReportMode"
              :text="$str('button_close', 'mod_perform')"
              :href="backToUserReportHref"
            />
          </div>
        </div>
      </Uniform>
    </template>

    <template v-slot:modals>
      <ConfirmationModal
        :open="modalOpen"
        :confirm-button-text="$str('submit', 'core')"
        :title="
          $str('user_activities_submit_confirmation_title', 'mod_perform')
        "
        @confirm="confirmModal"
        @cancel="cancelModal"
      >
        <p>
          {{
            $str('user_activities_submit_confirmation_message', 'mod_perform')
          }}
        </p>

        <!-- Submitting current section will trigger auto close activity -->
        <p v-if="activityAutoClose">
          {{ $str('activity_closes_on_section_submit', 'mod_perform') }}
        </p>

        <!-- Won't be able to change responses after submission text -->
        <p v-else-if="lockSectionOnSubmission">
          {{
            $str(
              'user_activities_close_on_completion_submit_confirmation_message',
              'mod_perform'
            )
          }}
        </p>
      </ConfirmationModal>

      <!-- Manually close confirmation -->
      <ConfirmationModal
        :confirm-button-text="
          $str('manual_closure_confirm_button', 'mod_perform')
        "
        :open="manuallyCloseConfirmOpen"
        :title="$str('manual_closure_confirm_title', 'mod_perform')"
        @confirm="handleManualClose"
        @cancel="closeManualCloseConfirmation"
      >
        <p>
          {{ $str('manual_closure_confirm_body', 'mod_perform') }}
        </p>

        <p>
          {{ $str('manual_closure_confirm_body_question', 'mod_perform') }}
        </p>
      </ConfirmationModal>
    </template>
  </LayoutSidePanel>
</template>

<script>
// Util
import { formatParams, uniqueId } from 'tui/util';
import { generateInitialValue } from 'mod_perform/initial_value_processor';
import { RELATIONSHIP_SUBJECT } from 'mod_perform/constants';
import { notify } from 'tui/notifications';
import { config } from 'tui/config';

// Components
import ActionLink from 'tui/components/links/ActionLink';
import ActivityProgressTrackerItem from 'mod_perform/components/user_activities/ActivityProgressTrackerItem';
import Button from 'tui/components/buttons/Button';
import ButtonCancel from 'tui/components/buttons/Cancel';
import ButtonGroup from 'tui/components/buttons/ButtonGroup';
import Collapsible from 'tui/components/collapsible/Collapsible';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import FormRow from 'tui/components/form/FormRow';
import LayoutSidePanel from 'mod_perform/components/user_activities/layout/LayoutOneColumnSidePanelActivities';
import Loader from 'tui/components/loading/Loader';
import LockedIcon from 'tui/components/icons/Lock';
import LockIcon from 'tui/components/icons/Unlock';
import NotificationBanner from 'tui/components/notifications/NotificationBanner';
import PageBackLink from 'tui/components/layouts/PageBackLink';
import PageHeading from 'tui/components/layouts/PageHeading';
import ParticipantUserHeader from 'mod_perform/components/user_activities/participant/ParticipantUserHeader';
import ProgressTrackerNav from 'tui/components/progresstracker/ProgressTrackerNav';
import ResponseHeader from 'mod_perform/components/element/ElementParticipantResponseHeader';
import ResponsesAreVisibleToDescription from 'mod_perform/components/user_activities/participant/ResponsesAreVisibleToDescription';
import ResponseRelationshipSelector from 'mod_perform/components/user_activities/ResponseRelationshipSelector';
import ToggleSwitch from 'tui/components/toggle/ToggleSwitch';
import { Uniform } from 'tui/components/uniform';
import MiniProfileCard from 'tui/components/profile/MiniProfileCard';
import ParticipantGeneralInformation from 'mod_perform/components/user_activities/participant/ParticipantGeneralInformation';
// graphQL
import SectionResponsesQuery from 'mod_perform/graphql/participant_section';
import viewOnlyReportModeSectionResponsesQuery from 'mod_perform/graphql/view_only_section_responses';
import SectionResponsesQueryExternal from 'mod_perform/graphql/participant_section_external_participant_nosession';
import UpdateSectionResponsesMutation from 'mod_perform/graphql/update_section_responses';
import UpdateSectionResponsesMutationExternalParticipant from 'mod_perform/graphql/update_section_responses_external_participant_nosession';
import manuallyCloseInstanceMutation from 'mod_perform/graphql/close_participant_instance';
import manuallyCloseInstanceMutationExternal from 'mod_perform/graphql/close_participant_instance_by_external_nosession';

/**
 * Participant section status constants
 */
const STATUS_IN_PROGRESS = 'IN_PROGRESS';
const STATUS_NOT_STARTED = 'NOT_STARTED';
const STATUS_PROGRESS_NOT_APPLICABLE = 'PROGRESS_NOT_APPLICABLE';
const STATUS_COMPLETE = 'COMPLETE';

/**
 * Participant section availability constants
 */
const AVAILABILITY_NOT_APPLICABLE = 'AVAILABILITY_NOT_APPLICABLE';
const AVAILABILITY_OPEN = 'OPEN';
const AVAILABILITY_CLOSED = 'CLOSED';

export default {
  components: {
    ActionLink,
    ActivityProgressTrackerItem,
    Button,
    ButtonCancel,
    ButtonGroup,
    Collapsible,
    ConfirmationModal,
    FormRow,
    LayoutSidePanel,
    Loader,
    LockedIcon,
    LockIcon,
    NotificationBanner,
    PageBackLink,
    PageHeading,
    ParticipantUserHeader,
    ProgressTrackerNav,
    ResponseHeader,
    ResponsesAreVisibleToDescription,
    ResponseRelationshipSelector,
    ToggleSwitch,
    Uniform,
    MiniProfileCard,
    ParticipantGeneralInformation,
  },

  props: {
    /**
     * The abstract perform activity this is an instance of.
     */
    activity: {
      required: true,
      type: Object,
    },

    /**
     * Created day of activity.
     */
    createdAt: {
      type: String,
      required: true,
    },

    /**
     * The user this activity is about.
     */
    subjectUser: {
      required: true,
      type: Object,
      validator(value) {
        return ['id', 'profileimageurlsmall', 'fullname'].every(
          Object.prototype.hasOwnProperty.bind(value)
        );
      },
    },

    /**
     * The id of the logged in user.
     */
    currentUserId: {
      type: Number,
    },

    /**
     * The url enables user navigate back to user activity list
     */
    userActivitiesUrl: {
      type: String,
    },

    /**
     * A participant instance id, to look the section up with (used by participant mode).
     */
    participantInstanceId: {
      type: Number,
    },

    /**
     * participant section id (used by participant mode).
     */
    participantSectionId: {
      type: Number,
    },

    /**
     * subject instance id (used by view-only mode).
     */
    subjectInstanceId: {
      type: Number,
      required: true,
    },

    /**
     * section id (used by view-only mode).
     */
    sectionId: {
      type: Number,
    },

    /**
     * Optional token if this is an external participant (used by participant mode).
     */
    token: {
      required: false,
      type: String,
    },

    jobAssignments: {
      type: Array,
      required: true,
    },
  },

  data() {
    return {
      answerableParticipantInstances: null,
      answeringAsParticipantId: this.participantInstanceId,
      activeParticipantSection: {},
      completionSaveSuccess: false,
      errors: null,
      // Track if external participant is completing final section
      externalSubmitting: false,
      hasUnsavedChanges: false,
      initialValues: null,
      isSaving: false,
      section: {
        title: '',
        section_elements: [],
      },
      sectionElements: [],
      progressStatus: null,
      showOtherResponse: false,
      modalOpen: false,
      formValues: {},
      manuallyCloseConfirmOpen: false,
      participantSections: [],
      siblingSections: [],
      hasChanges: false,
      responsesAreVisibleTo: [],
      respondingParticipant: false,
      selectedRelationshipFilter: null,
      selectedParticipantSectionId: this.participantSectionId,
      selectedSectionId: this.selectedSectionId || null,
      isDraft: false,
      checkboxGroupId: this.$id('label'),
      unsavedPluginChanges: [],
      showBanner: false,
      bannerMessage: '',
    };
  },

  computed: {
    /*
     * Do any of the questions have other responses
     */
    hasOtherResponse() {
      // We must check every question, because relationships can differ per question.
      return this.sectionElements.some(
        sectionElement => sectionElement.other_responder_groups.length > 0
      );
    },

    /*
     * Is there more than one section in the activity
     */
    hasMultipleSections() {
      return this.viewOnlyReportMode
        ? this.siblingSections.length > 1
        : this.participantSections.length > 1;
    },

    /**
     * Are we showing view-only (report) version,
     * this is the form not from perspective of any one participant, but as someone reviewing all other responses.
     *
     * View only mode requires the subjectInstanceId prop (sectionId is optional)
     * Participant mode requires the participantInstanceId prop (participantSectionId is optional)
     */
    viewOnlyReportMode() {
      return Boolean(!this.participantInstanceId);
    },

    participantCanAnswer() {
      return this.activeParticipantSection.can_answer;
    },

    coreRelationshipId() {
      let relationshipId = null;

      if (this.activeParticipantSection.answerable_participant_instances) {
        let coreRelationship = this.activeParticipantSection.answerable_participant_instances.filter(
          e => e.id == this.answeringAsParticipantId
        );

        if (coreRelationship !== 'undefined' && coreRelationship.length) {
          relationshipId = coreRelationship[0].core_relationship.id;
        }
      }

      return relationshipId;
    },

    /**
     * Checks draft savings is available
     *
     * @return {Boolean}
     */
    hasSaveDraft() {
      return this.progressStatus !== STATUS_COMPLETE;
    },

    /**
     * Get and set the section navigation model,
     * for the view-only (report) version this is a section_id,
     * for participant mode this is a participant_section_id.
     */
    navModel: {
      get() {
        if (this.viewOnlyReportMode) {
          const firstSiblingSection = this.siblingSections[0] || {};

          return this.selectedSectionId
            ? this.selectedSectionId
            : firstSiblingSection.id;
        }

        return this.selectedParticipantSectionId;
      },
      set(value) {
        if (this.viewOnlyReportMode) {
          this.selectedSectionId = value;
        } else {
          this.selectedParticipantSectionId = value;
        }
      },
    },

    /**
     * Returns true if the current user is an external participant,
     * means the token is set
     * @return {Boolean}
     */
    isExternalParticipant() {
      if (this.viewOnlyReportMode) {
        return false;
      }

      return this.token !== null && this.token.length > 0;
    },

    /**
     * Apply dynamic filtering and sorting to section elements.
     */
    cleanedSectionElements() {
      return this.sectionElements.map(sectionElement => {
        const cleanSectionElement = Object.assign({}, sectionElement);

        if (
          this.activity.anonymous_responses &&
          cleanSectionElement.other_responder_groups.length > 0
        ) {
          // Push all missing responses to the end.
          const sortedAnonGroup = this.sortMissingAnswersToEnd(
            cleanSectionElement.other_responder_groups[0]
          );

          cleanSectionElement.other_responder_groups = [sortedAnonGroup];
        } else if (this.selectedRelationshipFilter) {
          // Apply relationship filter.
          cleanSectionElement.other_responder_groups = cleanSectionElement.other_responder_groups.filter(
            group => group.relationship_name === this.selectedRelationshipFilter
          );
        }

        return cleanSectionElement;
      });
    },

    /**
     * Has a relationship filter been applied, but the selected
     * relationship is not a participant in the current section
     */
    noParticipantForRelationshipFilter() {
      if (this.selectedRelationshipFilter === null) {
        return false;
      }

      // Check that any element has "other responders".
      // The reason we need to check them all is that "other responders"
      //  are stripped off non-respondable elements in the back end.
      return !this.cleanedSectionElements.some(
        sectionElement => sectionElement.other_responder_groups.length !== 0
      );
    },

    relationshipToUser() {
      if (this.currentUserIsSubject) {
        return this.$str('relation_to_subject_self', 'mod_perform');
      }

      return this.answeringAs ? this.answeringAs.core_relationship.name : null;
    },

    /**
     * Get the elements that can be responded to.
     */
    respondableSectionElements() {
      return this.sectionElements.filter(
        sectionElement => sectionElement.is_respondable
      );
    },

    /**
     * Is the participant answering as the subject relationship?
     */
    currentUserIsSubject() {
      return (
        !this.isExternalParticipant &&
        this.answeringAs != null &&
        this.answeringAs.core_relationship.idnumber === RELATIONSHIP_SUBJECT
      );
    },

    /**
     * Checks if active participant section is closed.
     *
     * @return {Boolean}
     */
    activeSectionIsClosed() {
      return (
        this.activeParticipantSection &&
        this.activeParticipantSection.availability_status === 'CLOSED'
      );
    },

    nextNavSectionModel() {
      const next = this.navModelSections[this.navModelIndex + 1];
      return next ? next.id : null;
    },

    previousNavSectionModel() {
      const previous = this.navModelSections[this.navModelIndex - 1];
      return previous ? previous.id : null;
    },

    navModelIndex() {
      const sections = this.navModelSections;

      return sections.findIndex(section => section.id == this.navModel);
    },

    navModelSections() {
      return this.viewOnlyReportMode
        ? this.siblingSections
        : this.participantSections;
    },

    /**
     * Get the participant instance we are currently answering as.
     * @return {object|null}
     */
    answeringAs() {
      if (
        this.viewOnlyReportMode ||
        this.answerableParticipantInstances === null
      ) {
        return null;
      }

      return this.answerableParticipantInstances.find(
        pi => Number(pi.id) === Number(this.answeringAsParticipantId)
      );
    },

    currentRelationship() {
      return this.answeringAs ? this.answeringAs.core_relationship.name : null;
    },

    /**
     * The url back to the subject user reporting page, for view-only report users.
     */
    backToUserReportHref() {
      return this.$url('/mod/perform/reporting/performance/user.php', {
        subject_user_id: this.subjectUser.id,
      });
    },

    /**
     * Section will lock on submission
     *
     * @return {Boolean}
     */
    lockSectionOnSubmission() {
      // Multi section activity and section closes on submission
      if (
        this.hasMultipleSections &&
        this.activity.settings.close_on_section_submission
      ) {
        return true;

        // Single section activity and activity closes on submission
      } else if (
        !this.hasMultipleSections &&
        this.activity.settings.close_on_completion
      ) {
        return true;
      }

      return false;
    },

    /**
     * Text to display for section submit button
     *
     * @return {String}
     */
    sectionSubmitButtonText() {
      // Multi section activity
      if (this.hasMultipleSections) {
        // Submit closes section
        if (this.lockSectionOnSubmission) {
          return this.$str('activity_complete_section', 'mod_perform');
        } else {
          return this.$str('activity_submit_section', 'mod_perform');
        }

        // Single section activity
      } else {
        // Submitting single section completes activity
        if (this.lockSectionOnSubmission) {
          return this.$str('activity_complete_activity', 'mod_perform');
        } else {
          return this.$str('submit', 'core');
        }
      }
    },

    /**
     * Activity will closed based on auto close setting when current
     * section is submitted
     *
     * @return {String}
     */
    activityAutoClose() {
      return this.activeParticipantSection
        .will_completion_close_participant_instance;
    },

    /**
     * Is this a single section activity that will close on submission
     *
     * @return {Boolean}
     */
    singleSectionCloseOnSubmission() {
      return !this.hasMultipleSections && this.lockSectionOnSubmission;
    },
  },

  mounted() {
    // Confirm navigation away if user is currently editing.
    window.addEventListener('beforeunload', this.unloadHandler);
    window.addEventListener('popstate', this.popstateHandler);
  },

  apollo: {
    section: {
      query() {
        if (this.viewOnlyReportMode) {
          return viewOnlyReportModeSectionResponsesQuery;
        }

        return this.isExternalParticipant
          ? SectionResponsesQueryExternal
          : SectionResponsesQuery;
      },
      variables() {
        if (this.viewOnlyReportMode) {
          return {
            subject_instance_id: this.subjectInstanceId,
            section_id: this.selectedSectionId,
          };
        }

        return {
          participant_instance_id: this.answeringAsParticipantId,
          participant_section_id: this.selectedParticipantSectionId,
          token: this.token,
        };
      },
      fetchPolicy: 'network-only',
      update(data) {
        if (this.viewOnlyReportMode) {
          return data.mod_perform_view_only_section_responses.section;
        }

        // External participants may lose access to section data if the activity closes
        if (
          this.isExternalParticipant &&
          !data.mod_perform_participant_section_external_participant
        ) {
          if (this.externalSubmitting) {
            // Lost access due to their submission closing the overall activity
            this.goToExternalCompletionSuccess();
          } else {
            window.location.reload();
          }
        }

        return this.isExternalParticipant
          ? data.mod_perform_participant_section_external_participant.section
          : data.mod_perform_participant_section.section;
      },
      result({ data }) {
        let result;
        this.externalSubmitting = false;

        if (this.viewOnlyReportMode) {
          result = data.mod_perform_view_only_section_responses;
          this.siblingSections = result.siblings;

          this.siblingSections = this.getSectionProgressTrackerData(
            this.siblingSections
          );
        } else {
          result = this.isExternalParticipant
            ? data.mod_perform_participant_section_external_participant
            : data.mod_perform_participant_section;
          if (result.id != this.selectedParticipantSectionId) {
            this.selectedParticipantSectionId = result.id;
          }
          this.answerableParticipantInstances =
            result.answerable_participant_instances;
          this.activeParticipantSection = result;

          this.participantSections = this.getSectionProgressTrackerData(
            result.participant_instance.participant_sections
          );

          this.respondingParticipant = this.hasRespondingParticipantSection(
            result.participant_instance.participant_sections
          );

          this.canManuallyClose =
            result.participant_instance.can_be_manually_closed_by_participant;

          this.instanceClosed = result.participant_instance.is_closed;

          this.progressStatus = result.progress_status;
          this.responsesAreVisibleTo = result.responses_are_visible_to;
        }

        this.formValues = {};
        this.initialValues = {
          sectionElements: {},
        };

        this.sectionElements = result.section_element_responses.map(item => {
          let childElements = item.element.children.map(child => {
            return Object.assign(
              {
                participantSectionId: result.id ? result.id : null,
                permissions: item.permissions
                  ? JSON.parse(item.permissions)
                  : null,
              },
              this.getElementDetails(child)
            );
          });

          return {
            id: item.section_element_id,
            clientId: uniqueId(),
            formComponent: tui.asyncComponent(
              item.element.element_plugin.participant_form_component
            ),
            element: Object.assign(
              {
                children: childElements,
                participantSectionId: result.id ? result.id : null,
              },
              this.getElementDetails(item.element)
            ),
            sort_order: item.sort_order,
            can_respond: item.can_respond,
            is_respondable: item.element.is_respondable,
            displays_responses: item.element.displays_responses,
            // We need to handle the absence of response data in the view-only report mode
            // in this mode the actor doesn't have "response_data" directly,
            // all responses are grouped under "other_responder_groups".
            response_data: this.viewOnlyReportMode
              ? null
              : JSON.parse(item.response_data),
            response_data_formatted_lines: this.viewOnlyReportMode
              ? []
              : item.response_data_formatted_lines,
            other_responder_groups: item.other_responder_groups,
            permissions: item.permissions ? JSON.parse(item.permissions) : null,
          };
        });

        if (this.viewOnlyReportMode || !this.participantCanAnswer) {
          this.showOtherResponse = true;
          return;
        }

        result.section_element_responses
          .filter(item => item.element.displays_responses)
          .forEach(item => {
            this.initialValues.sectionElements[
              item.section_element_id
            ] = generateInitialValue(
              item.element,
              JSON.parse(item.response_data_raw)
            );

            item.other_responder_groups.forEach(group => {
              if (group.responses.length > 0 && item.response_data) {
                this.showOtherResponse = true;
              }
            });
          });
      },
    },
  },

  methods: {
    /**
     * Does the current user have any sections as a responding participant
     *
     * @param {Array} sections
     * @return {Boolean}
     */
    hasRespondingParticipantSection(sections) {
      let respondingSections = Array.prototype.filter.call(
        sections,
        ({ availability_status }) => {
          return availability_status !== AVAILABILITY_NOT_APPLICABLE;
        }
      );

      return respondingSections.length > 0;
    },

    /**
     * Adds the relevant section data needed by the ProgressTrackerNav.
     *
     * Returns a new array of sections that will contain "states", "description" and "availability_string" fields
     *
     * @param {Array} sections
     * @return {Array}
     */
    getSectionProgressTrackerData(sections) {
      let result = sections.map(section => {
        let states = [];
        let description = '';
        let availability_string = '';

        // Map progress_status values to progress tracker states
        switch (section.progress_status) {
          case STATUS_IN_PROGRESS:
          case STATUS_NOT_STARTED:
            states.push('ready');
            description = this.$str(
              'completionstatus_not_complete',
              'totara_tui'
            );
            break;
          case STATUS_PROGRESS_NOT_APPLICABLE:
            states.push('view_only');
            description = this.$str('completionstatus_view_only', 'totara_tui');
            break;
          case STATUS_COMPLETE:
            states.push('done');
            description = this.$str('completionstatus_complete', 'totara_tui');
            break;
          default:
            states.push('ready');
            description = this.$str(
              'completionstatus_not_complete',
              'totara_tui'
            );
        }

        // Add the 'selected' state if this is the currently selected section
        if (section.id == this.navModel) {
          states.push('selected');
        }

        // Map the availability_status value to its corresponding string
        switch (section.availability_status) {
          case AVAILABILITY_NOT_APPLICABLE:
            availability_string = this.$str(
              'participant_section_availability_status_availability_not_applicable',
              'mod_perform'
            );
            break;
          case AVAILABILITY_OPEN:
            availability_string = this.$str(
              'participant_section_availability_open',
              'mod_perform'
            );
            break;
          case AVAILABILITY_CLOSED:
            availability_string = this.$str(
              'participant_section_availability_closed',
              'mod_perform'
            );
            break;
        }

        return Object.assign({}, section, {
          states: states,
          availability_string: availability_string,
          // Description is used by the ProgressTrackerNav to add to the popover for the state icon
          description: description,
        });
      });

      return result;
    },

    /**
     * Sort all missing responses to the end in a responder group.
     */
    sortMissingAnswersToEnd(responderGroup) {
      const groupClone = Object.assign({}, responderGroup);

      groupClone.responses = groupClone.responses
        .slice()
        .sort(response => (response.response_data === null ? 1 : -1));

      return groupClone;
    },

    /**
     * Get element details from object.
     *
     * @param {Object} element
     * @return Object
     */
    getElementDetails(element) {
      return {
        data: element.data ? JSON.parse(element.data) : null,
        id: element.id,
        is_required: element.is_required,
        is_respondable: element.is_respondable,
        displays_responses: element.displays_responses,
        title: element.title,
        element_plugin: element.element_plugin,
        identifier: element.identifier,
      };
    },
    /**
     * Show a generic saving error toast.
     */
    showErrorNotification() {
      notify({
        message: this.$str('toast_error_save_response', 'mod_perform'),
        type: 'error',
      });
    },

    /**
     * Show a generic success toast.
     */
    showSuccessNotification() {
      let message = this.$str(
        this.lockSectionOnSubmission
          ? 'toast_success_save_close_on_completion_response'
          : 'toast_success_save_response',
        'mod_perform'
      );
      if (this.isDraft) {
        message = this.$str('participant_section_draft_saved', 'mod_perform');
      }
      notify({ message, type: 'success' });
    },

    /**
     * Handle full and draft submit
     *
     * @param {Object} values Form values.
     */
    handleSubmit(values) {
      this.formValues = values;
      if (!this.isDraft) {
        this.modalOpen = true;
      } else {
        this.submit(this.formValues);
        this.hasUnsavedChanges = false;
        this.unsavedPluginChanges = [];
      }
    },

    /**
     * Confirms confirmation modal.
     */
    confirmModal() {
      this.submit(this.formValues);
      this.modalOpen = false;
      this.hasUnsavedChanges = false;
      this.unsavedPluginChanges = [];
    },

    /**
     * Close confirmation modal.
     */
    cancelModal() {
      this.modalOpen = false;
    },

    /**
     * Removes a specified property from a sectionElement saved in the form
     *
     * @param {Number} sectionElementId the id of the section element that contains what we want to remove
     * @param {Array} path the path to the property to remove
     */
    removeFromSectionElement(sectionElementId, path) {
      let sectionElementCopy = Object.assign(
        {},
        this.$refs.form.get(['sectionElements', sectionElementId])
      );

      const propertyToRemove = path[path.length - 1];

      // Drill down into the sectionElement object.
      // currentItem becomes the object above the one we want to delete
      let currentItem = sectionElementCopy;
      for (let i = 0; i < path.length - 1; i++) {
        if (!currentItem) {
          return;
        }
        currentItem = currentItem[path[i]];
      }
      delete currentItem[propertyToRemove];

      this.$refs.form.update(
        ['sectionElements', sectionElementId],
        sectionElementCopy
      );
    },

    /**
     * Save user responses and show notifications
     *
     * @param {Object} values
     */
    async submit(values) {
      if (this.errors) {
        this.errors = null;
      }

      // assign values from submission to the section elements
      this.sectionElements.forEach(sectionElement => {
        sectionElement.element.responseData =
          values.sectionElements[sectionElement.id];
      });

      this.isSaving = true;
      try {
        const sectionResponsesResult = await this.save();
        const result = this.isExternalParticipant
          ? sectionResponsesResult.mod_perform_update_section_responses_external_participant
          : sectionResponsesResult.mod_perform_update_section_responses;
        const submittedParticipantSection = result.participant_section;
        //assign errors to individual elements
        this.errors = submittedParticipantSection.section_element_responses
          .filter(item => item.validation_errors)
          .reduce((accumulator, current) => {
            current.validation_errors.forEach(error => {
              if (accumulator == null) {
                accumulator = {};
              }
              accumulator[current.section_element_id] = error.error_message;
            });
            return accumulator;
          }, null);

        //show validation if no errors
        if (!this.errors) {
          if (this.isDraft) {
            //stay same page and show notification
            this.showSuccessNotification();
          } else if (this.nextNavSectionModel) {
            if (this.isExternalParticipant) {
              this.externalSubmitting = true;
            }

            // Redirect to next section.
            this.showSuccessNotification();
            this.loadNextSection();
            // Scroll the next section into view
            this.$nextTick(() => {
              this.$refs.sectionElement.scrollIntoView();
            });
          } else {
            if (this.isExternalParticipant) {
              this.externalSubmitting = true;
            }

            this.reloadData();
            this.showSuccessNotification();
          }
        }
      } catch (e) {
        this.showErrorNotification();
      }
      this.isSaving = false;
    },

    /**
     * Extract section elements into new. update , delete and move
     * and call the GQL mutation to save section elements
     *
     * @returns {Object}
     */
    async save() {
      const update = this.respondableSectionElements.map(item => {
        return {
          section_element_id: item.id,
          response_data: JSON.stringify(item.element.responseData),
        };
      });

      let inputVariables = {
        participant_section_id: this.activeParticipantSection.id,
        is_draft: this.isDraft,
        update: update,
      };

      if (this.token) {
        inputVariables.token = this.token;
      }

      const { data: resultData } = await this.$apollo.mutate({
        mutation: this.isExternalParticipant
          ? UpdateSectionResponsesMutationExternalParticipant
          : UpdateSectionResponsesMutation,
        variables: {
          input: inputVariables,
        },
        refetchAll: false,
      });
      return resultData;
    },

    /**
     * Loads the next (participant) section.
     */
    loadNextSection() {
      this.changeSection(this.nextNavSectionModel);
    },

    /**
     * Loads the previous (participant) section.
     */
    loadPreviousSection() {
      this.changeSection(this.previousNavSectionModel);
    },

    /**
     * Loads the view-only section as active section.
     */
    async reloadData() {
      await this.$apollo.queries.section.refetch();
    },

    /**
     * Returns the activity title.
     */
    getActivityTitle() {
      var title = this.activity.name.trim();
      var suffix = this.createdAt ? this.createdAt.trim() : '';

      if (suffix) {
        return this.$str(
          'activity_title_with_subject_creation_date',
          'mod_perform',
          {
            title: title,
            date: suffix,
          }
        );
      }

      return title;
    },

    /**
     * Redirects to the external completion page
     */
    goToExternalCompletionSuccess() {
      const lang = config.locale.totaraLangId;
      if (this.isExternalParticipant) {
        window.location.href = this.$url(
          '/mod/perform/activity/external.php?lang=' + lang,
          {
            success: 1,
            token: this.token,
          }
        );
        return;
      }
    },

    /**
     * check if there is unsaved changes in response form
     *
     * @param values
     */
    handleChange(values) {
      for (let i in this.initialValues.sectionElements) {
        let formValue = values.sectionElements[i];
        let initValue = JSON.stringify(this.initialValues.sectionElements[i]);
        // handle initValue is null, but formValue is empty string or array
        // convert empty formValue to null, then we can compare the difference
        let isEmptyForm =
          formValue === null ||
          !Object.values(formValue)[0] ||
          Object.values(formValue)[0].length === 0;

        if (isEmptyForm) {
          formValue = null;
        }
        formValue = JSON.stringify(formValue);
        // compare init value with form value
        if (initValue !== formValue) {
          this.hasUnsavedChanges = true;
          break;
        }
        this.hasUnsavedChanges = false;
      }
    },

    /**
     * navigate to a different participant section
     *
     * @param id {Number} A participant section in participant mode,
     * or a section_id for view-only mode.
     */
    navChange({ id }) {
      this.changeSection(id);
    },

    /**
     * Change the url and data to a new (participant) section.
     * @param newNavModel {Number}
     */
    changeSection(newNavModel) {
      const shouldChange = this.checkForUnsavedChanges();

      if (shouldChange) {
        this.navModel = newNavModel;
        this.updateUrl();
        this.updateTitle();
        this.reloadData();
      }
    },

    /**
     * Check for unsaved changes before nav change.
     */
    checkForUnsavedChanges() {
      if (
        this.viewOnlyReportMode ||
        (!this.hasUnsavedChanges && !this.unsavedPluginChanges.length)
      ) {
        return true;
      }

      const message = this.$str('unsaved_changes_warning', 'mod_perform');
      const confirmNav = window.confirm(message);

      if (!confirmNav) {
        this.selectedParticipantSectionId = this.activeParticipantSection.id;
        return false;
      }

      this.hasUnsavedChanges = false;
      this.unsavedPluginChanges = [];
      return true;
    },

    /**
     * Track unsaved changes in sub plugins
     */
    unsavedPluginChange(data) {
      // add plugin keys with changes
      if (data.hasChanges) {
        if (!this.unsavedPluginChanges.includes(data.key)) {
          this.unsavedPluginChanges.push(data.key);
        }
      } else {
        // Remove plugin keys without changes
        this.unsavedPluginChanges = this.unsavedPluginChanges.filter(
          t => t !== data.key
        );
      }
    },

    /**
     * Close modal for confirming manual closure action
     */
    closeManualCloseConfirmation() {
      this.manuallyCloseConfirmOpen = false;
    },

    /**
     * Open a modal for confirming manual closure action
     */
    openManualCloseConfirmation() {
      this.manuallyCloseConfirmOpen = true;
    },

    /**
     * Attempt to manually close the participants instance
     */
    async handleManualClose() {
      this.closeManualCloseConfirmation();

      try {
        this.isSaving = true;
        let mutationInput = {};
        if (this.isExternalParticipant) {
          mutationInput.token = this.token;
        } else {
          mutationInput.participant_instance_id = this.answeringAsParticipantId;
        }
        const result = await this.$apollo.mutate({
          mutation: this.isExternalParticipant
            ? manuallyCloseInstanceMutationExternal
            : manuallyCloseInstanceMutation,
          variables: {
            input: mutationInput,
          },
        });

        if (
          result &&
          (result.data.mod_perform_close_participant_instance ||
            result.data
              .mod_perform_close_participant_instance_by_external_nosession)
        ) {
          this.reloadData();
          notify({
            message: this.$str(
              'toast_success_activity_completed_closed',
              'mod_perform'
            ),
            type: 'success',
          });
        }
      } catch (e) {
        // Error notification
        this.showErrorNotification();
      } finally {
        this.isSaving = false;
      }
    },

    /**
     * Push url params based of current state.
     */
    updateUrl() {
      const params = {};
      if (this.viewOnlyReportMode) {
        params.subject_instance_id = this.subjectInstanceId;

        if (this.selectedSectionId) {
          params.section_id = this.selectedSectionId;
        }
      } else if (this.isExternalParticipant) {
        params.token = this.token;
        params.participant_section_id = this.selectedParticipantSectionId;
      } else {
        params.participant_section_id = this.selectedParticipantSectionId;
      }

      const formattedParams = formatParams(params);
      const url = window.location.pathname + '?' + formattedParams;

      // Note we push state by default (a new history entry) not replace it on section change.
      window.history.pushState(null, null, url);
    },
    updateTitle() {
      if (this.hasMultipleSections && !this.viewOnlyReportMode) {
        const currentParticipantSection = this.activeParticipantSection.participant_instance.participant_sections.find(
          section => this.selectedParticipantSectionId === section.id
        );
        window.document.title =
          this.activity.name +
          ' - ' +
          currentParticipantSection.section.display_title;

        return;
      }
      window.document.title = this.activity.name;
    },
    popstateHandler() {
      const urlNavModelKey = this.viewOnlyReportMode
        ? 'section_id'
        : 'participant_section_id';

      let newNavModel = new URLSearchParams(window.location.search).get(
        urlNavModelKey
      );

      // If section_id is not in the url, assume the first section.
      if (this.viewOnlyReportMode && !newNavModel) {
        newNavModel = this.siblingSections[0].id;
      }

      if (newNavModel) {
        this.navModel = newNavModel;
        this.reloadData();
      } else {
        // Force reload if we didn't find the query string param we
        // were looking for.
        window.location.reload();
      }
    },
    /**
     * Displays a warning message if the user tries to navigate away without saving.
     * @param {Event} e
     * @returns {String|void}
     */
    unloadHandler(e) {
      if (!this.hasUnsavedChanges && !this.unsavedPluginChanges.length) {
        return;
      }

      // For older browsers that still show custom message.
      const discardUnsavedChanges = this.$str(
        'unsaved_changes_warning',
        'mod_perform'
      );
      e.preventDefault();
      e.returnValue = discardUnsavedChanges;
      return discardUnsavedChanges;
    },

    /**
     * Handle full submit
     *
     * @param handleSubmit
     */
    fullSubmit(handleSubmit) {
      this.isDraft = false;
      handleSubmit();
    },

    /**
     * Handle draft submit
     *
     * @param handleSubmit
     */
    draftSubmit(handleSubmit) {
      this.isDraft = true;
      handleSubmit();
    },

    /**
     * Show banner
     *
     * @param bannerMessage
     */
    canShowBanner(bannerMessage) {
      this.showBanner = true;
      this.bannerMessage = bannerMessage;
    },

    /**
     * Hide banner
     */
    hideBanner() {
      this.showBanner = false;
      this.bannerMessage = null;
    },
  },
};
</script>

<style lang="scss">
.tui-participantContent {
  @include font(body);

  &__user {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: var(--gap-2) var(--gap-4);
    border: var(--border-width-thin) solid var(--color-border);
    border-radius: var(--border-radius-normal);

    & > * + * {
      margin-top: var(--gap-4);
    }

    &-relationship {
      display: flex;
      padding-top: var(--gap-3);
    }

    &-relationshipValue {
      @include font(h4);
      display: inline;
      margin: auto 0 auto var(--gap-1);
    }
  }

  &__navigation {
    display: flex;
    padding-top: var(--gap-6);
    border-top: var(--border-width-thin) solid var(--color-neutral-5);

    &-buttons {
      margin-left: auto;

      & > * + * {
        margin-left: var(--gap-4);
      }
    }
  }

  &__sectionHeading {
    & > * + * {
      margin-top: var(--gap-8);
    }

    &-title {
      @include font(h2);
      flex: 1;
      margin: auto 0;
    }

    &-relationshipNotInSection {
      font-style: italic;
    }
  }

  &__progressTracker {
    padding: var(--gap-2) 0 0 var(--gap-2);
  }

  &__progressTrackerHeading {
    @include font(h4);
    margin-top: 0;
    padding-left: var(--gap-2);
  }

  &__form {
    padding-bottom: var(--gap-12);
    & > * + * {
      margin-top: var(--gap-8);
    }
  }

  &__infoBar {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    padding: var(--gap-4) 0;
    border-top: solid var(--color-neutral-5) var(--border-width-thin);
    border-bottom: solid var(--color-neutral-5) var(--border-width-thin);
  }

  &__sectionHeadingOtherResponsesDescription {
    margin: 0;
  }

  &__sectionHeading-otherResponseSwitch {
    margin-top: var(--gap-4);
    margin-left: 0;
  }

  &__section {
    & > * + * {
      margin-top: var(--gap-8);
    }

    &-responseRequired {
      display: inline-flex;
      color: var(--color-prompt-alert);
      font-weight: var(--label-weight);
    }
  }

  &__sectionItems {
    margin-top: var(--gap-4);

    & > * + * {
      margin-top: var(--gap-12);
    }
  }

  &__sectionItem {
    &-content {
      margin-top: var(--gap-8);
      padding: 0 var(--gap-4);

      & > * + * {
        margin-top: var(--gap-8);
      }
    }
  }

  &__actions {
    padding-top: var(--gap-6);
    border-top: var(--border-width-thin) solid var(--color-neutral-5);
  }

  &__manualClose {
    display: flex;
    flex-direction: column;
    gap: var(--gap-2);
    hyphens: none;

    &-requirement {
      display: flex;
      justify-content: center;
      @include tui-font-body-x-small();
      color: var(--color-neutral-6);
      text-align: center;
    }

    &-action {
      display: flex;
      justify-content: center;
    }
  }
}

@media (min-width: $tui-screen-xs) {
  .tui-participantContent {
    // Spit the avatar and relationship blurb on tablet an larger.
    &__user {
      flex-direction: row;
      align-items: center;

      & > * + * {
        margin-top: 0;
      }

      &-info {
        padding-right: var(--gap-4);
      }

      &-relationship {
        display: block;
        padding-top: 0;
      }

      &-relationshipValue {
        display: block;
        margin: var(--gap-1) 0 0;
      }
    }

    &__infoBar {
      flex-wrap: nowrap;
    }

    &__sectionHeading-otherResponseSwitch {
      flex-shrink: 0;
      margin-top: 0;
      margin-left: var(--gap-6);
    }
  }
}
</style>
