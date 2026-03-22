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

  @author Jaron Steenson <jaron.steenson@totaralearning.com>
  @module mod_perform
-->

<template>
  <Layout
    class="tui-performManageActivity"
    :loading="$apollo.loading"
    :small-on-mobile="true"
    :title="basicData && basicData.name ? basicData.name.display_value : ''"
  >
    <template v-slot:content-nav>
      <PageBackLink
        :link="goBackLink"
        :text="$str('back_to_all_activities', 'mod_perform')"
      />
    </template>

    <template v-if="basicData" v-slot:header-buttons>
      <div class="tui-performManageActivity__actions">
        <!-- Desktop activate -->
        <Button
          v-if="basicData.state_details.name === 'DRAFT'"
          class="tui-performManageActivity__actions-activate"
          :text="$str('activity_action_activate', 'mod_perform')"
          @click="activateActivity"
        />

        <!-- Setting options -->
        <Dropdown position="bottom-right">
          <template v-slot:trigger="{ toggle, isOpen }">
            <Button
              :aria-label="$str('activity_action_options', 'mod_perform')"
              variant="link"
              :aria-expanded="isOpen"
              @click="toggle"
            >
              <MoreIcon
                class="tui-performManageActivity__actions-triggerIcon"
              />
            </Button>
          </template>
          <DropdownItem @click="openEditDetails()">
            {{ $str('activity_action_edit', 'mod_perform') }}
          </DropdownItem>
        </Dropdown>
      </div>
    </template>

    <template v-if="basicData" v-slot:header-sub-content>
      <div class="tui-performManageActivity__details">
        <div class="tui-performManageActivity__details-type">
          <span class="sr-only">
            {{
              $str(
                'a11y_activity_type_value',
                'mod_perform',
                basicData.type.display_name
              )
            }}
          </span>
          <span aria-hidden="true">
            {{ basicData.type.display_name }}
          </span>
        </div>

        <div class="tui-performManageActivity__details-status">
          <span class="sr-only">
            {{
              $str(
                'a11y_activity_status_value',
                'mod_perform',
                basicData.state_details.display_name
              )
            }}
          </span>
          <span aria-hidden="true">
            <Lozenge
              :text="basicData.state_details.display_name"
              :type="
                basicData.state_details.name === 'ACTIVE'
                  ? 'success'
                  : 'neutral'
              "
            />
          </span>
        </div>
      </div>
    </template>

    <template v-if="basicData" v-slot:pre-body>
      <div class="tui-performManageActivity__heading">
        <div
          v-if="basicData.description && basicData.description.display_value"
          class="tui-performManageActivity__description"
        >
          <HideShow
            :aria-region-label="
              $str('a11y_activity_full_description', 'mod_perform')
            "
            :content-before-toggle="true"
            :hide-content-text="$str('show_less', 'totara_core')"
            :narrow-trigger="true"
            :show-content-text="$str('show_more', 'totara_core')"
          >
            <template
              v-slot:trigger="{ controls, expanded, text, toggleContent }"
            >
              <div class="tui-performManageActivity__description-expand">
                <div v-if="!expanded">
                  {{
                    truncateString(
                      basicData.description.display_value,
                      maxDescriptionLength
                    )
                  }}
                </div>

                <div v-if="hasLargeDescription">
                  <Button
                    :aria-controls="controls"
                    :styleclass="{
                      small: true,
                      transparent: true,
                    }"
                    :text="text"
                    @click="toggleContent"
                  />
                </div>
              </div>
            </template>

            <template v-slot:content>
              <div>
                {{ basicData.description.display_value }}
              </div>
            </template>
          </HideShow>
        </div>

        <!-- Mobile activate -->
        <div class="tui-performManageActivity__mobileActions">
          <Button
            v-if="basicData.state_details.name === 'DRAFT'"
            :text="$str('activity_action_activate', 'mod_perform')"
            @click="activateActivity"
          />
        </div>
      </div>
    </template>

    <template v-if="activity" v-slot:content>
      <div class="tui-performManageActivity__content">
        <Tabs
          :selected="currentTabId"
          :controlled="true"
          content-spacing="large"
          @input="changeTabRequest"
        >
          <Tab
            v-for="({ component, name, id }, index) in tabs"
            :id="id"
            :key="index"
            :name="name"
          >
            <component
              :is="component"
              v-model:value="activity"
              :activity="activity"
              :activity-id="activityId"
              :activity-state="activityState"
              :activity-context-id="parseInt(activity.context_id)"
              :activity-has-unsaved-changes="unsavedChanges"
              :tab-is-active="id === currentTabId"
              @unsaved-changes="setUnsavedChanges"
              @mutation-error="showMutationErrorNotification"
              @mutation-success="showMutationSuccessNotification"
              @refetch-core-query="refetch"
            />
          </Tab>
        </Tabs>

        <ActivateActivityModal
          v-if="basicData"
          :activity-id="activityId"
          :activity-name="basicData.name.display_value"
          :trigger-open="showActivateModal"
          @close-activate-modal="updateShowActivateModal"
          @update-loading="uploadLoading"
          @unsaved-changes="setUnsavedChanges"
          @refetch="activityActivated"
        />
      </div>
    </template>

    <template v-slot:modals>
      <ModalPresenter :open="showEditDetails" @request-close="closeEditDetails">
        <EditDetailsModal
          :activity-id="activityId"
          :data="basicData"
          :loading="$apollo.queries.basicData.loading"
          @updated="detailsUpdated"
        />
      </ModalPresenter>
    </template>
  </Layout>
