<!--
  This file is part of Totara Enterprise Extensions.

  Copyright (C) 2021 onwards Totara Learning Solutions LTD

  Totara Enterprise Extensions is provided only to Totara
  Learning Solutions LTD's customers and partners, pursuant to
  the terms and conditions of a separate agreement with Totara
  Learning Solutions LTD or its affiliate.

  If you do not have an agreement with Totara Learning Solutions
  LTD, you may not access, use, modify, or distribute this software.
  Please contact [licensing@totaralearning.com] for more information.

  @author Alvin Smith <alvin.smith@totaralearning.com>
  @module mod_approval
-->

<template>
  <div class="tui-mod_approval-workflowHeader">
    <NavigationBar
      :back-url="backUrl"
      :back-content="$str('back_to_workflows', 'mod_approval')"
    />
    <div aria-live="assertive">
      <NotificationBanner v-if="$context.notification" />
    </div>
    <div class="tui-mod_approval-workflowHeader__titleRow">
      <div class="tui-mod_approval-workflowHeader__titleWrapper">
        <div>
          <h1 class="tui-mod_approval-workflowHeader__title">
            {{ workflowName }}
            <Lozenge
              class="tui-mod_approval-workflowHeader__status"
              type="neutral"
              :text="workflowStatus"
            />
          </h1>
          <div
            v-if="saving"
            class="tui-mod_approval-workflowHeader__savingStatus"
          >
            <Loading
              class="tui-mod_approval-workflowHeader__savingStatus-loading"
            />
            <span>{{ $str('workflow_edit_saving', 'mod_approval') }}</span>
          </div>
        </div>
        <span class="tui-mod_approval-workflowHeader__dateTime" />
        <Popover size="md" :title="$str('workflow_info', 'mod_approval')">
          <template v-slot:trigger>
            <Button
              class="tui-mod_approval-workflowHeader__infoButton"
              :text="$str('view_workflow_info', 'mod_approval')"
              :styleclass="{ transparent: true }"
            />
          </template>
          <div class="tui-mod_approval-workflowHeader__info">
            <div class="tui-mod_approval-workflowHeader__collection">
              <div class="tui-mod_approval-workflowHeader__collectionName">
                {{ $str('workflow_heading_type', 'mod_approval') }}
              </div>
              <div class="tui-mod_approval-workflowHeader__collectionValue">
                {{ workflowType }}
              </div>
            </div>
            <div class="tui-mod_approval-workflowHeader__collection">
              <div class="tui-mod_approval-workflowHeader__collectionName">
                {{ $str('workflow_heading_id', 'mod_approval') }}
              </div>
              <div class="tui-mod_approval-workflowHeader__collectionValue">
                {{ workflowIdNumber }}
              </div>
            </div>
            <div class="tui-mod_approval-workflowHeader__collection">
              <div class="tui-mod_approval-workflowHeader__collectionName">
                {{ $str('workflow_heading_assignment_type', 'mod_approval') }}
              </div>
              <div class="tui-mod_approval-workflowHeader__collectionValue">
                {{ assignmentType }}
              </div>
            </div>
            <div class="tui-mod_approval-workflowHeader__collection">
              <div class="tui-mod_approval-workflowHeader__collectionName">
                {{ $str('workflow_heading_assigned_to', 'mod_approval') }}
              </div>
              <div class="tui-mod_approval-workflowHeader__collectionValue">
                {{ assignedToName }}
              </div>
            </div>
          </div>
        </Popover>
        <span class="tui-mod_approval-workflowHeader__description">
          {{ workflowDescription }}
        </span>
      </div>
      <div class="tui-mod_approval-workflowHeader__actions">
        <Button
          v-if="$selectors.getWorkflowIsDraft($context)"
          :text="$str('publish', 'mod_approval')"
          :styleclass="{ primary: true }"
          @click="$send({ type: $e.PUBLISH })"
        />
        <Dropdown position="bottom-right" :separator="false">
          <template v-slot:trigger="{ toggle, isOpen }">
            <ButtonIcon
              :aria-label="$str('more_actions', 'mod_approval')"
              :aria-expanded="isOpen ? 'true' : 'false'"
              @click="toggle"
            >
              <MoreIcon :size="400" />
            </ButtonIcon>
          </template>
          <div class="tui-mod_approval-workflowHeader__actionsWrapper">
            <div class="tui-mod_approval-workflowHeader__actionsHeader">
              {{ $str('workflow_settings', 'mod_approval') }}
            </div>
            <DropdownItem
              v-if="interactor.can_edit"
              @click="$send({ type: $e.EDIT })"
              >{{
                $str('workflow_edit_details_settings', 'mod_approval')
              }}</DropdownItem
            >
            <DropdownItem
              v-if="interactor.can_assign_roles"
              @click="$send({ type: $e.ASSIGN_ROLES })"
            >
              {{ $str('assign_roles', 'mod_approval') }}
            </DropdownItem>
            <DropdownItem
              v-if="interactor.can_clone"
              @click="$send({ type: $e.CLONE })"
            >
              {{ $str('clone', 'mod_approval') }}
            </DropdownItem>
            <DropdownItem
              v-if="interactor.can_archive"
              @click="$send({ type: $e.ARCHIVE })"
            >
              {{ $str('archive', 'mod_approval') }}
            </DropdownItem>
            <DropdownItem
              v-if="interactor.can_unarchive"
              @click="$send({ type: $e.UNARCHIVE })"
            >
              {{ $str('unarchive', 'mod_approval') }}
            </DropdownItem>
            <DropdownItem
              v-if="interactor.can_delete"
              @click="$send({ type: $e.DELETE })"
            >
              {{ $str('delete', 'core') }}
            </DropdownItem>
          </div>
          <div class="tui-mod_approval-workflowHeader__actionsWrapper">
            <div class="tui-mod_approval-workflowHeader__actionsHeader">
              Manage reports
            </div>
            <DropdownItem
              v-if="interactor.can_upload_approver_overrides"
              :href="reportApproversUrl(workflowId)"
            >
              {{ $str('approvers_report', 'mod_approval') }}
            </DropdownItem>
            <DropdownItem
              v-if="interactor.can_assign_roles"
              :href="roleAssignmentReportUrl(workflowId)"
            >
              {{ $str('view_role_assignment_report', 'mod_approval') }}
            </DropdownItem>
            <DropdownItem
              v-if="interactor.can_view_applications_report"
              :href="reportUrl(workflowId)"
            >
              {{ $str('applications_report', 'mod_approval') }}
            </DropdownItem>
          </div>
        </Dropdown>
      </div>
    </div>
    <WorkflowDeleteModal
      :open="$matches('persistence.confirmDelete')"
      :name="workflowName"
      :workflow-applications-count="workflowApplicationsCount"
      :workflow-assignments-count="workflowAssignmentsCount"
      @confirm="$send($e.DELETE)"
      @cancel="$send($e.CANCEL)"
    />
    <ConfirmationModal
      :open="$matches('persistence.confirmArchive')"
      :title="$str('archive_workflow_warning_title', 'mod_approval')"
      :loading="$matches('persistence.confirmArchive.archiving')"
      @confirm="$send({ type: $e.ARCHIVE })"
      @cancel="$send({ type: $e.CANCEL })"
    >
      <p v-html="$str('archive_workflow_warning_message', 'mod_approval')" />
    </ConfirmationModal>
    <ConfirmationModal
      :open="$matches('persistence.confirmUnarchive')"
      :title="$str('unarchive_workflow_warning_title', 'mod_approval')"
      :loading="$matches('persistence.confirmUnarchive.unarchiving')"
      @confirm="$send({ type: $e.UNARCHIVE })"
      @cancel="$send({ type: $e.CANCEL })"
    >
      <p v-html="$str('unarchive_workflow_warning_message', 'mod_approval')" />
    </ConfirmationModal>
    <ModalPresenter
      :open="$matches('persistence.clone')"
      @request-close="$send({ type: $e.CANCEL_MODAL })"
    >
      <WorkflowCloneModal
        :can-view-orgframeworks="canViewOrgframeworks"
        :can-view-posframeworks="canViewPosframeworks"
      />
    </ModalPresenter>
    <ModalPresenter
      :open="$matches('persistence.assignRoles')"
      @request-close="$send({ type: $e.CANCEL_MODAL })"
    >
      <AssignRolesModal
        :machine-id="machineId"
        :can-view-orgframeworks="canViewOrgframeworks"
        :can-view-posframeworks="canViewPosframeworks"
      />
    </ModalPresenter>
    <ModalPresenter
      :open="$matches('persistence.editDetails')"
      @request-close="$send($e.CANCEL)"
    >
      <WorkflowEditModal
        :workflow-id="workflowId"
        :initial-values="initialValuesForEditing"
        :updating="$matches('persistence.editDetails.updating')"
        @confirm="$send({ type: $e.EDIT, data: $event })"
        @cancel="$send({ type: $e.CANCEL })"
      />
    </ModalPresenter>
    <ConfirmationModal
      :open="$matches('persistence.confirmPublishWorkflowVersion')"
      :title="
        $str('confirm_publish_workflow_version_warning_title', 'mod_approval')
      "
      :confirm-button-text="$str('publish', 'mod_approval')"
      :confirm-disabled="!workflowVersionActivatable"
      :loading="
        $matches(
          'persistence.confirmPublishWorkflowVersion.publishingWorkflowVersion'
        )
      "
      @confirm="$send({ type: $e.PUBLISH })"
      @cancel="$send({ type: $e.CANCEL })"
    >
      {{ confirmPublishText }}
      <ul v-if="workflowVersionActivationWarnings">
        <li v-for="(warning, i) in workflowVersionActivationWarnings" :key="i">
          {{ warning }}
        </li>
      </ul>
    </ConfirmationModal>
  </div>
