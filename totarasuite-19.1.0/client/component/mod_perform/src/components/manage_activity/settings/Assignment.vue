<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2024 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totara.com] for more information.

  @author Kevin Hottinger <kevin.hottinger@totara.com>
  @module totara_perform
-->

<template>
  <!-- Assignments setting -->
  <div class="tui-performPASettingAssignment">
    <div class="tui-performPASettingAssignment__heading">
      <h2 class="tui-performPASettingAssignment__heading-title">
        {{ $str('user_group_assignment_title', 'mod_perform') }}
      </h2>

      <!-- Drop down for adding assignment types -->
      <div class="tui-performPASettingAssignment__heading-add">
        <Dropdown :separator="true">
          <template v-slot:trigger="{ toggle, isOpen }">
            <Button
              :aria-expanded="isOpen ? 'true' : 'false'"
              :caret="true"
              :text="$str('user_group_assignment_add_group', 'mod_perform')"
              variant="primary"
              @click="toggle"
            />
          </template>
          <DropdownButton @click="openAdder('aud')">
            {{ $str('user_group_assignment_group_cohort', 'mod_perform') }}
          </DropdownButton>
          <DropdownButton
            v-if="data.can_assign_organisations"
            @click="openAdder('org')"
          >
            {{
              $str('user_group_assignment_group_organisation', 'mod_perform')
            }}
          </DropdownButton>
          <DropdownButton
            v-if="data.can_assign_positions"
            @click="openAdder('pos')"
          >
            {{ $str('user_group_assignment_group_position', 'mod_perform') }}
          </DropdownButton>
          <DropdownButton @click="openAdder('ind')">
            {{ $str('user_group_assignment_group_individual', 'mod_perform') }}
          </DropdownButton>
        </Dropdown>
      </div>
    </div>

    <AudienceAdder
      :context-id="data.activity_context_id"
      :existing-items="addedIds.aud"
      :open="isOpen.aud"
      :show-loading-btn="showAdderLoadingBtn"
      @add-button-clicked="showAdderLoadingBtn = true"
      @added="addAssignments($event, 'aud')"
      @cancel="closeAdder('aud')"
    />

    <IndividualAdder
      :existing-items="addedIds.ind"
      :open="isOpen.ind"
      :show-loading-btn="showAdderLoadingBtn"
      @add-button-clicked="showAdderLoadingBtn = true"
      @added="addAssignments($event, 'ind')"
      @cancel="closeAdder('ind')"
    />

    <OrganisationAdder
      :existing-items="addedIds.org"
      :open="isOpen.org"
      :show-loading-btn="showAdderLoadingBtn"
      @add-button-clicked="showAdderLoadingBtn = true"
      @added="addAssignments($event, 'org')"
      @cancel="closeAdder('org')"
    />

    <PositionAdder
      :existing-items="addedIds.pos"
      :open="isOpen.pos"
      :show-loading-btn="showAdderLoadingBtn"
      @add-button-clicked="showAdderLoadingBtn = true"
      @added="addAssignments($event, 'pos')"
      @cancel="closeAdder('pos')"
    />

    <div class="tui-performPASettingAssignment__assigned">
      <!-- Initial loading display -->
      <AssignmentsGroup
        v-if="loading"
        :assignments="[]"
        title=""
        :updating="true"
      />

      <!-- No assignments added -->
      <div v-else-if="!assignments.length && !updatingGroup">
        {{ $str('user_group_assignment_no_users', 'mod_perform') }}
      </div>

      <!-- Individual assignments -->
      <AssignmentsIndividualGroup
        v-if="individualGroup.length > 0 || updatingGroup === types.ind"
        :assignments="individualGroup"
        :title="
          $str('user_group_assignment_group_individual_plural', 'mod_perform')
        "
        :updating="updatingGroup === types.ind"
        @remove="showRemoveConfirmationModal($event)"
      />

      <!-- Audience assignments -->
      <AssignmentsGroup
        v-if="audienceGroup.length > 0 || updatingGroup === types.aud"
        :assignments="audienceGroup"
        :title="
          $str('user_group_assignment_group_cohort_plural', 'mod_perform')
        "
        :updating="updatingGroup === types.aud"
        @remove="showRemoveConfirmationModal($event)"
      />

      <!-- Organisation assignments -->
      <AssignmentsGroup
        v-if="organisationGroup.length > 0 || updatingGroup === types.org"
        :assignments="organisationGroup"
        :title="
          $str('user_group_assignment_group_organisation_plural', 'mod_perform')
        "
        :updating="updatingGroup === types.org"
        @remove="showRemoveConfirmationModal($event)"
      />

      <!-- Position assignments -->
      <AssignmentsGroup
        v-if="positionGroup.length > 0 || updatingGroup === types.pos"
        :assignments="positionGroup"
        :title="
          $str('user_group_assignment_group_position_plural', 'mod_perform')
        "
        :updating="updatingGroup === types.pos"
        @remove="showRemoveConfirmationModal($event)"
      />
    </div>

    <!-- Confirm assignment removal modal -->
    <ConfirmationModal
      :confirm-button-text="
        $str('user_group_assignment_confirm_modal_remove', 'mod_perform')
      "
      :open="removeModalOpen"
      :title="confirmationModalTitle"
      @confirm="removeAssignment"
      @cancel="hideRemoveConfirmationModal"
    >
      <p>
        {{ confirmationModalMessage }}
      </p>
    </ConfirmationModal>
  </div>