</template>

<script>
import ActivateActivityModal from 'mod_perform/components/manage_activity/ActivateActivityModal';
import ActivityContentTab from 'mod_perform/components/manage_activity/content/ActivityContentTab';
import AssignmentsTab from 'mod_perform/components/manage_activity/tabs/AssignmentsTab';
import Button from 'tui/components/buttons/Button';
import Dropdown from 'tui/components/dropdown/Dropdown';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import EditDetailsModal from 'mod_perform/components/manage_activity/settings/EditDetailsModal';
import InstanceCreationTab from 'mod_perform/components/manage_activity/instance_creation/InstanceCreationTab';
import HideShow from 'tui/components/collapsible/HideShow';
import Layout from 'tui/components/layouts/LayoutOneColumn';
import Lozenge from 'tui/components/lozenge/Lozenge';
import ModalPresenter from 'tui/components/modal/ModalPresenter';
import MoreIcon from 'tui/components/icons/More';
import NotificationsTab from 'mod_perform/components/manage_activity/notification/NotificationsTab';
import PageBackLink from 'tui/components/layouts/PageBackLink';
import Tab from 'tui/components/tabs/Tab';
import Tabs from 'tui/components/tabs/Tabs';
import VisibilityClosureTab from 'mod_perform/components/manage_activity/tabs/VisibilityClosureTab';

import { notify } from 'tui/notifications';
import { debounce } from 'tui/util';

// graphQL
import activityQuery from 'mod_perform/graphql/activity';
import activityControls from 'mod_perform/graphql/activity_controls';