</template>

<script>
import Loading from 'tui/components/icons/Loading';
import Button from 'tui/components/buttons/Button';
import ButtonIcon from 'tui/components/buttons/ButtonIcon';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import Lozenge from 'tui/components/lozenge/Lozenge';
import MoreIcon from 'tui/components/icons/More';
import NotificationBanner from 'tui/components/notifications/NotificationBanner';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import NavigationBar from 'mod_approval/components/application/header/NavigationBar';
import Popover from 'tui/components/popover/Popover';
import WorkflowEditModal from 'mod_approval/components/workflow/WorkflowEditModal';
import WorkflowCloneModal from 'mod_approval/components/workflow/WorkflowCloneModal';
import AssignRolesModal from 'mod_approval/components/workflow/AssignRolesModal';
import WorkflowDeleteModal from 'mod_approval/components/workflow/WorkflowDeleteModal';

export default {
  components: {
    Button,
    ButtonIcon,
    ConfirmationModal,
    Dropdown,
    DropdownItem,
    Loading,
    Lozenge,
    MoreIcon,
    NotificationBanner,
    ModalPresenter,
    NavigationBar,
    Popover,
    WorkflowEditModal,
    WorkflowCloneModal,
    AssignRolesModal,
    WorkflowDeleteModal,
  },

  props: {
    backUrl: {
      type: String,
      required: true,
    },
    machineId: {
      type: String,
      required: true,
    },
    canViewOrgframeworks: Boolean,
    canViewPosframeworks: Boolean,
  },

  data() {
    return {};
  },

  computed: {
    saving() {
      return (
        this.$matches('persistence.saving') ||
        this.$matches('persistence.debouncing')
      );
    },
    assignmentType() {
      return this.$selectors.getAssignmentType(this.$context);
    },
    assignedToName() {
      return this.$selectors.getAssignedToName(this.$context);
    },
    interactor() {
      return this.$selectors.getInteractor(this.$context);
    },
    workflowType() {
      return this.$selectors.getWorkflowTypeName(this.$context);
    },
    workflowId() {
      return this.$selectors.getWorkflowId(this.$context);
    },
    workflowIdNumber() {
      return this.$selectors.getWorkflowIdNumber(this.$context);
    },
    workflowContextId() {
      return this.$selectors.getWorkflowContextId(this.$context);
    },
    workflowName() {
      return this.$selectors.getWorkflowName(this.$context);
    },
    workflowApplicationsCount() {
      return this.$selectors.getWorkflowApplicationsCount(this.$context);
    },
    workflowAssignmentsCount() {
      return this.$selectors.getWorkflowAssignmentsCount(this.$context);
    },
    workflowDescription() {
      return this.$selectors.getWorkflowDescription(this.$context);
    },
    workflowStatus() {
      return this.$selectors.getWorkflowStatus(this.$context);
    },
    workflowVersionActivationWarnings() {
      return this.$selectors.getWorkflowVersionActivationWarnings(
        this.$context
      );
    },
    workflowVersionActivatable() {
      return this.$selectors.getWorkflowVersionActivatable(this.$context);
    },
    initialValuesForEditing() {
      return {
        name: this.$selectors.getWorkflowName(this.$context),
        description: this.$selectors.getWorkflowDescription(this.$context),
        id_number: this.$selectors.getWorkflowIdNumber(this.$context),
      };
    },
    confirmPublishText() {
      return this.workflowVersionActivatable
        ? this.$str(
            'confirm_publish_workflow_version_warning_message',
            'mod_approval',
            this.workflowName
          )
        : this.$str(
            'confirm_publish_workflow_version_blocked_message',
            'mod_approval',
            this.workflowName
          );
    },
  },

  methods: {
    reportUrl(workflow_id) {
      return this.$url('/mod/approval/workflow/report.php', { workflow_id });
    },
    reportApproversUrl(workflow_id) {
      return this.$url('/mod/approval/workflow/report/approvers_report.php', {
        workflow_id,
      });
    },
    roleAssignmentReportUrl(workflow_id) {
      return this.$url(
        '/mod/approval/workflow/report/role_assignment/override.php',
        {
          workflow_id,
        }
      );
    },
  },

  xState: {
    machineId() {
      return this.machineId;
    },
  },
};
</script>