</template>

<script>
import AssignmentsGroup from 'mod_perform/components/manage_activity/assignment/AssignmentsGroup';
import AssignmentsIndividualGroup from 'mod_perform/components/manage_activity/assignment/AssignmentsIndividualGroup';
import AudienceAdder from 'tui/components/adder/AudienceAdder';
import Button from 'tui/components/buttons/Button';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownButton from 'tui/components/dropdown/DropdownButton';
import IndividualAdder from 'tui/components/adder/IndividualAdder';
import OrganisationAdder from 'tui/components/adder/OrganisationAdder';
import PositionAdder from 'tui/components/adder/PositionAdder';

// Util
import { notify } from 'tui/notifications';

// Queries
import AddTrackAssignmentMutation from 'mod_perform/graphql/add_track_assignments';
import RemoveTrackAssignmentMutation from 'mod_perform/graphql/remove_track_assignments';

export default {
  components: {
    AssignmentsGroup,
    AssignmentsIndividualGroup,
    AudienceAdder,
    Button,
    ConfirmationModal,
    Dropdown,
    DropdownButton,
    IndividualAdder,
    OrganisationAdder,
    PositionAdder,
  },

  props: {
    activityId: { required: true, type: [Number, String] },
    data: { required: true, type: Object },
    loading: { type: Boolean },
    trackId: { required: true, type: Number },
  },

  emits: ['updated'],

  data() {
    return {
      // Added assignment Ids for each type
      addedIds: {
        aud: [],
        ind: [],
        org: [],
        pos: [],
      },
      // Existing assignments (all types)
      assignments: this.data.assignees,
      // Assignment user is removing
      assignmentToRemove: null,
      // Default enum
      defaultEnum: 1,
      // Controls which adder is open
      isOpen: {
        aud: false,
        ind: false,
        org: false,
        pos: false,
      },
      // Displaying assignment removal modal
      removeModalOpen: false,
      // Show loading button in adder
      showAdderLoadingBtn: false,
      // Enum for each type
      types: this.data.types,
      // Assignment group type being updated
      updatingGroup: false,
    };
  },

  computed: {
    /**
     * Is the activity active
     *
     * @return {Boolean}
     */
    activityActive() {
      return !this.data.draft;
    },

    /**
     * Title string for remove assignment confirmation modal
     *
     * @return {String}
     */
    confirmationModalTitle() {
      if (this.assignmentToRemove === null) {
        return this.$str(
          'user_group_assignment_confirm_remove_title',
          'mod_perform'
        );
      }
      return this.$str(
        this.assignmentToRemove.groupType === this.types.ind
          ? 'user_individual_assignment_confirm_remove_title'
          : 'user_group_assignment_confirm_remove_title',
        'mod_perform'
      );
    },

    /**
     * Body string for remove assignment confirmation modal
     *
     * @return {String}
     */
    confirmationModalMessage() {
      if (this.assignmentToRemove === null) {
        return this.$str(
          'user_group_assignment_confirm_remove_draft',
          'mod_perform'
        );
      }

      if (this.assignmentToRemove.groupType === this.types.ind) {
        return this.$str(
          this.activityActive
            ? 'user_individual_assignment_confirm_remove_active'
            : 'user_individual_assignment_confirm_remove_draft',
          'mod_perform'
        );
      }

      return this.$str(
        this.activityActive
          ? 'user_group_assignment_confirm_remove_active'
          : 'user_group_assignment_confirm_remove_draft',
        'mod_perform'
      );
    },

    /**
     * Current audience assignments
     *
     * @return {Object}
     */
    audienceGroup() {
      return this.assignments.filter(item => item.group.type == this.types.aud);
    },

    /**
     * Current individual assignments
     *
     * @return {Object}
     */
    individualGroup() {
      return this.assignments.filter(item => item.group.type == this.types.ind);
    },

    /**
     * Current organisations assignments
     *
     * @return {Object}
     */
    organisationGroup() {
      return this.assignments.filter(item => item.group.type == this.types.org);
    },

    /**
     * Current position assignments
     *
     * @return {Object}
     */
    positionGroup() {
      return this.assignments.filter(item => item.group.type == this.types.pos);
    },
  },

  watch: {
    data: {
      deep: true,
      handler(update) {
        this.assignments = update.assignees;
        this.getAssignmentIDs();
      },
      immediate: true,
    },
  },

  methods: {
    /**
     * Add additional assignments for a certain type
     *
     * @param {Object} selection Contains data for selected assignments
     * @param {String} typeKey (aud, ind, org, pos)
     */
    async addAssignments(selection, typeKey) {
      let existingIds = this.addedIds[typeKey];
      let typeId = this.types[typeKey];

      // Filter out previously added items
      const groups = selection.data
        .filter(item => existingIds.indexOf(item.id) == -1)
        .map(item => {
          return { id: item.id, type: typeId };
        });

      // If no changes to be made
      if (!groups[0]) {
        return;
      }

      // Handle saving state
      this.updatingGroup = groups[0].type;

      try {
        const { data: result } = await this.$apollo.mutate({
          mutation: AddTrackAssignmentMutation,
          variables: {
            assignments: {
              track_id: this.trackId,
              type: this.defaultEnum,
              groups: groups,
            },
          },
          refetchAll: false, // Don't refetch all the data again
        });

        this.updatingGroup = false;

        if (result.mod_perform_add_track_assignments) {
          this.$emit('updated');
          this.addedIds[typeKey] = selection.ids;
          this.closeAdder(typeKey);
        }
      } catch (e) {
        // Error notification
        notify({
          message: this.$str('toast_error_generic_update', 'mod_perform'),
          type: 'error',
        });
        this.updatingGroup = false;
      }
    },

    /**
     * Close the adder based on type
     *
     * @param {String} type (aud, ind, org, pos)
     */
    closeAdder(type) {
      this.isOpen[type] = false;
    },

    /**
     * Get the assignments for each type from provided assignment data
     */
    getAssignmentIDs() {
      this.addedIds.aud = this.assignments
        .filter(assignment => assignment.group.type == this.types.aud)
        .map(assignment => assignment.group.id);

      this.addedIds.ind = this.assignments
        .filter(assignment => assignment.group.type == this.types.ind)
        .map(assignment => assignment.group.id);

      this.addedIds.org = this.assignments
        .filter(assignment => assignment.group.type == this.types.org)
        .map(assignment => assignment.group.id);

      this.addedIds.pos = this.assignments
        .filter(assignment => assignment.group.type == this.types.pos)
        .map(assignment => assignment.group.id);
    },

    /**
     * Hides the remove assignment confirmation dialog.
     */
    hideRemoveConfirmationModal() {
      this.assignmentToRemove = null;
      this.removeModalOpen = false;
    },

    /**
     * Open the adder based on type
     *
     * @param {String} type (aud, ind, org, pos)
     */
    openAdder(type) {
      // Show the adder
      this.isOpen[type] = true;
      this.showAdderLoadingBtn = false;
    },

    /**
     * Remove existing assignment
     *
     */
    async removeAssignment() {
      if (!this.assignmentToRemove) {
        return;
      }

      // Handle saving state
      this.updatingGroup = this.assignmentToRemove.groupType;

      try {
        const { data: result } = await this.$apollo.mutate({
          mutation: RemoveTrackAssignmentMutation,
          variables: {
            assignments: {
              track_id: this.trackId,
              type: this.assignmentToRemove.assignmentType,
              groups: [
                {
                  id: this.assignmentToRemove.groupId,
                  type: this.assignmentToRemove.groupType,
                },
              ],
            },
          },
          refetchAll: false, // Don't refetch all the data again
        });

        this.updatingGroup = false;

        if (result.mod_perform_remove_track_assignments) {
          this.$emit('updated');
          this.hideRemoveConfirmationModal();
        }
      } catch (e) {
        // Error notification
        notify({
          message: this.$str('toast_error_generic_update', 'mod_perform'),
          type: 'error',
        });

        this.updatingGroup = false;
      }
    },

    /**
     * Shows the remove assignment confirmation dialog.
     *
     * @param {Object} request Contains data for assignment to be removed
     */
    showRemoveConfirmationModal(request) {
      this.assignmentToRemove = {
        assignmentType: parseInt(request.assignmentType),
        groupId: request.groupId,
        groupType: parseInt(request.groupType),
      };
      this.removeModalOpen = true;
    },
  },
};
</script>

<style lang="scss">
.tui-performPASettingAssignment {
  display: flex;
  flex-direction: column;
  gap: var(--gap-4);

  &__heading {
    display: flex;
    flex-direction: column;
    gap: var(--gap-2);
    margin-top: calc(var(--gap-1) * -1);

    &-title {
      @include font(h3);
      margin: 0;
    }

    &-add {
      margin-left: auto;
    }
  }

  &__assigned {
    display: flex;
    flex-direction: column;
    gap: var(--gap-4);
  }
}

@media (min-width: $tui-screen-sm) {
  .tui-performPASettingAssignment {
    &__heading {
      flex-direction: row;
      align-items: center;
    }
  }
}
</style>