export default {
  components: {
    ActivateActivityModal,
    ActivityContentTab,
    AssignmentsTab,
    Button,
    Dropdown,
    DropdownItem,
    EditDetailsModal,
    InstanceCreationTab,
    HideShow,
    Layout,
    Lozenge,
    ModalPresenter,
    MoreIcon,
    NotificationsTab,
    PageBackLink,
    Tab,
    Tabs,
    VisibilityClosureTab,
  },

  props: {
    activityId: {
      required: true,
      type: [Number, String],
    },
    goBackLink: {
      required: true,
      type: String,
    },
    activityClonedSuccess: Boolean,
    clonedActivityName: String,
  },

  data() {
    return {
      activity: null,
      // Core activity details
      basicData: null,
      currentTabId: this.$id('content-tab'),
      // Number of characters to show before switching to hide/show expand
      maxDescriptionLength: 200,
      tabs: [
        {
          id: this.$id('content-tab'),
          component: 'ActivityContentTab',
          name: this.$str('manage_activities_tabs_content', 'mod_perform'),
        },
        {
          id: this.$id('assignments-tab'),
          component: 'AssignmentsTab',
          name: this.$str('manage_activities_tabs_assignment', 'mod_perform'),
        },
        {
          id: this.$id('instance-creation-tab'),
          component: 'InstanceCreationTab',
          name: this.$str(
            'manage_activities_tabs_instance_creation',
            'mod_perform'
          ),
        },
        {
          id: this.$id('visibility-closure-tab'),
          component: 'visibilityClosureTab',
          name: this.$str(
            'manage_activities_tabs_visibility_closure',
            'mod_perform'
          ),
        },
        {
          id: this.$id('notifications-tab'),
          component: 'NotificationsTab',
          name: this.$str(
            'manage_activities_tabs_notifications',
            'mod_perform'
          ),
        },
      ],
      unsavedChanges: false,
      showActivateModal: false,
      // Show edit details modal
      showEditDetails: false,
      activateModalLoading: false,
    };
  },

  computed: {
    activityState() {
      return this.activity ? this.activity.state_details.name : null;
    },

    /**
     * Is the description longer that the max allowed characters
     */
    hasLargeDescription() {
      return (
        this.basicData &&
        this.basicData.description &&
        this.basicData.description.display_value.length >
          this.maxDescriptionLength
      );
    },
  },

  created() {
    this.showMutationSuccessNotification = debounce(
      this.showMutationSuccessNotification,
      500
    );
  },

  mounted() {
    if (this.activityClonedSuccess) {
      notify({
        message: this.$str(
          'toast_success_activity_cloned',
          'mod_perform',
          this.clonedActivityName
        ),
        type: 'success',
      });
    }
  },

  apollo: {
    activity: {
      query: activityQuery,
      variables() {
        return {
          activity_id: this.activityId,
        };
      },
      update: data => {
        return data.mod_perform_activity;
      },
    },

    basicData: {
      query: activityControls,
      fetchPolicy: 'network-only',
      variables() {
        return {
          input: {
            activity_id: this.activityId,
            control_keys: ['basic'],
          },
        };
      },
      update({ mod_perform_activity_controls: data }) {
        if (data.controls) {
          data = JSON.parse(data.controls);
        }
        return data.basic;
      },
    },
  },

  methods: {
    /**
     * Re-fetch the activity basic and full data from the server.
     */
    activityActivated() {
      this.$apollo.queries.basicData.refetch();
      this.$apollo.queries.activity.refetch();
    },

    /**
     * Change tab request has been made
     *
     * @param {String} id
     */
    changeTabRequest(id) {
      if (this.unsavedChanges) {
        const message = this.$str('unsaved_changes_warning', 'mod_perform');
        let answer = window.confirm(message);
        if (answer) {
          this.currentTabId = id;
          this.unsavedChanges = false;
        } else {
          return;
        }
      } else {
        this.currentTabId = id;
      }
    },

    /**
     * Hide model for updating activity details
     */
    closeEditDetails() {
      this.showEditDetails = false;
    },

    /**
     * Activity details have been changed, update UI and display confirmation
     */
    async detailsUpdated() {
      await this.$apollo.queries.basicData.refetch();
      notify({
        message: this.$str('toast_success_activity_update', 'mod_perform'),
        type: 'success',
      });
      this.closeEditDetails();
    },

    /**
     * Display UI for edit activity details (Name, description, type)
     */
    openEditDetails() {
      this.$apollo.queries.basicData.refetch();
      this.showEditDetails = true;
    },

    /**
     * Re-fetch the activity from the server.
     */
    refetch() {
      this.$apollo.queries.activity.refetch();
    },

    /**
     * Show a generic saving error toast.
     */
    showMutationErrorNotification() {
      notify({
        message: this.$str('toast_error_generic_update', 'mod_perform'),
        type: 'error',
      });
    },

    /**
     * Show a generic success toast.
     */
    showMutationSuccessNotification() {
      notify({
        message: this.$str('toast_success_activity_update', 'mod_perform'),
        type: 'success',
      });
    },

    /**
     * Set if there is unsaved changes or not
     */
    setUnsavedChanges(hasUnsavedChanges) {
      this.unsavedChanges = hasUnsavedChanges;
    },

    /**
     * Truncate the text to a set character length
     * and replace the last three characters with an ellipsis
     *
     * @param {String} str string to be truncated
     * @param {Number} characters Number of characters to limit the string to
     */
    truncateString(str, characters) {
      const ellipsis = '...';
      return str.length > characters
        ? str.substring(0, characters - ellipsis.length) + ellipsis
        : str;
    },

    /**
     * Check unsaved changes when click activate button from manage activity
     */
    activateActivity() {
      if (this.unsavedChanges) {
        const message = this.$str('unsaved_changes_warning', 'mod_perform');
        let answer = window.confirm(message);
        if (answer) {
          this.showActivateModal = true;
        } else {
          return;
        }
      } else {
        this.showActivateModal = true;
      }
    },

    updateShowActivateModal(value) {
      this.showActivateModal = value;
    },

    uploadLoading(value) {
      this.activateModalLoading = value;
    },
  },
};
</script>

<style lang="scss">
.tui-performManageActivity {
  &__actions {
    display: flex;
    gap: var(--gap-4);
    align-items: center;
    min-height: var(--gap-6);

    &-activate {
      display: none;
    }

    &-triggerIcon {
      font-size: font-size-px(16);
    }
  }

  &__content {
    & > * + * {
      margin-top: var(--gap-8);
    }
  }

  &__heading {
    display: flex;
    flex-direction: column;
    gap: var(--gap-5);
    margin-top: var(--gap-4);
  }

  &__details {
    display: flex;
    gap: var(--gap-2);
    align-items: baseline;

    &-type {
      color: var(--color-neutral-6);
      @include font(body-sm);
    }
  }

  &__description {
    max-width: 700px;

    &-expand {
      display: flex;
      flex-direction: column;
      gap: var(--gap-1);
    }
  }

  .tui-pageHeading {
    flex-wrap: nowrap;
  }
}

@media (min-width: $tui-screen-sm) {
  .tui-performManageActivity {
    &__actions {
      &-activate {
        display: block;
      }

      &-triggerIcon {
        font-size: font-size-px(20);
      }
    }

    &__mobileActions {
      display: none;
    }
  }
}
</style>