<style lang="scss">
.tui-mod_approval-workflowHeader {
  padding: var(--gap-10) var(--gap-8);
  background-color: var(--color-neutral-3);

  &__savingStatus {
    display: inline;
    color: var(--color-neutral-6);
    vertical-align: super;

    &-loading {
      margin-left: var(--gap-2);
    }
  }

  &__notificationRow {
    margin-top: var(--gap-6);
  }

  &__titleRow {
    margin-top: var(--gap-6);

    @media (min-width: $tui-screen-md) {
      display: flex;
      justify-content: space-between;
    }
  }

  &__titleWrapper {
    display: flex;
    flex-direction: column;
  }

  &__title {
    display: inline;
  }

  &__infoButton {
    text-decoration: underline;
  }

  &__status {
    margin-top: var(--gap-1);
    margin-bottom: calc(var(--gap-2) + (var(--gap-1) / 2));
    margin-left: var(--gap-2);
    padding-top: calc(var(--gap-1) / 2);
    white-space: nowrap;
    vertical-align: middle;
  }

  &__actionsWrapper {
    margin-right: var(--gap-2);
    margin-left: var(--gap-2);

    &:not(:last-child) {
      margin-bottom: var(--gap-2);
      padding-bottom: var(--gap-2);
      border-bottom: 1px solid var(--color-neutral-5);
    }
  }

  &__actionsHeader {
    padding: var(--gap-2) var(--gap-4);
    color: var(--color-neutral-6);
    font-size: font-size-px(12);
    text-transform: uppercase;
  }

  &__actions {
    display: flex;
    align-self: flex-start;
    margin-top: var(--gap-6);
    @include tui-stack-horizontal(var(--gap-4));

    @media (min-width: $tui-screen-md) {
      margin-top: 0;
    }
  }

  &__collection {
    display: flex;

    &:not(:first-child) {
      margin-top: var(--gap-4);
    }
  }

  &__collectionName {
    width: 50%;
    color: var(--color-neutral-6);
  }
  &__collectionValue {
    width: 50%;
    word-wrap: break-word;
  }

  &__dateTime {
    @include font(body-sm);
    color: var(--color-neutral-7);
  }

  &__description {
    margin-top: var(--gap-4);
  }
}
</style>
